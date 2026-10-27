<!DOCTYPE html>
<html lang="en" class="bg-white">
<head>
    <meta charset="utf-8" />
    @include('layouts.title-meta')
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    @include('layouts.head-css')
    <style>
        body { background: #fff; min-height: 100vh; display: flex; flex-direction: column; }
        .admin-topstrip { height: 4px; background: linear-gradient(to right, #EC4899 0%, #A855F7 100%); }
        .admin-header-wrap { background: linear-gradient(to bottom, #fdf2f8, #f5f3ff); }
        .admin-title { color: #7C3AED; font-weight: 800; }
        .admin-subtitle { color: #6b7280; }
        .admin-tabs { background: #f3f0fa; border-radius: .75rem; padding: .35rem; display: inline-flex; flex-wrap: wrap; gap: .25rem; }
        .admin-tabs .nav-link { border-radius: .5rem; padding: .5rem 1.1rem; font-weight: 600; font-size: .9rem; color: #6b7280; }
        .admin-tabs .nav-link.active { background: #fff; color: #7C3AED; box-shadow: 0 1px 4px rgba(0,0,0,.08); }
        .admin-content { background: linear-gradient(to bottom, #fdf2f8, #f5f3ff); flex: 1 0 auto; }
        .admin-card { border: none; border-radius: 1rem; box-shadow: 0 1px 6px rgba(0,0,0,.06); }
        .admin-stat-icon { width: 2.75rem; height: 2.75rem; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; }
        .btn-admin-primary { background: linear-gradient(to right, #EC4899, #A855F7); border: none; color: #fff; }
        .btn-admin-primary:hover { color: #fff; opacity: .9; }
        .btn-admin-primary:disabled, .btn-admin-primary.disabled { color: #fff; }
        .btn-admin-logout { border: 1px solid #DB2777; color: #DB2777; background: #fff; }
        .btn-admin-logout:hover { background: #DB2777; color: #fff; }
        .admin-container { max-width: 1320px; margin-left: auto; margin-right: auto; }

        /* Search / filter / "Add" row above a list: full width each on a phone, side by side from tablet up */
        .admin-toolbar-form { flex: 1 1 100%; display: flex; gap: .5rem; }
        .admin-toolbar-form .form-control, .admin-toolbar-form .form-select { flex: 1 1 auto; min-width: 0; }
        .admin-toolbar-btn { width: 100%; }
        @media (min-width: 768px) {
            .admin-toolbar-form { flex: 0 0 auto; }
            .admin-toolbar-form .form-control { flex: 0 0 auto; min-width: 220px; }
            .admin-toolbar-form .form-select { flex: 0 0 auto; }
            .admin-toolbar-btn { width: auto; }
        }

        /* ── Phones ── */
        @media (max-width: 767.98px) {
            .admin-title { font-size: 1.5rem !important; }

            /* The section tabs become one row that swipes sideways */
            .admin-tabs {
                display: flex; flex-wrap: nowrap; overflow-x: auto; gap: .2rem;
                overscroll-behavior-x: contain; -webkit-overflow-scrolling: touch;
                scroll-snap-type: x proximity; scrollbar-width: none;
            }
            .admin-tabs::-webkit-scrollbar { display: none; }
            .admin-tabs .nav-link { flex: 0 0 auto; white-space: nowrap; scroll-snap-align: center; padding: .5rem .95rem; }

            /*
             * Tables marked .table-stack become a list of cards: each row a card, each cell a
             * "LABEL  value" line (the label comes from the cell's data-label). Same markup, so
             * nothing that reads the rows changes.
             *   .stack-title   the card's heading (no label)
             *   .stack-block   long text: label above, value below
             *   .stack-actions buttons, along the bottom of the card
             */
            .table-stack, .table-stack > tbody, .table-stack > tbody > tr, .table-stack > tbody > tr > td { display: block; }
            .table-stack thead { display: none; }
            .table-stack > tbody > tr { padding: .85rem 1rem; border-bottom: 1px solid #ede9f6; }
            .table-stack > tbody > tr:last-child { border-bottom: 0; }
            .table-stack > tbody > tr > td {
                display: flex; align-items: center; justify-content: space-between; gap: 1rem;
                padding: .3rem 0; border: 0; text-align: right; background: transparent;
                white-space: normal !important; overflow-wrap: anywhere;
            }
            .table-stack > tbody > tr > td::before {
                content: attr(data-label); flex: 0 0 auto; text-align: left;
                font-size: .7rem; font-weight: 700; letter-spacing: .05em; text-transform: uppercase; color: #9ca3af;
            }
            .table-stack > tbody > tr > td:not([data-label])::before { content: none; }
            .table-stack > tbody > tr > td.stack-title { justify-content: flex-start; text-align: left; font-weight: 700; font-size: 1rem; padding-bottom: .45rem; }
            .table-stack > tbody > tr > td.stack-block { flex-direction: column; align-items: flex-start; gap: .1rem; text-align: left; }
            .table-stack > tbody > tr > td.stack-actions {
                justify-content: flex-start; flex-wrap: wrap; gap: .5rem;
                margin-top: .5rem; padding-top: .75rem; border-top: 1px dashed #e5e7eb;
            }
            .table-stack > tbody > tr > td[colspan] { display: block; text-align: center; }
            /* fields inside a card line sit on the right, at a sensible width */
            .table-stack > tbody > tr > td .form-control, .table-stack > tbody > tr > td .form-select { width: 58% !important; max-width: 13rem; flex: 0 1 58%; }

            /* a product row: thumbnail beside its name, everything else full width below */
            .table-stack-media > tbody > tr { display: grid; grid-template-columns: 52px 1fr; column-gap: .85rem; align-items: center; }
            .table-stack-media > tbody > tr > td { grid-column: 1 / -1; }
            .table-stack-media > tbody > tr > td:nth-child(1) { grid-column: 1; width: auto !important; }
            .table-stack-media > tbody > tr > td:nth-child(2) { grid-column: 2; }
            .table-stack-media > tbody > tr > td[colspan] { grid-column: 1 / -1; }
        }
    </style>
    @yield('css')
</head>
<body class="overflow-x-hidden font-poppins">
    <div class="admin-topstrip"></div>

    <div class="admin-header-wrap py-4">
        <div class="admin-container px-4">
            <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">
                <div>
                    <h1 class="admin-title mb-1" style="font-size:2rem;">Admin Dashboard</h1>
                    <p class="admin-subtitle mb-0">Manage your store, products, and orders</p>
                </div>
                {{-- On a phone, Log Out lives in the Settings tab instead --}}
                <div class="d-none d-md-flex align-items-center gap-2">
                    <span class="text-muted small">{{ Auth::user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-admin-logout">
                            <i class="ri-logout-box-r-line"></i> Log Out
                        </button>
                    </form>
                </div>
            </div>

            <nav class="admin-tabs mt-4">
                <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">Overview</a>
                <a class="nav-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}" href="{{ route('admin.products.index') }}">Products</a>
                <a class="nav-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}" href="{{ route('admin.orders.index') }}">Orders</a>
                <a class="nav-link {{ request()->routeIs('admin.pre-orders.*') ? 'active' : '' }}" href="{{ route('admin.pre-orders.index') }}">Pre-Orders</a>
                <a class="nav-link {{ request()->routeIs('admin.refund-requests.*') ? 'active' : '' }}" href="{{ route('admin.refund-requests.index') }}">Refund Requests</a>
                <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">Users</a>
                <a class="nav-link {{ request()->routeIs('admin.shipping-rates.*') ? 'active' : '' }}" href="{{ route('admin.shipping-rates.index') }}">Shipping Rates</a>
                <a class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}" href="{{ route('admin.settings.edit') }}">Settings</a>
            </nav>
        </div>
    </div>

    <main class="admin-content py-4">
        <div class="admin-container px-4">
            @yield('content')
        </div>
    </main>

    @include('layouts.footer')

    <div class="modal fade" id="adminConfirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Please Confirm</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="adminConfirmModalBody">Are you sure?</div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger btn-sm" id="adminConfirmModalOk">Confirm</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // On a phone the tabs swipe sideways: start with the current section centred in view
        (function () {
            const nav = document.querySelector('.admin-tabs');
            const active = nav && nav.querySelector('.nav-link.active');
            if (active) {
                const offset = active.getBoundingClientRect().left - nav.getBoundingClientRect().left + nav.scrollLeft;
                nav.scrollLeft = offset - (nav.clientWidth - active.offsetWidth) / 2;
            }
        })();

        window.confirmAction = function (form, message, confirmClass) {
            const modalEl = document.getElementById('adminConfirmModal');
            const okBtn = document.getElementById('adminConfirmModalOk');

            document.getElementById('adminConfirmModalBody').textContent = message;
            okBtn.className = 'btn btn-sm ' + (confirmClass || 'btn-danger');

            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);

            const handler = function () {
                okBtn.removeEventListener('click', handler);
                modal.hide();
                form.matches('[data-ajax]') ? adminSubmit(form) : form.submit();
            };
            okBtn.addEventListener('click', handler);

            modal.show();
            return false;
        };

        /*
         * In-place saving: forms marked data-ajax are sent in the background
         * (RespondToBackgroundForms turns the usual redirect into JSON), the
         * server's message shows as a toast, and the page's [data-admin-region]
         * sections are re-rendered from a fresh copy of the page — so the admin
         * never leaves or reloads the page. The form gets an "admin:saved" event.
         */
        window.adminSubmit = async function (form) {
            const buttons = [...form.elements].filter(el => el.type === 'submit');
            const wasDisabled = buttons.map(b => b.disabled);
            buttons.forEach(b => b.disabled = true);
            const restore = () => buttons.forEach((b, i) => b.disabled = wasDisabled[i]);

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
                if (res.status === 413) {
                    showToast('That upload is larger than the server accepts. Try fewer or smaller files.', 'error');
                    return restore();
                }

                const data = await res.json().catch(() => ({}));
                if (!res.ok) {
                    showToast(data.errors && Object.keys(data.errors).length
                        ? [...new Set(Object.values(data.errors).flat())].join(' ')
                        : (data.message || 'That could not be saved. Please try again.'), 'error');
                    return restore();
                }

                if (data.message) showToast(data.message, 'success');
                await refreshAdminRegions(form);
                restore();
                form.dispatchEvent(new CustomEvent('admin:saved', { bubbles: true, detail: data }));
            } catch (e) {
                showToast('Could not reach the server. Please try again.', 'error');
                restore();
            }
        };

        // A field the admin changed but hasn't saved yet
        const isDirty = (el) => el.type === 'checkbox' || el.type === 'radio' ? el.checked !== el.defaultChecked
            // a select with no option marked selected starts on its first option
            : el.tagName === 'SELECT' ? el.selectedIndex !== Math.max(0, [...el.options].findIndex(o => o.defaultSelected))
            : el.type !== 'file' && 'defaultValue' in el && el.value !== el.defaultValue;

        window.refreshAdminRegions = async function (savedForm) {
            const regions = [...document.querySelectorAll('[data-admin-region]')];
            if (!regions.length) return;

            const res = await fetch(location.href, { headers: { 'Accept': 'text/html', 'X-Live-Refresh': '1' }, cache: 'no-store' });
            if (!res.ok) return;
            const fresh = new DOMParser().parseFromString(await res.text(), 'text/html');
            const savedFields = savedForm ? [...savedForm.elements] : [];

            regions.forEach(region => {
                const next = fresh.querySelector(`[data-admin-region="${region.dataset.adminRegion}"]`);
                if (!next) return;
                // keep a section with unsaved edits, unless those edits are what was just saved
                const unsaved = [...region.querySelectorAll('input, select, textarea')].some(el => isDirty(el) && !savedFields.includes(el));
                if (!unsaved) region.innerHTML = next.innerHTML;
            });
            document.dispatchEvent(new CustomEvent('admin:regions-updated'));
        };

        document.addEventListener('submit', (e) => {
            if (!e.target.matches('form[data-ajax]') || e.defaultPrevented) return;
            e.preventDefault();
            adminSubmit(e.target);
        });
    </script>

    @include('layouts.toastify')

    @yield('script')
</body>
</html>
