@extends('layouts.landing-page-layout')

@section('css')
<style>
    .cart-wrapper { background: linear-gradient(to bottom, #fdf2f8, #f5f3ff); min-height: 100vh; }

    /* ── Empty state ── */
    .empty-state {
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        min-height: 70vh; text-align: center;
    }
    .empty-icon-wrap {
        width: 6rem; height: 6rem; border-radius: 50%;
        background: #f3f4f6;
        display: flex; align-items: center; justify-content: center;
        margin-bottom: 1.5rem;
    }
    .empty-icon-wrap i { font-size: 2.5rem; color: #9ca3af; }

    /* ── Item cards ── */
    .cart-item-card {
        border: 2px solid transparent; border-radius: 1rem;
        box-shadow: 0 2px 12px rgba(0,0,0,.07);
        background: #fff; margin-bottom: 1rem;
        transition: box-shadow .2s, border-color .2s;
        cursor: pointer; user-select: none; -webkit-user-select: none;
    }
    .cart-item-card:hover { box-shadow: 0 6px 20px rgba(0,0,0,.11); }
    /* Blue outline = this item goes to checkout */
    .cart-item-card.selected {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59,130,246,.15), 0 6px 20px rgba(0,0,0,.11);
    }
    .cart-item-card:focus-visible { outline: 2px solid #3b82f6; outline-offset: 3px; }
    .cart-item-img {
        width: 5rem; height: 5rem; object-fit: cover;
        border-radius: .6rem; flex-shrink: 0;
    }

    /* ── Qty controls ── */
    .cart-qty-btn {
        width: 1.75rem; height: 1.75rem; border-radius: .35rem;
        border: 1px solid #e5e7eb; background: #fff;
        display: flex; align-items: center; justify-content: center;
        font-size: .9rem; cursor: pointer; transition: border-color .2s; line-height: 1;
    }
    .cart-qty-btn:hover { border-color: #ec4899; }
    .cart-qty-val { min-width: 2rem; text-align: center; font-weight: 600; font-size: .95rem; }

    .delete-btn {
        background: none; border: none; color: #f87171;
        font-size: 1.1rem; cursor: pointer; padding: 0; transition: color .2s;
    }
    .delete-btn:hover { color: #ef4444; }

    /* ── Summary card ── */
    .summary-card {
        border: 0; border-radius: 1rem;
        box-shadow: 0 4px 20px rgba(0,0,0,.09);
        background: #fff;
    }
    .checkout-btn {
        background: linear-gradient(135deg, #ec4899, #a855f7);
        border: 0; border-radius: .5rem; color: #fff;
        font-weight: 600; font-size: .95rem;
        padding: .5rem .25rem; width: 100%; transition: opacity .2s;
    }
    .checkout-btn:hover { opacity: .88; color: #fff; }
    .checkout-btn:disabled { opacity: .5; cursor: not-allowed; }
    .continue-btn {
        border: 1.5px solid #e5e7eb; border-radius: .6rem;
        background: #fff; color: #374151;
        font-weight: 500; font-size: .9rem;
        padding: .65rem 1rem; width: 100%; transition: border-color .2s;
    }
    .continue-btn:hover { border-color: #a855f7; color: #7c3aed; }

    .preorder-note { font-size: .75rem; color: #d97706; font-weight: 500; }
    .item-comment {
        font-size: .75rem; color: #6b7280; font-style: italic;
        overflow-wrap: break-word;
    }
    .item-price {
        font-weight: 700; font-size: .95rem;
        background: linear-gradient(to right, #ec4899, #a855f7);
        -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
    }
    .start-shopping-btn {
        background: linear-gradient(135deg, #ec4899, #a855f7);
        border: 0; border-radius: .6rem; color: #fff;
        font-weight: 600; font-size: .9rem;
        padding: .6rem 1.5rem; transition: opacity .2s;
        display: inline-flex; align-items: center; gap: .4rem;
        text-decoration: none;
    }
    .start-shopping-btn:hover { opacity: .88; color: #fff; text-decoration: none; }
</style>
@endsection

@section('content')
<div class="cart-wrapper py-5">
<div class="container px-4">

    {{-- ── Empty State (full page center) ── --}}
    <div id="empty-state" class="empty-state" style="display:none;">
        <div class="empty-icon-wrap">
            <i class="ri-shopping-cart-line"></i>
        </div>
        <h4 class="fw-bold mb-2">Your Cart is Empty</h4>
        <p class="text-muted mb-4" style="font-size:.9rem;">Add some mystery scoops to get started!</p>
        <a href="{{ url('/shop') }}" class="start-shopping-btn">
            <i class="ri-store-2-line"></i> Start Shopping
        </a>
    </div>

    {{-- ── Cart Content ── --}}
    <div id="cart-content" style="display:none;">
        <h1 class="fw-bold mb-1" style="font-size:2rem;background:linear-gradient(to right,#ec4899,#a855f7);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">
            Shopping Cart
        </h1>
        <p class="text-muted mb-4 small" id="cart-count-label">0 items in your cart</p>

        <div class="row g-4 align-items-start">
            <div class="col-lg-8">
                <div id="cart-items-list"></div>
            </div>

            <div class="col-lg-4">
                <div class="summary-card p-4">
                    <h5 class="fw-bold mb-4">Order Summary</h5>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small" id="subtotal-label">Subtotal (0 items)</span>
                        <span class="fw-semibold small" id="subtotal-val">PKR 0</span>
                    </div>
                    <div id="preorder-row" class="d-flex justify-content-between mb-2" style="display:none!important;">
                        <span class="text-muted small">Pre-order items (40% advance)</span>
                        <span class="fw-semibold small" id="preorder-val">PKR 0</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3 pb-3 border-bottom">
                        <span class="text-muted small">Shipping Fee</span>
                        <span class="fw-semibold small">PKR 200</span>
                    </div>
                    <div class="d-flex justify-content-between mb-4 fw-bold fs-5">
                        <span>Total</span>
                        <span id="total-val" style="color:#ec4899;">PKR 200</span>
                    </div>
                    <button class="checkout-btn mb-2" id="checkout-btn" onclick="window.location.href='{{ url('/checkout') }}'">Proceed to Checkout &nbsp;→</button>
                    <button class="continue-btn" onclick="window.location.href='{{ url('/shop') }}'">Continue Shopping</button>
                    <p class="text-muted mt-3 mb-0" style="font-size:.75rem;">
                        <strong>Note:</strong> Shipping fee of PKR 200 is applied to all orders.
                    </p>
                </div>
            </div>
        </div>
    </div>

</div>
</div>
@endsection

@section('script')
<script>
const SHIPPING   = 200;
const CART_KEY       = 'rv_cart';
const DESELECTED_KEY = 'rv_cart_deselected';   // read by the checkout page too
const IS_AUTH        = {{ auth()->check() ? 'true' : 'false' }};
const CSRF_TOKEN     = '{{ csrf_token() }}';

/* ── helpers ────────────────────────────────────────────────────────────── */
function getCart()       { try { return JSON.parse(localStorage.getItem(CART_KEY) || '[]'); } catch(e) { return []; } }
function saveCart(cart)  { localStorage.setItem(CART_KEY, JSON.stringify(cart)); }

/* Selection is stored as the items the user has *un*-selected, so anything
   newly added to the cart is selected by default. */
function getDeselected()     { try { return new Set(JSON.parse(localStorage.getItem(DESELECTED_KEY) || '[]')); } catch(e) { return new Set(); } }
function saveDeselected(set) { try { localStorage.setItem(DESELECTED_KEY, JSON.stringify([...set])); } catch(e) {} }
/* db-backed rows have a unique id; guest rows are identified by id + variant. */
function itemKey(item)       { return item.id.startsWith('db-') ? item.id : item.id + '|' + (item.subtitle || ''); }
function fmt(n)          { return 'PKR ' + Number(n).toLocaleString(); }
/* Price plus the crossed-out regular price while the item is on sale. */
function priceHtml(amount, original) {
    return fmt(amount) + (original && original > amount
        ? ' <s style="color:#9ca3af;-webkit-text-fill-color:#9ca3af;font-weight:500;font-size:.8em;margin-left:.3em;">' + fmt(original) + '</s>'
        : '');
}
function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

/* ── API wrappers ────────────────────────────────────────────────────────── */
async function apiUpdateQty(dbId, qty) {
    if (!IS_AUTH || !dbId) return;
    await fetch(`/cart/items/${dbId}`, {
        method:  'PUT',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
        body:    JSON.stringify({ qty }),
    });
}

async function apiRemove(dbId) {
    if (!IS_AUTH || !dbId) return;
    await fetch(`/cart/items/${dbId}`, {
        method:  'DELETE',
        headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
    });
}

/**
 * For authenticated users, replace localStorage cart with the DB's
 * authoritative list. Any stale 'pi-' entries left over from failed API
 * calls are discarded. Guest-only items (no 'pi-' or 'db-' mismatch) are
 * kept so nothing is lost if the user logged in mid-session.
 */
async function syncFromDb() {
    if (!IS_AUTH) return;
    try {
        const res      = await fetch('/cart/items', { headers: { 'Accept': 'application/json' } });
        const data     = await res.json();
        const dbItems  = data.items || [];

        // DB is the source of truth for authenticated users.
        // Keep any localStorage items that are NOT db-backed (e.g. mystery-balls)
        // but evict all stale 'pi-' temporary entries.
        const local    = getCart();
        const nonDb    = local.filter(i => !i.id.startsWith('db-') && !i.id.startsWith('pi-'));

        // Build the final cart: DB items first, then any non-DB guest items
        const merged   = [...dbItems, ...nonDb];

        saveCart(merged);
    } catch (e) {
        console.error('Cart sync failed:', e);
    }
}

/* ── render ─────────────────────────────────────────────────────────────── */
function renderCart() {
    const cart        = getCart();
    const emptyState  = document.getElementById('empty-state');
    const cartContent = document.getElementById('cart-content');

    if (cart.length === 0) {
        emptyState.style.display  = 'flex';
        cartContent.style.display = 'none';
        return;
    }

    emptyState.style.display  = 'none';
    cartContent.style.display = 'block';

    const totalQty = cart.reduce((s, i) => s + i.qty, 0);
    document.getElementById('cart-count-label').textContent =
        totalQty + ' item' + (totalQty !== 1 ? 's' : '') + ' in your cart · click an item to select or deselect it for checkout';

    // Drop deselections for items that are no longer in the cart
    const deselected = getDeselected();
    const liveKeys   = new Set(cart.map(itemKey));
    const pruned     = [...deselected].filter(k => liveKeys.has(k));
    if (pruned.length !== deselected.size) saveDeselected(new Set(pruned));

    document.getElementById('cart-items-list').innerHTML = cart.map((item, idx) => {
        const lineTotal    = item.unitPrice * item.qty;
        const displayPrice = item.preorder ? Math.round(lineTotal * 0.4) : lineTotal;
        const originalLine = item.originalPrice && !item.preorder ? item.originalPrice * item.qty : null;
        // DB items carry id like 'db-123'; extract numeric id for API calls
        const dbId = item.id && item.id.startsWith('db-') ? item.id.replace('db-', '') : null;
        const isSelected = !pruned.includes(itemKey(item));

        return `
        <div class="cart-item-card p-3 ${isSelected ? 'selected' : ''}" data-idx="${idx}" data-db-id="${dbId || ''}"
             role="checkbox" aria-checked="${isSelected}" tabindex="0">
            <div class="d-flex align-items-center gap-3">
                <img src="${item.image || ''}" alt="${item.name}" class="cart-item-img"
                     onerror="this.src='https://images.unsplash.com/photo-1513885535751-8b9238bd345a?w=200&q=80'">
                <div class="flex-grow-1 min-w-0">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="fw-semibold" style="font-size:.95rem;">${item.name}</div>
                            ${item.subtitle ? `<div class="text-muted" style="font-size:.78rem;">${item.subtitle}</div>` : ''}
                            ${item.preorder ? `<div class="preorder-note mt-1"><i class="ri-information-line me-1"></i>PRE-ORDER · 40% Advance Required</div>` : ''}
                            ${item.comment ? `<div class="item-comment mt-1"><i class="ri-chat-1-line me-1"></i>${escapeHtml(item.comment)}</div>` : ''}
                        </div>
                        <button class="delete-btn ms-2" onclick="removeItem(${idx},'${dbId || ''}')" title="Remove">
                            <i class="ri-delete-bin-line"></i>
                        </button>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mt-2">
                        <div class="d-flex align-items-center gap-2">
                            <button class="cart-qty-btn" onclick="changeQty(${idx}, -1, '${dbId || ''}')">−</button>
                            <span class="cart-qty-val">${item.qty}</span>
                            <button class="cart-qty-btn" onclick="changeQty(${idx},  1, '${dbId || ''}')">+</button>
                        </div>
                        <div class="text-end">
                            <div class="text-muted" style="font-size:.75rem;">
                                ${priceHtml(item.unitPrice, item.originalPrice)} each${item.preorder ? ' · 40% advance' : ''}
                            </div>
                            <div class="item-price">${priceHtml(displayPrice, originalLine)}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>`;
    }).join('');

    updateSummary(cart);
}

/* ── summary ────────────────────────────────────────────────────────────── */
/* Totals cover only the selected items — those are what checkout will order. */
function updateSummary(cart) {
    let regularTotal  = 0;
    let preorderTotal = 0;
    let totalQty      = 0;

    const deselected = getDeselected();
    const selected   = cart.filter(item => !deselected.has(itemKey(item)));

    selected.forEach(item => {
        const line = item.unitPrice * item.qty;
        totalQty += item.qty;
        if (item.preorder) preorderTotal += Math.round(line * 0.4);
        else               regularTotal  += line;
    });

    const subtotal   = regularTotal + preorderTotal;
    const grandTotal = selected.length ? subtotal + SHIPPING : 0;

    document.getElementById('checkout-btn').disabled = selected.length === 0;

    document.getElementById('subtotal-label').textContent = `Subtotal (${totalQty} item${totalQty !== 1 ? 's' : ''})`;
    document.getElementById('subtotal-val').textContent   = fmt(subtotal);
    document.getElementById('total-val').textContent      = fmt(grandTotal);

    const preRow = document.getElementById('preorder-row');
    if (preorderTotal > 0) {
        preRow.style.setProperty('display', 'flex', 'important');
        document.getElementById('preorder-val').textContent = fmt(preorderTotal);
    } else {
        preRow.style.setProperty('display', 'none', 'important');
    }
}

/* ── selection ───────────────────────────────────────────────────────────── */
function toggleSelect(card) {
    const cart = getCart();
    const item = cart[Number(card.dataset.idx)];
    if (!item) return;

    const deselected = getDeselected();
    const key        = itemKey(item);
    if (deselected.has(key)) deselected.delete(key); else deselected.add(key);
    saveDeselected(deselected);

    // Update in place (no re-render) so keyboard focus stays on the card
    const isSelected = !deselected.has(key);
    card.classList.toggle('selected', isSelected);
    card.setAttribute('aria-checked', isSelected);
    updateSummary(cart);
}

function initSelection() {
    const list = document.getElementById('cart-items-list');

    list.addEventListener('click', e => {
        if (e.target.closest('button')) return;      // qty / delete keep their own behaviour
        const card = e.target.closest('.cart-item-card');
        if (card) toggleSelect(card);
    });

    list.addEventListener('keydown', e => {
        if (e.key !== ' ' && e.key !== 'Enter') return;
        if (!e.target.classList.contains('cart-item-card')) return;
        e.preventDefault();
        toggleSelect(e.target);
    });
}

/* ── mutations ───────────────────────────────────────────────────────────── */
async function changeQty(idx, delta, dbId) {
    let cart = getCart();
    cart[idx].qty = Math.max(1, cart[idx].qty + delta);
    saveCart(cart);
    renderCart();
    // fire-and-forget DB update
    if (dbId) await apiUpdateQty(dbId, cart[idx].qty);
}

async function removeItem(idx, dbId) {
    let cart = getCart();
    cart.splice(idx, 1);
    saveCart(cart);
    renderCart();
    if (dbId) await apiRemove(dbId);
}

/* ── boot ───────────────────────────────────────────────────────────────── */
// The admin changed prices / discounts while the cart is open (layouts/live-updates)
document.addEventListener('live:catalog', async () => {
    await syncFromDb();
    renderCart();
});

document.addEventListener('DOMContentLoaded', async () => {
    await syncFromDb();
    initSelection();
    renderCart();
});
</script>
@endsection
