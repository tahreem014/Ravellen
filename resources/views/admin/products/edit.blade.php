@extends('layouts.admin')

@section('title', 'Edit Product')

@section('content')
    <div class="mb-3 d-flex align-items-center justify-content-between">
        <a href="{{ route('admin.products.index') }}" class="text-decoration-none"><i class="ri-arrow-left-line"></i> Back to Products</a>
        <a href="{{ route('admin.products.create') }}" class="btn btn-admin-primary btn-sm"><i class="ri-add-line"></i> Add Another Product</a>
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

                <button type="submit" class="btn btn-admin-primary">Save Changes</button>
            </form>
        </div>
    </div>

    <div class="admin-card card mb-4">
        <div class="card-body">
            <h5 class="mb-3">Variants</h5>
            <div class="table-responsive mb-3">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Price (PKR)</th>
                            <th>Min</th>
                            <th>Max</th>
                            <th>Stock</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($product->variants as $variant)
                            @php $formId = 'variant-form-' . $variant->id; @endphp
                            <tr>
                                <td><input form="{{ $formId }}" type="text" name="variant_name" value="{{ $variant->variant_name }}" class="form-control form-control-sm" required></td>
                                <td><input form="{{ $formId }}" type="number" step="0.01" name="price" value="{{ $variant->price }}" class="form-control form-control-sm" style="width:110px;" required></td>
                                <td><input form="{{ $formId }}" type="number" name="min_qty" value="{{ $variant->min_qty }}" class="form-control form-control-sm" style="width:70px;" required></td>
                                <td><input form="{{ $formId }}" type="number" name="max_qty" value="{{ $variant->max_qty }}" class="form-control form-control-sm" style="width:70px;" required></td>
                                <td><input form="{{ $formId }}" type="number" name="stock" value="{{ $variant->stock }}" class="form-control form-control-sm" style="width:80px;" required></td>
                                <td>
                                    <select form="{{ $formId }}" name="status" class="form-select form-select-sm">
                                        @foreach (['active','out_of_stock','pre_order','restocking','discontinued'] as $status)
                                            <option value="{{ $status }}" @selected($variant->status === $status)>{{ ucfirst(str_replace('_',' ', $status)) }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="text-end text-nowrap">
                                    <form id="{{ $formId }}" action="{{ route('admin.products.variants.update', [$product, $variant]) }}" method="POST" class="d-none">
                                        @csrf @method('PUT')
                                    </form>
                                    <button form="{{ $formId }}" class="btn btn-sm btn-outline-secondary" type="submit"><i class="ri-save-line"></i></button>
                                    <form action="{{ route('admin.products.variants.destroy', [$product, $variant]) }}" method="POST" class="d-inline" onsubmit="return confirmAction(this, 'Remove this variant?');">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger"><i class="ri-delete-bin-line"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-3">No variants yet — add one below.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <form action="{{ route('admin.products.variants.store', $product) }}" method="POST" class="row g-2 align-items-end">
                @csrf
                <div class="col-md-3"><label class="form-label small">Name</label><input type="text" name="variant_name" class="form-control form-control-sm" required></div>
                <div class="col-md-2"><label class="form-label small">Price</label><input type="number" step="0.01" name="price" class="form-control form-control-sm" required></div>
                <div class="col-md-1"><label class="form-label small">Min</label><input type="number" name="min_qty" value="1" class="form-control form-control-sm" required></div>
                <div class="col-md-1"><label class="form-label small">Max</label><input type="number" name="max_qty" value="1" class="form-control form-control-sm" required></div>
                <div class="col-md-2"><label class="form-label small">Stock</label><input type="number" name="stock" value="0" class="form-control form-control-sm" required></div>
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
        </div>
    </div>

    <div class="admin-card card">
        <div class="card-body">
            <h5 class="mb-3">Media</h5>
            <div class="row g-3 mb-3">
                @forelse ($product->images as $image)
                    <div class="col-6 col-md-3 col-lg-2">
                        <div class="position-relative">
                            @if ($image->image)
                                <img src="{{ $image->image_url }}" class="rounded w-100" style="height:100px;object-fit:cover;">
                            @elseif ($image->video)
                                <video src="{{ $image->video_url }}" class="rounded w-100" style="height:100px;object-fit:cover;" muted></video>
                            @endif
                            <form action="{{ route('admin.products.images.destroy', [$product, $image]) }}" method="POST" onsubmit="return confirmAction(this, 'Remove this media?');" class="position-absolute top-0 end-0 m-1">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger" style="padding:.1rem .4rem;"><i class="ri-close-line"></i></button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-muted">No media uploaded yet.</div>
                @endforelse
            </div>

            <form action="{{ route('admin.products.images.store', $product) }}" method="POST" enctype="multipart/form-data" class="row g-2 align-items-end">
                @csrf
                <div class="col-md-4">
                    <label class="form-label small">Image</label>
                    <input type="file" name="image" class="form-control form-control-sm" accept="image/*">
                </div>
                <div class="col-md-4">
                    <label class="form-label small">or Video</label>
                    <input type="file" name="video" class="form-control form-control-sm" accept="video/*">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-admin-primary btn-sm" type="submit"><i class="ri-upload-line"></i> Upload</button>
                </div>
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
