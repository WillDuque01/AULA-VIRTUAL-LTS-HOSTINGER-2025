<?php

namespace App\Livewire\Admin;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class ProductCatalog extends Component
{
    use WithPagination;

    public string $search = '';
    public string $status = 'published';
    public ?string $type = null;
    public bool $onlyFeatured = false;

    public bool $showEditor = false;
    public ?int $editingProductId = null;
    public array $form = [
        'title' => '',
        'excerpt' => '',
        'price_amount' => 0,
        'price_currency' => 'USD',
        'status' => 'draft',
        'category' => null,
    ];

    protected $queryString = [
        'status' => ['except' => 'published'],
        'type' => ['except' => ''],
        'onlyFeatured' => ['except' => false],
    ];

    protected $listeners = [
        'product:saved' => '$refresh',
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatingType(): void
    {
        $this->resetPage();
    }

    public function toggleFeatured(int $productId): void
    {
        $product = Product::findOrFail($productId);
        $product->is_featured = ! $product->is_featured;
        $product->save();

        $this->dispatch('notify', message: __('Actualizamos el estado destacado.'));
    }

    public function edit(int $productId): void
    {
        $product = Product::findOrFail($productId);
        $this->editingProductId = $product->id;
        $this->form = [
            'title' => $product->title,
            'excerpt' => $product->excerpt,
            'price_amount' => (float) $product->price_amount,
            'price_currency' => $product->price_currency,
            'status' => $product->status,
            'category' => $product->category,
        ];
        $this->showEditor = true;
        $this->resetErrorBag();
    }

    public function save(): void
    {
        if (! $this->editingProductId) {
            return;
        }

        $data = $this->validate([
            'form.title' => ['required', 'string', 'max:140'],
            'form.excerpt' => ['nullable', 'string', 'max:280'],
            'form.price_amount' => ['required', 'numeric', 'min:0'],
            'form.price_currency' => ['required', 'string', 'size:3'],
            'form.status' => ['required', 'in:draft,published,archived'],
            'form.category' => ['nullable', 'string', 'max:80'],
        ])['form'];

        $product = Product::findOrFail($this->editingProductId);
        $product->update([
            'title' => $data['title'],
            'excerpt' => $data['excerpt'],
            'price_amount' => $data['price_amount'],
            'price_currency' => Str::upper($data['price_currency']),
            'status' => $data['status'],
            'category' => $data['category'],
        ]);

        $this->showEditor = false;
        $this->editingProductId = null;
        $this->dispatch('notify', message: __('Producto actualizado.'));
    }

    public function render()
    {
        $products = Product::query()
            ->with('productable')
            ->when($this->search !== '', function (Builder $query) {
                $query->where(function (Builder $sub) {
                    $sub->where('title', 'like', '%'.$this->search.'%')
                        ->orWhere('excerpt', 'like', '%'.$this->search.'%')
                        ->orWhere('category', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->status !== 'all', fn (Builder $query) => $query->where('status', $this->status))
            ->when($this->type, fn (Builder $query) => $query->where('type', $this->type))
            ->when($this->onlyFeatured, fn (Builder $query) => $query->featured())
            ->orderByDesc('is_featured')
            ->orderBy('title')
            ->paginate(12);

        $types = Product::query()
            ->select('type')
            ->distinct()
            ->pluck('type')
            ->values();

        return view('livewire.admin.product-catalog', [
            'products' => $products,
            'types' => $types,
        ]);
    }
}


