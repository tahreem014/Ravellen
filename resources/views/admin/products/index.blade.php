@extends('layouts.admin')

@section('title', 'Products')

@section('content')
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <form method="GET" class="admin-toolbar-form">
            <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Search products...">
            <button class="btn btn-sm btn-outline-secondary" type="submit"><i class="ri-search-line"></i></button>
        </form>
        <a href="{{ route('admin.products.create') }}" class="btn btn-admin-primary btn-sm admin-toolbar-btn">
            <i class="ri-add-line"></i> Add Product
        </a>
    </div>

    <div class="admin-card card">
        <div class="table-responsive">
            <table class="table table-stack table-stack-media align-middle mb-0">
                <thead>
                    <tr>
                        <th></th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Variants</th>
                        <th>Price Range</th>
                        <th>Discount</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                        @php
                            $thumb = $product->images->firstWhere('image', '!=', null);
                            $prices = $product->variants->map(fn ($variant) => $variant->effectivePrice());
                            $discount = $product->discountLabel();
                            $discountPercent = $product->variants->map(fn ($variant) => $variant->discountPercent())->filter()->max();
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
                            <td class="stack-title">
                                <div class="fw-semibold">{{ $product->name }}</div>
                            </td>
                            <td data-label="Category">
                                <span>
                                    {{ $product->category?->name }}
                                    @if ($product->subCategory)
                                        <span class="text-muted small">/ {{ $product->subCategory->name }}</span>
                                    @endif
                                </span>
                            </td>
                            <td data-label="Variants">{{ $product->variants->count() }}</td>
                            <td data-label="Price">
                                <div>
                                @if ($prices->isNotEmpty())
                                    <span data-row-price>PKR {{ number_format($prices->min()) }}@if ($prices->min() != $prices->max()) &ndash; {{ number_format($prices->max()) }}@endif</span>
                                    @if ($product->usesPackPricing())<div class="text-muted small">Mystery Balls &middot; {{ $product->pricing_mode }} pricing</div>@endif
                                @else
                                    <span class="text-muted">&mdash;</span>
                                @endif
                                </div>
                            </td>
                            <td class="text-nowrap" data-label="Discount">
                                <span data-row-discount>
                                    @if ($discount)
                                        <span class="badge rounded-pill me-1" style="background:#e11d48;">{{ $discount }}</span>
                                    @else
                                        <span class="text-muted small me-1">None</span>
                                    @endif
                                </span>
                                @if ($product->variants->isNotEmpty())
                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                            data-bs-toggle="modal" data-bs-target="#discountModal"
                                            data-action="{{ route('admin.products.sale', $product) }}"
                                            data-name="{{ $product->name }}"
                                            data-percent="{{ $discountPercent }}"
                                            data-on-sale="{{ $discount ? 1 : 0 }}">
                                        <i class="ri-price-tag-3-line"></i> {{ $discount ? 'Change' : 'Set discount' }}
                                    </button>
                                @endif
                            </td>
                            <td class="text-end text-nowrap stack-actions">
                                <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-sm btn-outline-secondary"><i class="ri-edit-line"></i></a>
                                <form action="{{ route('admin.products.destroy', $product) }}" method="POST" class="d-inline" onsubmit="return confirmAction(this, 'Delete this product and all its variants/images?');">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="ri-delete-bin-line"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">No products yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $products->links() }}</div>

    {{-- "Set discount" dialog, filled in from the clicked row's button --}}
    <div class="modal fade" id="discountModal" tabindex="-1" aria-labelledby="discountModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" id="discountForm">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="discountModalTitle">Set discount</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="fw-semibold mb-3" id="discountProductName"></p>
                        <label class="form-label" for="discountPercent">Discount</label>
                        <div class="input-group" style="max-width:220px;">
                            <input type="number" name="percent" id="discountPercent" min="0.01" max="99.99" step="0.01" class="form-control" placeholder="e.g. 15" required>
                            <span class="input-group-text">% off</span>
                        </div>
                        <div class="form-text">
                            Applies to every variant / pack of this product. Customers see the sale price, the old price crossed out,
                            and a "-15%" style badge on the product image.
                        </div>
                        <div class="text-danger small mt-2" id="discountError" hidden></div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" form="endDiscountForm" class="btn btn-outline-danger me-auto" id="endDiscountButton">End discount</button>
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-admin-primary">Apply discount</button>
                    </div>
                </form>
                <form method="POST" id="endDiscountForm">@csrf</form>
            </div>
        </div>
    </div>
@endsection

@section('script')
<script>
    const discountModal = document.getElementById('discountModal');
    const discountError = document.getElementById('discountError');
    let discountButton = null;   // the row button that opened the dialog

    discountModal.addEventListener('show.bs.modal', (event) => {
        discountButton = event.relatedTarget;
        document.getElementById('discountForm').action    = discountButton.dataset.action;
        document.getElementById('endDiscountForm').action = discountButton.dataset.action;
        document.getElementById('discountProductName').textContent = discountButton.dataset.name;
        document.getElementById('discountPercent').value  = discountButton.dataset.percent || '';
        document.getElementById('endDiscountButton').hidden = discountButton.dataset.onSale !== '1';
        discountError.hidden = true;
    });

    discountModal.addEventListener('shown.bs.modal', () => document.getElementById('discountPercent').focus());

    // Save in the background and update the row in place — the page never changes.
    async function saveDiscount(event) {
        event.preventDefault();
        const form = event.target;
        const buttons = discountModal.querySelectorAll('button[type=submit]');
        buttons.forEach(b => b.disabled = true);
        discountError.hidden = true;

        try {
            const res = await fetch(form.action, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: new FormData(form),
            });

            if (res.status === 419 || res.status === 401) {
                showToast('Your session expired. Reloading the page so you can try again.', 'error');
                setTimeout(() => location.reload(), 1500);
                return;
            }
            const data = await res.json();
            if (!res.ok) {
                discountError.textContent = data.errors ? Object.values(data.errors).flat().join(' ') : (data.message || 'Could not save the discount.');
                discountError.hidden = false;
                return;
            }

            const row = discountButton.closest('tr');
            row.querySelector('[data-row-discount]').innerHTML = data.discount
                ? `<span class="badge rounded-pill me-1" style="background:#e11d48;"></span>`
                : '<span class="text-muted small me-1">None</span>';
            if (data.discount) row.querySelector('[data-row-discount] .badge').textContent = data.discount;
            const price = row.querySelector('[data-row-price]');
            if (price) {
                const fmt = (n) => Math.round(n).toLocaleString();
                price.textContent = 'PKR ' + fmt(data.price_min) + (data.price_min !== data.price_max ? ' – ' + fmt(data.price_max) : '');
            }
            discountButton.dataset.percent = data.percent ?? '';
            discountButton.dataset.onSale  = data.discount ? '1' : '0';
            discountButton.innerHTML = `<i class="ri-price-tag-3-line"></i> ${data.discount ? 'Change' : 'Set discount'}`;

            bootstrap.Modal.getInstance(discountModal).hide();
            showToast(data.message, 'success');
        } catch (e) {
            discountError.textContent = 'Could not reach the server. Please try again.';
            discountError.hidden = false;
        } finally {
            buttons.forEach(b => b.disabled = false);
        }
    }

    document.getElementById('discountForm').addEventListener('submit', saveDiscount);
    document.getElementById('endDiscountForm').addEventListener('submit', saveDiscount);
</script>
@endsection
