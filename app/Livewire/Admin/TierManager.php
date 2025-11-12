<?php

namespace App\Livewire\Admin;

use App\Models\Tier;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;

class TierManager extends Component
{
    public $tiers;

    public array $form = [];

    public string $featureString = '';

    public ?int $editingId = null;

    protected $rules = [
        'form.name' => ['required', 'string', 'max:255'],
        'form.slug' => ['nullable', 'string', 'max:255'],
        'form.tagline' => ['nullable', 'string', 'max:255'],
        'form.description' => ['nullable', 'string'],
        'form.priority' => ['nullable', 'integer', 'min:0', 'max:1000'],
        'form.access_type' => ['required', 'in:free,paid,vip'],
        'form.price_monthly' => ['nullable', 'numeric', 'min:0'],
        'form.price_yearly' => ['nullable', 'numeric', 'min:0'],
        'form.currency' => ['required', 'string', 'size:3'],
        'form.metadata_color' => ['nullable', 'regex:/^#([0-9a-fA-F]{3}){1,2}$/'],
        'form.is_default' => ['boolean'],
        'form.is_active' => ['boolean'],
    ];

    public function mount(): void
    {
        $this->resetForm();
        $this->loadTiers();
    }

    public function render()
    {
        return view('livewire.admin.tier-manager');
    }

    public function updatedFormName(string $value): void
    {
        if (! $this->editingId && blank($this->form['slug'])) {
            $this->form['slug'] = Str::slug($value);
        }
    }

    public function updatedFormCurrency(string $value): void
    {
        $this->form['currency'] = Str::upper(Str::substr($value, 0, 3));
    }

    public function createTier(): void
    {
        $this->resetForm();
    }

    public function editTier(int $tierId): void
    {
        $tier = Tier::findOrFail($tierId);

        $this->editingId = $tier->id;
        $this->form = [
            'name' => $tier->name,
            'slug' => $tier->slug,
            'tagline' => $tier->tagline,
            'description' => $tier->description,
            'priority' => $tier->priority,
            'access_type' => $tier->access_type,
            'price_monthly' => $tier->price_monthly,
            'price_yearly' => $tier->price_yearly,
            'currency' => $tier->currency,
            'is_default' => (bool) $tier->is_default,
            'is_active' => (bool) $tier->is_active,
            'metadata_color' => Arr::get($tier->metadata ?? [], 'color', '#1d4ed8'),
        ];
        $this->featureString = implode(', ', $tier->features ?? []);
    }

    public function saveTier(): void
    {
        $rules = $this->rules;
        $rules['form.slug'][] = Rule::unique('tiers', 'slug')->ignore($this->editingId);

        $validated = $this->validate($rules)['form'];

        if (blank($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $validated['is_default'] = (bool) ($validated['is_default'] ?? false);
        $validated['is_active'] = (bool) ($validated['is_active'] ?? true);

        if ($validated['access_type'] === 'free') {
            $validated['price_monthly'] = null;
            $validated['price_yearly'] = null;
        }

        $features = collect(preg_split('/[,;\n]+/', (string) $this->featureString))
            ->map(fn ($item) => trim($item))
            ->filter()
            ->values()
            ->all();

        $data = $validated;
        $data['currency'] = Str::upper($data['currency']);
        $data['features'] = $features ?: null;
        $data['metadata'] = ['color' => $validated['metadata_color'] ?: null];
        unset($data['metadata_color']);

        $tier = $this->editingId ? Tier::findOrFail($this->editingId) : new Tier();
        $tier->fill(Arr::except($data, ['is_default', 'is_active']));
        $tier->is_default = $data['is_default'];
        $tier->is_active = $data['is_active'];
        $tier->save();

        if ($tier->is_default) {
            Tier::where('id', '!=', $tier->id)->update(['is_default' => false]);
        }

        $this->resetForm();
        $this->loadTiers();

        session()->flash('status', __('Tier guardado correctamente.'));
    }

    public function toggleActive(int $tierId): void
    {
        $tier = Tier::findOrFail($tierId);
        $tier->is_active = ! $tier->is_active;
        $tier->save();

        if ($this->editingId === $tier->id) {
            $this->form['is_active'] = $tier->is_active;
        }

        $this->loadTiers();
        session()->flash('status', $tier->is_active ? __('Tier activado.') : __('Tier desactivado.'));
    }

    public function setDefault(int $tierId): void
    {
        $tier = Tier::findOrFail($tierId);
        $tier->is_default = true;
        $tier->save();

        Tier::where('id', '!=', $tierId)->update(['is_default' => false]);

        if ($this->editingId === $tierId) {
            $this->form['is_default'] = true;
        }

        $this->loadTiers();
        session()->flash('status', __('Tier establecido como predeterminado.'));
    }

    public function deleteTier(int $tierId): void
    {
        $tier = Tier::findOrFail($tierId);

        if ($tier->is_default) {
            session()->flash('error', __('No puedes eliminar el tier predeterminado.'));
            return;
        }

        if ($tier->users()->exists() || $tier->subscriptions()->exists()) {
            session()->flash('error', __('No puedes eliminar un tier con estudiantes o suscripciones activas.'));
            return;
        }

        $tier->delete();

        if ($this->editingId === $tierId) {
            $this->resetForm();
        }

        $this->loadTiers();
        session()->flash('status', __('Tier eliminado correctamente.'));
    }

    private function loadTiers(): void
    {
        $this->tiers = Tier::withCount([
            'activeUsers as active_users_count',
            'groups',
        ])->orderByDesc('priority')->orderBy('name')->get();
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->form = [
            'name' => '',
            'slug' => '',
            'tagline' => '',
            'description' => '',
            'priority' => 0,
            'access_type' => 'free',
            'price_monthly' => null,
            'price_yearly' => null,
            'currency' => 'USD',
            'is_default' => false,
            'is_active' => true,
            'metadata_color' => '#22c55e',
        ];
        $this->featureString = '';
    }
}
