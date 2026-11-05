@extends('layouts.admin')

@section('title', 'Add Product')

@section('content')
    <div class="mb-3">
        <a href="{{ route('admin.products.index') }}" class="text-decoration-none"><i class="ri-arrow-left-line"></i> Back to Products</a>
    </div>

    {{-- One form, one Save button at the very end: details, pricing, variants and media are all saved together --}}
    <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data" id="productForm">
        @csrf

        <div class="admin-card card mb-4">
            <div class="card-body">
                <h5 class="mb-3">Product Details</h5>

                <div class="mb-3">
                    <label class="form-label">Product Name</label>
                    <input type="text" name="name" value="{{ old('name') }}" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                </div>

                @include('admin.products.partials.category-fields')

                @include('admin.products.partials.pricing-fields', ['currentMode' => null, 'hasVariants' => false])
            </div>
        </div>

        @include('admin.products.partials.variant-fields', ['product' => null])

        <div class="admin-card card mb-4">
            <div class="card-body">
                <h5 class="mb-3">Media <span class="text-muted fw-normal small">(optional)</span></h5>
                @include('admin.products.partials.media-picker')
            </div>
        </div>

        <button type="submit" class="btn btn-admin-primary" data-media-submit>Save Product</button>
    </form>
@endsection
