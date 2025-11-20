<?php

namespace App\Livewire\Shop;

use App\Models\Product;
use App\Support\Practice\PracticeCart;
use Livewire\Component;

class ProductGallery extends Component
{
    public string $category = 'all';
    public bool $onlyFeatured = false;
    public ?string $flash = null;
    public string $type = 'all';

    public function addToCart(int $productId): void
    {
        $product = Product::with('productable')->published()->find($productId);

        if (! $product) {
            $this->addError('cart', __('El producto ya no está disponible.'));

            return;
        }

        if (! $product->productable) {
            $this->addError('cart', __('Todavía no está listo para la compra.'));

            return;
        }

        if ($product->isSoldOut()) {
            $this->addError('cart', __('Ya no hay cupos disponibles para este producto.'));

            return;
        }

        PracticeCart::addProduct($productId);
        $this->flash = __('Producto agregado al carrito. Completa tu compra desde el checkout.');
        $this->dispatch('notify', message: $this->flash);
    }

    public function render()
    {
        $products = Product::query()
            ->published()
            ->when($this->category !== 'all', fn ($query) => $query->where('category', $this->category))
            ->when($this->type !== 'all', fn ($query) => $query->where('type', $this->type))
            ->when($this->onlyFeatured, fn ($query) => $query->featured())
            ->orderByDesc('is_featured')
            ->orderBy('title')
            ->with('productable')
            ->get();

        $categories = Product::query()
            ->select('category')
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category')
            ->filter()
            ->values();

        $types = Product::query()
            ->select('type')
            ->distinct()
            ->pluck('type')
            ->values();

        return view('livewire.shop.product-gallery', [
            'products' => $products,
            'categories' => $categories,
            'types' => $types,
        ]);
    }
}


