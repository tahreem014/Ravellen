@extends('layouts.admin')

@section('title', 'Edit Product')

@section('content')
    <div class="mb-3 d-flex align-items-center justify-content-between">
        <a href="{{ route('admin.products.index') }}" class="text-decoration-none"><i class="ri-arrow-left-line"></i> Back to Products</a>
        <a href="{{ route('admin.products.create') }}" class="btn btn-admin-primary btn-sm"><i class="ri-add-line"></i> Add Another Product</a>
    </div>

    {{-- Whole-product discount, kept at the top so it's easy to find --}}
    @php
        $discount = $product->discountLabel();
        $discountPercent = $product->variants->map(fn ($variant) => $variant->discountPercent())->filter()->max();
    @endphp
    {{-- data-admin-region / data-ajax: these sections save in place (layouts/admin) --}}
    <div class="admin-card card mb-4" id="discount" data-admin-region="discount">
        <div class="card-body">
            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                <h5 class="mb-0"><i class="ri-price-tag-3-line text-danger"></i> Discount</h5>
                @if ($discount)
                    <span class="badge rounded-pill" style="background:#e11d48;">{{ $discount }}</span>
                    <span class="small text-muted">on sale now</span>
                @else
                    <span class="small text-muted">No discount running</span>
                @endif
            </div>

            @if ($product->variants->isEmpty())
                <p class="small text-muted mb-0">Add a variant below first, then you can put this product on sale.</p>
            @else
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <form action="{{ route('admin.products.sale', $product) }}" method="POST" class="d-flex align-items-center gap-2" data-ajax>
                        @csrf
                        <div class="input-group input-group-sm" style="width:170px;">
                            <input type="number" name="percent" value="{{ $discountPercent }}" min="0.01" max="99.99" step="0.01" class="form-control" placeholder="e.g. 15" aria-label="Discount percentage" required>
                            <span class="input-group-text">% off</span>
                        </div>
                        <button type="submit" class="btn btn-sm btn-admin-primary">{{ $discount ? 'Update discount' : 'Apply discount' }}</button>
                    </form>
                    @if ($discount)
                        <form action="{{ route('admin.products.sale', $product) }}" method="POST" data-ajax>
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-danger">End discount</button>
                        </form>
                    @endif
                </div>
                <div class="form-text">
                    Takes the percentage off every {{ $product->usesPackPricing() ? 'pack' : 'variant' }} of this product.
                    @if ($product->pricing_mode === \App\Models\Product::PRICING_AUTOMATIC)
                        Every pack follows the 5-ball sale price.
                    @elseif ($product->pricing_mode === \App\Models\Product::PRICING_CUSTOM)
                        To discount only some quantities, use the sale price boxes in Pricing below.
                    @else
                        To discount just one variant, use its Sale Price in the Variants table below.
                    @endif
                </div>
            @endif
        </div>
    </div>

    <div class="admin-card card mb-4">
        <div class="card-body">
            <h5 class="mb-3">Product Details</h5>
            <form action="{{ route('admin.products.update', $product) }}" method="POST">
                @csrf @method('PUT')

                <div class="mb-3">
                    <label class="form-label">Product Name</label>
                    <input type="text" name="name" value="{{ old('name', $product->name) }}" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3">{{ old('description', $product->description) }}</textarea>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Category</label>
                        <select name="category_id" id="categorySelect" class="form-select" required>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected($product->category_id == $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Sub-category (optional)</label>
                        <select name="sub_category_id" id="subCategorySelect" class="form-select"></select>
                    </div>
                </div>

                @include('admin.products.partials.pricing-fields', [
                    'currentMode' => $product->pricing_mode,
                    'hasVariants' => ! $product->usesPackPricing() && $product->variants->isNotEmpty(),
                ])

                <button type="submit" class="btn btn-admin-primary">Save Changes</button>
            </form>
        </div>
    </div>

    @php
        $isPackProduct = $product->usesPackPricing();
        $tableVariants = $isPackProduct
            ? $product->variants->sortBy(fn ($variant) => $variant->packSize() ?? PHP_INT_MAX)
            : $product->variants;
    @endphp
    <div class="admin-card card mb-4" data-admin-region="variants">
        <div class="card-body">
            <h5 class="mb-3">{{ $isPackProduct ? 'Packs' : 'Variants' }}</h5>

            @if ($isPackProduct)
                <p class="small text-muted mb-2">Pack prices come from the Pricing settings above. Stock and availability can still be set per pack.</p>
            @endif

            <div class="table-responsive mb-3">
                <table class="table table-stack align-middle">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Price (PKR)</th>
                            <th>Sale Price</th>
                            @unless ($isPackProduct)
                                <th>Min</th>
                                <th>Max</th>
                            @endunless
                            <th>Stock</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($tableVariants as $variant)
                            @php $formId = 'variant-form-' . $variant->id; @endphp
                            <tr>
                                @if ($isPackProduct)
                                    <td class="fw-semibold stack-title">{{ $variant->variant_name }}</td>
                                    <td data-label="Price (PKR)">{{ number_format($variant->price) }}</td>
                                    <td data-label="Sale Price">
                                        @if ($variant->hasDiscount())
                                            <span class="text-danger fw-semibold">{{ number_format($variant->sale_price) }}</span>
                                        @else
                                            <span class="text-muted">&mdash;</span>
                                        @endif
                                    </td>
                                @else
                                    <td data-label="Name"><input form="{{ $formId }}" type="text" name="variant_name" value="{{ $variant->variant_name }}" class="form-control form-control-sm" required></td>
                                    <td data-label="Price (PKR)"><input form="{{ $formId }}" type="number" step="0.01" name="price" value="{{ $variant->price }}" class="form-control form-control-sm" style="width:110px;" required></td>
                                    <td data-label="Sale Price"><input form="{{ $formId }}" type="number" step="0.01" name="sale_price" value="{{ $variant->sale_price }}" class="form-control form-control-sm" style="width:110px;" placeholder="No sale"></td>
                                    <td data-label="Min"><input form="{{ $formId }}" type="number" name="min_qty" value="{{ $variant->min_qty }}" class="form-control form-control-sm" style="width:70px;" required></td>
                                    <td data-label="Max"><input form="{{ $formId }}" type="number" name="max_qty" value="{{ $variant->max_qty }}" class="form-control form-control-sm" style="width:70px;" required></td>
                                @endif
                                <td data-label="Stock"><input form="{{ $formId }}" type="number" name="stock" value="{{ $variant->stock }}" class="form-control form-control-sm" style="width:80px;" required></td>
                                <td data-label="Status">
                                    <select form="{{ $formId }}" name="status" class="form-select form-select-sm">
                                        @foreach (['active','out_of_stock','pre_order','restocking','discontinued'] as $status)
                                            <option value="{{ $status }}" @selected($variant->status === $status)>{{ ucfirst(str_replace('_',' ', $status)) }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="text-end text-nowrap stack-actions">
                                    <form id="{{ $formId }}" action="{{ route('admin.products.variants.update', [$product, $variant]) }}" method="POST" class="d-none" data-ajax>
                                        @csrf @method('PUT')
                                    </form>
                                    <button form="{{ $formId }}" class="btn btn-sm btn-outline-secondary" type="submit"><i class="ri-save-line"></i></button>
                                    @unless ($isPackProduct)
                                        <form action="{{ route('admin.products.variants.destroy', [$product, $variant]) }}" method="POST" class="d-inline" data-ajax onsubmit="return confirmAction(this, 'Remove this variant?');">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger"><i class="ri-delete-bin-line"></i></button>
                                        </form>
                                    @endunless
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center text-muted py-3">No variants yet — add one below.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @unless ($isPackProduct)
                <form action="{{ route('admin.products.variants.store', $product) }}" method="POST" class="row g-2 align-items-end" data-ajax>
                    @csrf
                    <div class="col-md-2"><label class="form-label small">Name</label><input type="text" name="variant_name" class="form-control form-control-sm" required></div>
                    <div class="col-md-2"><label class="form-label small">Price</label><input type="number" step="0.01" name="price" class="form-control form-control-sm" required></div>
                    <div class="col-md-2"><label class="form-label small">Sale price <span class="text-muted">(optional)</span></label><input type="number" step="0.01" name="sale_price" class="form-control form-control-sm" placeholder="No sale"></div>
                    <div class="col-md-1"><label class="form-label small">Min</label><input type="number" name="min_qty" value="1" class="form-control form-control-sm" required></div>
                    <div class="col-md-1"><label class="form-label small">Max</label><input type="number" name="max_qty" value="1" class="form-control form-control-sm" required></div>
                    <div class="col-md-1"><label class="form-label small">Stock</label><input type="number" name="stock" value="0" class="form-control form-control-sm" required></div>
                    <div class="col-md-2">
                        <label class="form-label small">Status</label>
                        <select name="status" class="form-select form-select-sm">
                            @foreach (['active','out_of_stock','pre_order','restocking','discontinued'] as $status)
                                <option value="{{ $status }}">{{ ucfirst(str_replace('_',' ', $status)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-1"><button class="btn btn-admin-primary btn-sm w-100" type="submit"><i class="ri-add-line"></i></button></div>
                </form>
            @endunless
        </div>
    </div>

    <div class="admin-card card">
        <div class="card-body">
            <h5 class="mb-3">Media</h5>
            <div class="row g-3 mb-3" data-admin-region="media">
                @forelse ($product->images as $image)
                    <div class="col-6 col-md-3 col-lg-2">
                        <div class="position-relative">
                            @if ($image->image)
                                <img src="{{ $image->image_url }}" class="rounded w-100" style="height:100px;object-fit:cover;">
                            @elseif ($image->video)
                                <video src="{{ $image->video_url }}" class="rounded w-100" style="height:100px;object-fit:cover;" muted></video>
                                <span class="badge bg-dark position-absolute bottom-0 start-0 m-1"><i class="ri-video-line"></i> Video</span>
                            @endif
                            <form action="{{ route('admin.products.images.destroy', [$product, $image]) }}" method="POST" data-ajax onsubmit="return confirmAction(this, 'Remove this media?');" class="position-absolute top-0 end-0 m-1">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger" style="padding:.1rem .4rem;"><i class="ri-close-line"></i></button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-muted">No media uploaded yet.</div>
                @endforelse
            </div>

            <form action="{{ route('admin.products.images.store', $product) }}" method="POST" enctype="multipart/form-data" data-ajax>
                @csrf
                @include('admin.products.partials.media-picker')
                <button class="btn btn-admin-primary btn-sm mt-3" type="submit" data-media-submit="required" disabled><i class="ri-upload-line"></i> Save media</button>
            </form>
        </div>
    </div>
@endsection

@section('script')
<script>
    const categories = @json($categories->keyBy('id'));
    const currentSubCategory = @json($product->sub_category_id);
    const categorySelect = document.getElementById('categorySelect');
    const subCategorySelect = document.getElementById('subCategorySelect');

    function populateSubCategories() {
        const category = categories[categorySelect.value];
        subCategorySelect.innerHTML = '<option value="">None</option>';
        if (category && category.subCategories) {
            category.subCategories.forEach((sub) => {
                const opt = document.createElement('option');
                opt.value = sub.id;
                opt.textContent = sub.name;
                if (String(sub.id) === String(currentSubCategory)) opt.selected = true;
                subCategorySelect.appendChild(opt);
            });
        }
    }

    categorySelect.addEventListener('change', populateSubCategories);
    populateSubCategories();
</script>
@endsection
