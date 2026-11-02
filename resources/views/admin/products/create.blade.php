@extends('layouts.admin')

@section('title', 'Add Product')

@section('content')
    <div class="mb-3">
        <a href="{{ route('admin.products.index') }}" class="text-decoration-none"><i class="ri-arrow-left-line"></i> Back to Products</a>
    </div>

    <div class="admin-card card">
        <div class="card-body">
            <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

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

                <div class="border rounded-3 p-3 mb-3">
                    <div class="fw-semibold mb-2">Photos &amp; videos <span class="text-muted fw-normal small">(optional)</span></div>
                    @include('admin.products.partials.media-picker')
                </div>

                <button type="submit" class="btn btn-admin-primary" data-media-submit>Create Product</button>
            </form>
        </div>
    </div>
@endsection
