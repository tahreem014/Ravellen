@extends('layouts.admin')

@section('title', 'Add Product')

@section('content')
    <div class="mb-3">
        <a href="{{ route('admin.products.index') }}" class="text-decoration-none"><i class="ri-arrow-left-line"></i> Back to Products</a>
    </div>

    <div class="admin-card card">
        <div class="card-body">
            <form action="{{ route('admin.products.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Product Name</label>
                    <input type="text" name="name" value="{{ old('name') }}" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Category</label>
                        <select name="category_id" id="categorySelect" class="form-select" required>
                            <option value="">Select category</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Sub-category (optional)</label>
                        <select name="sub_category_id" id="subCategorySelect" class="form-select">
                            <option value="">None</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="btn btn-admin-primary">Create Product</button>
            </form>
        </div>
    </div>
@endsection

@section('script')
<script>
    const categories = @json($categories->keyBy('id'));
    const oldSubCategory = @json(old('sub_category_id'));
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
                if (String(sub.id) === String(oldSubCategory)) opt.selected = true;
                subCategorySelect.appendChild(opt);
            });
        }
    }

    categorySelect.addEventListener('change', populateSubCategories);
    populateSubCategories();
</script>
@endsection
