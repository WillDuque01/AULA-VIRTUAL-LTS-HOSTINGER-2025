<?php

namespace App\Livewire\Admin;

use App\Models\TelemetrySyncLog;
use App\Models\VideoPlayerEvent;
use App\Support\DataPorter\DataPorter;
use App\Support\Telemetry\TelemetrySyncService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class DataPorterHub extends Component
{
    use WithFileUploads;

    public array $datasets = [];

    public array $importDatasets = [];

    public string $dataset = 'video_player_events';

    public string $format = 'csv';

    public array $filters = [
        'date_from' => '',
        'date_to' => '',
        'course_id' => '',
        'lesson_id' => '',
        'practice_package_id' => '',
        'event' => '',
        'provider' => '',
        'category' => '',
    ];

    public string $importDataset = '';

    public ?string $lastImportSummary = null;

    public $importFile;

    public array $telemetryStatus = [];

    public bool $syncingTelemetry = false;

    public ?string $lastSyncMessage = null;

    public function mount(DataPorter $porter): void
    {
        $user = Auth::user();

        abort_unless($user && ($user->can('manage-settings') || $user->hasRole('teacher_admin')), 403);

        $exportDefinitions = $porter->datasetsFor($user);
        $this->datasets = $this->summarizeDatasets($exportDefinitions);
        $this->dataset = array_key_first($exportDefinitions) ?? $this->dataset;

        $importDefinitions = $porter->datasetsFor($user, intent: 'import');
        $this->importDatasets = $this->summarizeDatasets($importDefinitions);
        $this->importDataset = array_key_first($importDefinitions) ?? $this->importDataset;

        $this->telemetryStatus = $this->resolveTelemetryStatus();
    }

    public function updatedDataset(): void
    {
        $this->filters = array_map(fn () => '', $this->filters);
    }

    public function download(DataPorter $porter): void
    {
        $user = Auth::user();
        abort_unless($user, 403);

        if (! isset($this->datasets[$this->dataset])) {
            $this->addError('dataset', __('Selecciona un dataset válido.'));

            return;
        }

        try {
            $filters = $this->extractFilters();
            $sanitized = $porter->sanitizeFilters($this->dataset, $filters, $user);
        } catch (AuthorizationException $exception) {
            $this->addError('filters', $exception->getMessage());

            return;
        }

        $params = array_merge([
            'dataset' => $this->dataset,
            'format' => $this->format,
            'locale' => app()->getLocale(),
        ], $sanitized);

        $url = URL::temporarySignedRoute(
            'admin.data-porter.export',
            now()->addMinutes(5),
            $params
        );

        $this->dispatch('data-porter:download', url: $url);
        $this->dispatch('notify', message: __('Generando archivo…'));
    }

    public function syncTelemetry(TelemetrySyncService $service): void
    {
        $user = Auth::user();
        abort_unless($user && $user->can('manage-settings'), 403);

        $this->syncingTelemetry = true;

        try {
            $processed = $service->syncVideoEvents(500, $user->id);
            $this->lastSyncMessage = $processed > 0
                ? trans_choice('{1} :count evento sincronizado.|[2,*] :count eventos sincronizados.', $processed, ['count' => $processed])
                : __('No se encontraron eventos pendientes o no hay drivers habilitados.');
            $this->dispatch('notify', message: $this->lastSyncMessage);
        } catch (\Throwable $exception) {
            $this->lastSyncMessage = $exception->getMessage();
            $this->addError('telemetry', $this->lastSyncMessage);
        } finally {
            $this->syncingTelemetry = false;
            $this->telemetryStatus = $this->resolveTelemetryStatus();
        }
    }

    public function import(DataPorter $porter): void
    {
        $user = Auth::user();
        abort_unless($user, 403);

        if (empty($this->importDatasets)) {
            $this->addError('importDataset', __('No hay datasets disponibles para importación.'));

            return;
        }

        $this->validate([
            'importDataset' => ['required', Rule::in(array_keys($this->importDatasets))],
            'importFile' => ['required', 'file', 'mimes:csv,txt,json', 'max:4096'],
        ]);

        $path = $this->importFile->getRealPath();

        try {
            $count = $porter->import($this->importDataset, $path, $user);
        } catch (\Throwable $exception) {
            $this->addError('importFile', $exception->getMessage());

            return;
        }

        $this->reset('importFile');
        $this->lastImportSummary = trans_choice('{0} No se importaron filas.|{1} :count fila importada.|[2,*] :count filas importadas.', $count, ['count' => $count]);
        $this->dispatch('notify', message: $this->lastImportSummary);
    }

    public function render()
    {
        return view('livewire.admin.data-porter-hub', [
            'currentDataset' => $this->datasets[$this->dataset] ?? null,
            'filtersSchema' => $this->datasets[$this->dataset]['filters'] ?? [],
            'isTeacherRestricted' => $this->isTeacherRestricted(),
            'telemetryStatus' => $this->telemetryStatus,
        ]);
    }

    private function extractFilters(): array
    {
        $available = $this->datasets[$this->dataset]['filters'] ?? [];

        return collect($available)
            ->mapWithKeys(fn ($_filter, $key) => [$key => $this->filters[$key] ?? null])
            ->all();
    }

    private function isTeacherRestricted(): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        return $user->hasRole('teacher_admin') && ! $user->can('manage-settings');
    }

    private function resolveTelemetryStatus(): array
    {
        $pendingEvents = VideoPlayerEvent::whereNull('synced_at')->count();
        $lastSyncedAt = VideoPlayerEvent::whereNotNull('synced_at')
            ->latest('synced_at')
            ->value('synced_at');

        $drivers = [
            [
                'key' => 'ga4',
                'label' => 'GA4',
                'enabled' => (bool) config('telemetry.ga4.enabled'),
                'details' => config('telemetry.ga4.measurement_id'),
            ],
            [
                'key' => 'mixpanel',
                'label' => 'Mixpanel',
                'enabled' => (bool) config('telemetry.mixpanel.enabled'),
                'details' => config('telemetry.mixpanel.project_token'),
            ],
        ];

        return [
            'pending' => $pendingEvents,
            'last_synced_at' => $lastSyncedAt ? $lastSyncedAt->copy()->timezone(config('app.timezone', 'UTC')) : null,
            'drivers' => $drivers,
            'alert_threshold' => (int) config('services.telemetry_alerts.threshold', 4000),
            'logs' => TelemetrySyncLog::with('user:id,name,email')
                ->latest()
                ->limit(5)
                ->get(),
        ];
    }

    private function summarizeDatasets(array $definitions): array
    {
        $summary = [];

        foreach ($definitions as $key => $definition) {
            $filters = [];

            foreach ($definition['filters'] ?? [] as $filterKey => $filter) {
                $filters[$filterKey] = [
                    'label' => $filter['label'] ?? Str::headline($filterKey),
                    'type' => $filter['type'] ?? 'text',
                ];
            }

            $summary[$key] = [
                'label' => $definition['label'] ?? Str::headline($key),
                'description' => $definition['description'] ?? null,
                'filters' => $filters,
            ];
        }

        return $summary;
    }
}

