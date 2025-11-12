<?php

namespace App\Livewire\Catalog;

use App\Models\Course;
use App\Models\Tier;
use App\Support\Payments\PaymentSimulator;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Livewire\Component;

class CourseCatalog extends Component
{
    public array $courses = [];

    public ?string $flashStatus = null;

    public ?string $flashError = null;

    protected PaymentSimulator $simulator;

    public function boot(PaymentSimulator $simulator): void
    {
        $this->simulator = $simulator;
    }

    public function mount(): void
    {
        $this->loadCourses();
    }

    public function render()
    {
        return view('livewire.catalog.course-catalog');
    }

    public function purchaseTier(int $tierId): void
    {
        if (! auth()->check()) {
            $this->flashError = __('Debes iniciar sesion para comprar acceso.');

            return;
        }

        $tier = Tier::find($tierId);

        if (! $tier) {
            $this->flashError = __('Tier no disponible.');

            return;
        }

        $user = auth()->user();
        $accessible = $user->hasTier($tier->slug);

        if ($accessible) {
            $this->flashStatus = __('Ya cuentas con este tier activo.');

            return;
        }

        $this->simulator->simulate($user, $tier, [
            'source' => 'catalog',
            'metadata' => ['origin' => 'catalog-component'],
        ]);

        $this->flashStatus = __('Acceso comprado correctamente.');
        $this->flashError = null;
        $this->loadCourses();
    }

    private function loadCourses(): void
    {
        $user = auth()->user();
        $accessibleTierIds = [];

        if ($user) {
            $accessibleTierIds = $user->activeTiers()->pluck('tiers.id')->toArray();
        }

        $this->courses = Course::query()
            ->with(['tiers:id,name,slug,access_type,price_monthly,currency,priority'])
            ->with(['i18n' => fn ($query) => $query->where('locale', app()->getLocale())])
            ->orderBy('level')
            ->get()
            ->map(function (Course $course) use ($accessibleTierIds) {
                $tiers = $course->tiers->map(function (Tier $tier) use ($accessibleTierIds) {
                    return [
                        'id' => $tier->id,
                        'name' => $tier->name,
                        'slug' => $tier->slug,
                        'access_type' => $tier->access_type,
                        'price_monthly' => $tier->price_monthly,
                        'currency' => $tier->currency,
                        'available' => in_array($tier->id, $accessibleTierIds, true),
                    ];
                });

                $isFreeCourse = $tiers->isEmpty() || $tiers->every(fn ($tier) => $tier['access_type'] === 'free');
                $isAccessible = $isFreeCourse || $tiers->where('available', true)->isNotEmpty();

                $primaryTier = $tiers->first(fn ($tier) => $tier['access_type'] !== 'free');

                $translation = $course->i18n->first();

                return [
                    'id' => $course->id,
                    'slug' => $course->slug,
                    'title' => $translation->title ?? Str::headline($course->slug),
                    'description' => $translation->description ?? null,
                    'level' => $course->level,
                    'tiers' => $tiers->values()->all(),
                    'is_free' => $isFreeCourse,
                    'is_accessible' => $isAccessible,
                    'primary_tier' => $primaryTier,
                ];
            })
            ->toArray();
    }
}
