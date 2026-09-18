@extends('layouts.admin')

@section('title', 'Products')

@section('content')
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <form method="GET" class="d-flex gap-2">
            <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Search products..." style="min-width:220px;">
            <button class="btn btn-sm btn-outline-secondary" type="submit"><i class="ri-search-line"></i></button>
        </form>
        <a href="{{ route('admin.products.create') }}" class="btn btn-admin-primary btn-sm">
            <i class="ri-add-line"></i> Add Product
        </a>
    </div>

    <div class="admin-card card">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th></th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Variants</th>
                        <th>Price Range</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                        @php
                            $thumb = $product->images->firstWhere('image', '!=', null);
                            $prices = $product->variants->pluck('price');
                        @endphp
                        <tr>
                            <td style="width:56px;">
                                @if ($thumb)
                                    <img src="{{ $thumb->image_url }}" alt="" class="rounded" style="width:44px;height:44px;object-fit:cover;">
                                @else
                                    <div class="rounded bg-light d-flex align-items-center justify-content-center" style="width:44px;height:44px;">
                                        <i class="ri-image-line text-muted"></i>
                                    </div>
                                @endif
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $product->name }}</div>
                            </td>
                            <td>
                                {{ $product->category?->name }}
                                @if ($product->subCategory)
                                    <span class="text-muted small">/ {{ $product->subCategory->name }}</span>
                                @endif
                            </td>
                            <td>{{ $product->variants->count() }}</td>
                            <td>
                                @if ($prices->isNotEmpty())
                                    PKR {{ number_format($prices->min()) }}@if ($prices->min() != $prices->max()) &ndash; {{ number_format($prices->max()) }}@endif
                                @else
                                    <span class="text-muted">&mdash;</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-sm btn-outline-secondary"><i class="ri-edit-line"></i></a>
                                <form action="{{ route('admin.products.destroy', $product) }}" method="POST" class="d-inline" onsubmit="return confirmAction(this, 'Delete this product and all its variants/images?');">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="ri-delete-bin-line"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">No products yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $products->links() }}</div>
@endsection
