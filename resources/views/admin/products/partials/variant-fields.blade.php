{{--
    Variants section of the Add / Edit product form. It has no Save button of its own: the form's one
    Save button at the bottom of the page saves these along with everything else.

      variants[id][…]       the product's existing variants, edited in place
      remove_variants[]     existing variants marked for removal (the bin button; removed on save)
      new_variants[n][…]    variants to add; rows left blank are ignored

    A Mystery Balls product's packs come from the Pricing section, so there only each pack's stock
    and status are edited here.

    $product: the product being edited, null on the Add page
--}}
@php
    $isPackProduct = $product?->usesPackPricing() ?? false;
    $tableVariants = $product
        ? ($isPackProduct ? $product->variants->sortBy(fn ($variant) => $variant->packSize() ?? PHP_INT_MAX) : $product->variants)
        : collect();
    $markedForRemoval = array_map('intval', old('remove_variants', []));
    $newRows = array_values(old('new_variants', [[]]));   // always at least one blank row to type into
    $statusLabel = fn (string $status) => ucfirst(str_replace('_', ' ', $status));
    $invalid = fn (string $key) => $errors->has($key) ? 'is-invalid' : '';
@endphp

<div class="admin-card card mb-4" id="variantsCard">
    <div class="card-body">
        <h5 class="mb-3">{{ $isPackProduct ? 'Packs' : 'Variants' }}</h5>

        @if ($isPackProduct)
            <p class="small text-muted mb-2">Pack prices come from the Pricing settings above. Stock and availability can still be set per pack.</p>
        @endif

        @if ($tableVariants->isNotEmpty())
            <div class="table-responsive mb-3">
                <table class="table table-stack align-middle mb-0">
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
                        @foreach ($tableVariants as $variant)
                            @php
                                $id = $variant->id;
                                $value = fn (string $field) => old("variants.$id.$field", $variant->{$field});
                                $removed = in_array($id, $markedForRemoval, true);
                            @endphp
                            <tr data-variant-row class="{{ $removed ? 'opacity-50' : '' }}">
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
                                    <td data-label="Name"><input type="text" name="variants[{{ $id }}][variant_name]" value="{{ $value('variant_name') }}" class="form-control form-control-sm {{ $invalid("variants.$id.variant_name") }}" required></td>
                                    <td data-label="Price (PKR)"><input type="number" step="0.01" name="variants[{{ $id }}][price]" value="{{ $value('price') }}" class="form-control form-control-sm {{ $invalid("variants.$id.price") }}" style="width:110px;" required></td>
                                    <td data-label="Sale Price"><input type="number" step="0.01" name="variants[{{ $id }}][sale_price]" value="{{ $value('sale_price') }}" class="form-control form-control-sm {{ $invalid("variants.$id.sale_price") }}" style="width:110px;" placeholder="No sale"></td>
                                    <td data-label="Min"><input type="number" name="variants[{{ $id }}][min_qty]" value="{{ $value('min_qty') }}" class="form-control form-control-sm {{ $invalid("variants.$id.min_qty") }}" style="width:70px;" required></td>
                                    <td data-label="Max"><input type="number" name="variants[{{ $id }}][max_qty]" value="{{ $value('max_qty') }}" class="form-control form-control-sm {{ $invalid("variants.$id.max_qty") }}" style="width:70px;" required></td>
                                @endif
                                {{-- data-synced: what the database holds, so the live stock poll (products/edit) can tell what the admin has changed here --}}
                                <td data-label="Stock"><input type="number" name="variants[{{ $id }}][stock]" value="{{ $value('stock') }}" data-synced="{{ $variant->stock }}" class="form-control form-control-sm {{ $invalid("variants.$id.stock") }}" style="width:80px;" required></td>
                                <td data-label="Status">
                                    <select name="variants[{{ $id }}][status]" data-synced="{{ $variant->status }}" class="form-select form-select-sm {{ $invalid("variants.$id.status") }}">
                                        @foreach (\App\Models\ProductVariant::STATUSES as $status)
                                            <option value="{{ $status }}" @selected($value('status') === $status)>{{ $statusLabel($status) }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="text-end text-nowrap stack-actions">
                                    @unless ($isPackProduct)
                                        <input type="hidden" name="remove_variants[]" value="{{ $id }}" @disabled(! $removed)>
                                        <button type="button" class="btn btn-sm {{ $removed ? 'btn-danger' : 'btn-outline-danger' }}" data-toggle-variant-removal
                                                title="{{ $removed ? 'Keep this variant' : 'Remove this variant' }}"><i class="{{ $removed ? 'ri-arrow-go-back-line' : 'ri-delete-bin-line' }}"></i></button>
                                        <span class="small text-danger ms-1" data-removal-note @if (! $removed) hidden @endif>Removed when you save</span>
                                    @endunless
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @elseif ($product)
            <p class="small text-muted mb-3">No variants yet — add one below.</p>
        @endif

        @unless ($isPackProduct)
            {{-- Rows to add. Standard pricing only: Mystery Balls pricing builds the packs itself --}}
            <div data-standard-only>
                <div class="fw-semibold small mb-2">{{ $product ? 'Add variants' : 'Variants' }} <span class="text-muted fw-normal">— rows left blank are ignored</span></div>
                <div class="table-responsive">
                    <table class="table table-stack table-sm align-middle mb-2">
                        <thead>
                            <tr><th>Name</th><th>Price (PKR)</th><th>Sale price <span class="text-muted fw-normal">(optional)</span></th><th>Min</th><th>Max</th><th>Stock</th><th>Status</th><th></th></tr>
                        </thead>
                        <tbody id="newVariantRows">
                            @foreach ($newRows as $i => $row)
                                @php $n = "new_variants.$i"; @endphp
                                <tr>
                                    <td data-label="Name"><input type="text" name="new_variants[{{ $i }}][variant_name]" value="{{ $row['variant_name'] ?? '' }}" class="form-control form-control-sm {{ $invalid("$n.variant_name") }}"></td>
                                    <td data-label="Price (PKR)"><input type="number" step="0.01" name="new_variants[{{ $i }}][price]" value="{{ $row['price'] ?? '' }}" class="form-control form-control-sm {{ $invalid("$n.price") }}" style="width:110px;"></td>
                                    <td data-label="Sale price"><input type="number" step="0.01" name="new_variants[{{ $i }}][sale_price]" value="{{ $row['sale_price'] ?? '' }}" class="form-control form-control-sm {{ $invalid("$n.sale_price") }}" style="width:110px;" placeholder="No sale"></td>
                                    <td data-label="Min"><input type="number" name="new_variants[{{ $i }}][min_qty]" value="{{ $row['min_qty'] ?? 1 }}" class="form-control form-control-sm {{ $invalid("$n.min_qty") }}" style="width:70px;"></td>
                                    <td data-label="Max"><input type="number" name="new_variants[{{ $i }}][max_qty]" value="{{ $row['max_qty'] ?? 1 }}" class="form-control form-control-sm {{ $invalid("$n.max_qty") }}" style="width:70px;"></td>
                                    <td data-label="Stock"><input type="number" name="new_variants[{{ $i }}][stock]" value="{{ $row['stock'] ?? 0 }}" class="form-control form-control-sm {{ $invalid("$n.stock") }}" style="width:80px;"></td>
                                    <td data-label="Status">
                                        <select name="new_variants[{{ $i }}][status]" class="form-select form-select-sm {{ $invalid("$n.status") }}">
                                            @foreach (\App\Models\ProductVariant::STATUSES as $status)
                                                <option value="{{ $status }}" @selected(($row['status'] ?? 'active') === $status)>{{ $statusLabel($status) }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="text-end stack-actions"><button type="button" class="btn btn-sm btn-outline-secondary" data-remove-new-variant title="Remove this row"><i class="ri-close-line"></i></button></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="addVariantRow"><i class="ri-add-line"></i> Add another variant</button>
            </div>
        @endunless
    </div>
</div>

@unless ($isPackProduct)
<template id="newVariantTemplate">
    <tr>
        <td data-label="Name"><input type="text" name="new_variants[__INDEX__][variant_name]" class="form-control form-control-sm"></td>
        <td data-label="Price (PKR)"><input type="number" step="0.01" name="new_variants[__INDEX__][price]" class="form-control form-control-sm" style="width:110px;"></td>
        <td data-label="Sale price"><input type="number" step="0.01" name="new_variants[__INDEX__][sale_price]" class="form-control form-control-sm" style="width:110px;" placeholder="No sale"></td>
        <td data-label="Min"><input type="number" name="new_variants[__INDEX__][min_qty]" value="1" class="form-control form-control-sm" style="width:70px;"></td>
        <td data-label="Max"><input type="number" name="new_variants[__INDEX__][max_qty]" value="1" class="form-control form-control-sm" style="width:70px;"></td>
        <td data-label="Stock"><input type="number" name="new_variants[__INDEX__][stock]" value="0" class="form-control form-control-sm" style="width:80px;"></td>
        <td data-label="Status">
            <select name="new_variants[__INDEX__][status]" class="form-select form-select-sm">
                @foreach (\App\Models\ProductVariant::STATUSES as $status)
                    <option value="{{ $status }}">{{ $statusLabel($status) }}</option>
                @endforeach
            </select>
        </td>
        <td class="text-end stack-actions"><button type="button" class="btn btn-sm btn-outline-secondary" data-remove-new-variant title="Remove this row"><i class="ri-close-line"></i></button></td>
    </tr>
</template>
@endunless

<script>
(function () {
    const card = document.getElementById('variantsCard');
    let nextIndex = 1000;   // keeps new row names unique next to the server's 0, 1, 2 …

    card.addEventListener('click', (e) => {
        // an existing variant: mark / unmark it for removal (nothing is deleted until the form is saved)
        const toggle = e.target.closest('[data-toggle-variant-removal]');
        if (toggle) {
            const row = toggle.closest('[data-variant-row]');
            const marker = row.querySelector('input[name="remove_variants[]"]');
            marker.disabled = !marker.disabled;
            const removed = !marker.disabled;
            row.classList.toggle('opacity-50', removed);
            row.querySelector('[data-removal-note]').hidden = !removed;
            toggle.classList.toggle('btn-danger', removed);
            toggle.classList.toggle('btn-outline-danger', !removed);
            toggle.title = removed ? 'Keep this variant' : 'Remove this variant';
            toggle.querySelector('i').className = removed ? 'ri-arrow-go-back-line' : 'ri-delete-bin-line';
        }

        // rows to add
        const template = document.getElementById('newVariantTemplate');
        const rows = document.getElementById('newVariantRows');
        if (e.target.closest('#addVariantRow')) {
            rows.insertAdjacentHTML('beforeend', template.innerHTML.replaceAll('__INDEX__', nextIndex++));
        }
        const removeNew = e.target.closest('[data-remove-new-variant]');
        if (removeNew) removeNew.closest('tr').remove();
    });

    // Stock and status go together: typing new stock for a variant that has none flips its Status to Active,
    // and typing 0 for an active one flips it to Out of stock — shown here before saving, so the admin can
    // still pick something else. A variant that was Out of stock while it had stock keeps its status.
    card.addEventListener('input', (e) => {
        const stock = e.target.closest('input[name$="[stock]"][data-synced]');
        if (!stock) return;

        const status = stock.closest('tr').querySelector('select[name$="[status]"]');
        const amount = Number(stock.value);
        if (!status || stock.value === '' || Number.isNaN(amount)) return;

        if (amount > 0 && status.value === 'out_of_stock' && Number(stock.defaultValue) <= 0) status.value = 'active';
        if (amount <= 0 && status.value === 'active') status.value = 'out_of_stock';
    });

    // Variants only apply to standard pricing: Mystery Balls pricing builds the packs itself
    function showForMode(mode) {
        card.querySelectorAll('[data-standard-only]').forEach(part => {
            const standard = mode === 'standard';
            part.hidden = !standard;
            part.querySelectorAll('input, select, button').forEach(el => el.disabled = !standard);
        });
    }

    document.addEventListener('pricing:mode', (e) => showForMode(e.detail.mode));
    showForMode(document.querySelector('input[name="pricing_mode"]:checked')?.value || 'standard');
})();
</script>
