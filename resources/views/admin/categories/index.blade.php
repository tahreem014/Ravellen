@extends('layouts.admin')

@section('title', 'Categories')

@section('content')
    <div class="d-flex flex-wrap align-items-center justify-content-end gap-2 mb-3">
        <button type="button" class="btn btn-admin-primary btn-sm admin-toolbar-btn" id="addCategoryButton">
            <i class="ri-add-line"></i> Add Category
        </button>
    </div>

    <div class="alert alert-light border small">
        Every product belongs to a category; its sub-categories are managed in the
        <a href="{{ route('admin.sub-categories.index') }}">Sub-categories</a> tab. A category can only be deleted once no
        product uses it, so products are never deleted along with it (deleting a category also removes its own, empty
        sub-categories). The shop pages look the built-in categories up by name, so renaming one can empty the page that
        shows it; you'll be warned when you edit or delete one.
    </div>

    <div class="admin-card card">
        <div class="table-responsive">
            <table class="table table-stack align-middle mb-0">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Sub-categories</th>
                        <th>Products</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($categories as $category)
                        @php
                            $inUse = $category->products_count > 0;
                            $storefront = $category->isStorefrontLinked();
                            $subCount = $category->sub_categories_count;
                        @endphp
                        <tr>
                            <td class="stack-title">
                                {{ $category->name }}
                            </td>
                            <td data-label="Sub-categories">
                                @if ($subCount)
                                    <a href="{{ route('admin.sub-categories.index', ['category' => $category->id]) }}" class="text-decoration-none">{{ $subCount }} &rarr;</a>
                                @else
                                    0
                                @endif
                            </td>
                            <td data-label="Products">{{ $category->products_count }}</td>
                            <td class="text-end text-nowrap stack-actions">
                                <button type="button" class="btn btn-sm btn-outline-secondary" data-edit-category
                                        data-id="{{ $category->id }}" data-name="{{ $category->name }}" data-storefront="{{ $storefront ? 1 : 0 }}">
                                    <i class="ri-edit-line"></i> Edit
                                </button>
                                {{-- In use: the server answers with why it can't go, and how many products hold it --}}
                                <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" class="d-inline"
                                      @unless ($inUse)
                                          onsubmit="return confirmAction(this, {{ json_encode(
                                              "Delete the category \"{$category->name}\"" . ($subCount ? " and its {$subCount} " . ($subCount === 1 ? 'sub-category' : 'sub-categories') : '') . '?'
                                              . ($storefront ? ' The shop pages look this category up by name, so its page may stop listing it.' : '')
                                          ) }});"
                                      @endunless>
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="ri-delete-bin-line"></i> Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">No categories yet. Add one to start organising products.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Add / edit a category --}}
    <div class="modal fade" id="categoryModal" tabindex="-1" aria-labelledby="categoryModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="categoryForm" method="POST" action="{{ route('admin.categories.store') }}">
                    @csrf
                    <input type="hidden" name="_method" id="categoryFormMethod" value="POST">
                    <input type="hidden" name="form" value="category">
                    <input type="hidden" name="editing_id" id="categoryEditingId" value="">
                    <div class="modal-header">
                        <h5 class="modal-title" id="categoryModalTitle">Add Category</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        @if (old('form') === 'category' && $errors->any())
                            <div class="alert alert-danger small">{{ $errors->first() }}</div>
                        @endif
                        <div class="alert alert-warning small d-none" id="categoryStorefrontNote">
                            The shop pages look this category up by name. Renaming it can empty the page that shows it until you rename it back.
                        </div>
                        <label class="form-label" for="categoryName">Name</label>
                        <input type="text" name="name" id="categoryName" class="form-control" maxlength="255" required autocomplete="off">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-admin-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('script')
<script>
const categoryStoreUrl = @json(route('admin.categories.store'));
const categoryModalEl  = document.getElementById('categoryModal');

/* Fill and show the dialog: empty to add, or the saved name to edit. */
function openCategoryModal({ id = '', name = '', storefront = false } = {}) {
    document.getElementById('categoryModalTitle').textContent = id ? 'Edit Category' : 'Add Category';
    document.getElementById('categoryFormMethod').value = id ? 'PUT' : 'POST';
    document.getElementById('categoryEditingId').value  = id;
    document.getElementById('categoryForm').action      = id ? categoryStoreUrl + '/' + id : categoryStoreUrl;
    document.getElementById('categoryName').value       = name;
    document.getElementById('categoryStorefrontNote').classList.toggle('d-none', !storefront);
    bootstrap.Modal.getOrCreateInstance(categoryModalEl).show();
}

document.getElementById('addCategoryButton').addEventListener('click', () => openCategoryModal());

document.addEventListener('click', (event) => {
    const edit = event.target.closest('[data-edit-category]');
    if (edit) openCategoryModal({ id: edit.dataset.id, name: edit.dataset.name, storefront: edit.dataset.storefront === '1' });
});

/* The dialog focuses its name field once open */
categoryModalEl.addEventListener('shown.bs.modal', () => document.getElementById('categoryName').focus());

@if (old('form') === 'category' && $errors->any())
    // Save was rejected: reopen the dialog as the admin left it
    // (empty fields come back as null, hence the ?? '')
    document.addEventListener('DOMContentLoaded', () => openCategoryModal({ id: @json(old('editing_id') ?? ''), name: @json(old('name') ?? '') }));
@endif
</script>
@endsection
