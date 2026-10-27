@php
    $currentPage = $currentPage ?? request()->path();

    $navItems = [
        ['name' => 'Home',       'path' => '/'],
        ['name' => 'Shop',       'path' => 'shop'],
        // ['name' => 'Categories', 'path' => 'categories'],
        ['name' => 'About',      'path' => 'about'],
        ['name' => 'Contact',    'path' => 'contact'],
    ];
@endphp

<style>
    .navbar-rv {
        position: sticky; top: 0; z-index: 1050;
        background: #fff;
        border-bottom: none;
        box-shadow: 0 2px 8px rgba(0,0,0,.08);
    }
    .navbar-rv .inner {
        display: flex; align-items: center; justify-content: space-between;
        height: 4rem;
    }
    /* Logo */
    .navbar-rv .rv-logo svg { width: 2.4rem; height: 2.4rem; }

    /* Nav links */
    .navbar-rv .rv-nav { display: flex; align-items: center; gap: .25rem; }
    .navbar-rv .rv-nav a {
        font-size: .875rem; font-weight: 500; color: #1f2937;
        text-decoration: none; padding: .4rem .75rem;
        border-bottom: 2px solid transparent;
        transition: color .2s, border-color .2s;
        display: inline-block;
    }
    .navbar-rv .rv-nav a:hover { color: #a855f7; }
    .navbar-rv .rv-nav a.active {
        color: #a855f7;
        border-bottom: 2px solid #a855f7;
    }

    /* Right actions */
    .navbar-rv .rv-actions { display: flex; align-items: center; gap: .5rem; }
    .navbar-rv .rv-icon-btn {
        background: transparent; border: 0; padding: .4rem;
        color: #374151; border-radius: .375rem;
        position: relative; line-height: 1; cursor: pointer;
        transition: color .2s;
        text-decoration: none !important;
        border-bottom: none !important;
        outline: none;
    }
    .navbar-rv .rv-icon-btn:hover { color: #a855f7; text-decoration: none !important; }
    .navbar-rv .cart-badge {
        position: absolute; top: -3px; right: -3px;
        background: #ef4444; color: #fff;
        font-size: .6rem; font-weight: 700;
        width: 1rem; height: 1rem;
        border-radius: 50%; display: flex; align-items: center; justify-content: center;
    }
    .navbar-rv .notif-dropdown {
        position: absolute; right: 0; top: calc(100% + .5rem);
        width: 320px; max-height: 380px; overflow-y: auto;
        background: #fff; border-radius: .75rem;
        box-shadow: 0 8px 24px rgba(0,0,0,.15);
        z-index: 1060; display: none;
    }
    .navbar-rv .notif-dropdown.show { display: block; }
    .navbar-rv .notif-dropdown-header {
        display: flex; align-items: center; justify-content: space-between;
        padding: .75rem 1rem; border-bottom: 1px solid #f3f4f6;
    }
    .navbar-rv .notif-dropdown-header h6 { margin: 0; font-size: .85rem; font-weight: 700; }
    .navbar-rv .notif-dropdown-header button {
        background: none; border: 0; color: #a855f7; font-size: .75rem; font-weight: 600; cursor: pointer;
    }
    .navbar-rv .notif-dropdown-item {
        display: flex; gap: .6rem; align-items: flex-start;
        padding: .7rem 1rem; border-bottom: 1px solid #f3f4f6;
        text-decoration: none !important; color: #1f2937 !important;
        font-size: .82rem;
    }
    .navbar-rv .notif-dropdown-item:hover { background: #fdf2f8; }
    .navbar-rv .notif-dropdown-item .notif-dot {
        width: .5rem; height: .5rem; border-radius: 50%; margin-top: .3rem; flex-shrink: 0;
        background: #a855f7;
    }
    .navbar-rv .notif-dropdown-item.read .notif-dot { background: transparent; }
    .navbar-rv .notif-dropdown-time { font-size: .7rem; color: #9ca3af; }
    .navbar-rv .notif-dropdown-empty { padding: 1.5rem 1rem; text-align: center; color: #9ca3af; font-size: .82rem; }
    .navbar-rv .notif-dropdown-footer {
        display: block; text-align: center; padding: .6rem; font-size: .8rem;
        font-weight: 600; color: #a855f7; text-decoration: none !important;
        border-top: 1px solid #f3f4f6;
    }
    .navbar-rv .notif-dropdown-footer:hover { background: #fdf2f8; }

    /* Login / Register buttons: in the top bar and in the mobile menu (which sits outside .navbar-rv) */
    .rv-login-btn, .rv-register-btn {
        border-radius: .5rem;
        padding: .45rem 1.25rem;
        font-size: .875rem; font-weight: 600;
        text-decoration: none !important; cursor: pointer;
        transition: background .2s, color .2s;
    }
    .rv-login-btn { background: #111827; color: #fff; border: 1.5px solid #111827; }
    .rv-login-btn:hover { background: #374151; border-color: #374151; color: #fff; }
    .rv-register-btn { background: #fff; color: #111827; border: 1.5px solid #111827; }
    .rv-register-btn:hover { background: #f3f4f6; color: #111827; }
    .offcanvas-rv .rv-login-btn, .offcanvas-rv .rv-register-btn { padding: .7rem 1.25rem; font-size: .95rem; }

    /* Avatar link */
    .navbar-rv .rv-avatar-btn {
        display: inline-block; padding: 0;
        border-radius: 50%; line-height: 1;
        width: 2.3rem; height: 2.3rem; overflow: hidden;
        border: 2px solid #f3e8ff; transition: border-color .2s;
    }
    .navbar-rv .rv-avatar-btn:hover { border-color: #a855f7; }
    .navbar-rv .rv-avatar-btn img { width: 100%; height: 100%; object-fit: cover; display: block; }

    .navbar-rv .rv-back-btn { display: inline-flex; align-items: center; margin-left: -.4rem; }

    /* Phones: heart, cart, bell, avatar and menu button share one row, and the bell's
       dropdown spans the screen (at 320px wide it would run off the left edge) */
    @media (max-width: 575.98px) {
        .navbar-rv .rv-actions { gap: .1rem; }
        .navbar-rv .rv-avatar-btn { margin: 0 .25rem; }
        .navbar-rv .notif-dropdown { position: fixed; left: .5rem; right: .5rem; top: 4.25rem; width: auto; }
    }

    /* Mobile offcanvas — above the sticky navbar (1050), or it covers the menu's header and close button */
    .offcanvas-rv { z-index: 1100; }
    .offcanvas-backdrop { z-index: 1090; }
    .offcanvas-rv .nav-link-m {
        display: block; padding: .55rem .75rem; border-radius: .375rem;
        color: #374151; text-decoration: none; font-size: .9rem;
        transition: background .2s;
    }
    .offcanvas-rv .nav-link-m:hover,
    .offcanvas-rv .nav-link-m.active { background: #f3e8ff; color: #7c3aed; }
</style>

<nav class="navbar-rv">
    <div class="container">
        <div class="inner">

            <div class="d-flex align-items-center gap-1">
                {{-- Back: to the page the visitor came from; if they arrived straight on this page
                     (a shared link, a new tab) the href is where it would sensibly go up to --}}
                @if($currentPage !== '/')
                    <a href="{{ request()->is('product-item/*', 'product/*', 'gift-box') ? url('/shop') : url('/') }}"
                       class="rv-icon-btn rv-back-btn" id="rv-back-btn" aria-label="Go back" title="Back"
                       onclick="return rvGoBack(event)">
                        <i class="ri-arrow-left-line fs-5"></i>
                    </a>
                @endif

                {{-- Logo --}}
                <a href="{{ url('/') }}" class="rv-logo text-decoration-none">
                    <img src="{{ asset('images/ravellen-logo.png') }}" alt="Ravellen Logo" height="40">
                </a>
            </div>

            {{-- Desktop Nav --}}
            <div class="rv-nav d-none d-lg-flex">
                @foreach($navItems as $item)
                    @php
                        $path = trim($item['path'], '/');
                        $isActive = ($path === '' && $currentPage === '/') || ($path !== '' && $currentPage === $path);
                    @endphp
                    <a href="{{ url($item['path']) }}" class="{{ $isActive ? 'active' : '' }}">
                        {{ $item['name'] }}
                    </a>
                @endforeach
            </div>

            {{-- Right Actions --}}
            <div class="rv-actions">

                
                {{-- Wishlist and cart for everyone: guests keep theirs in the session / this browser --}}
                @php $wishlistCount = auth()->check() ? auth()->user()->wishLists()->count() : count(session('wishlist', [])); @endphp
               
                <a href="{{ route('wish-list') }}" class="rv-icon-btn ">
                    <i class="ri-heart-line fs-5"></i>
                    <span class="cart-badge" id="wishlist-badge" style="{{ $wishlistCount > 0 ? '' : 'display:none;' }}">{{ $wishlistCount > 99 ? '99+' : $wishlistCount }}</span>
                </a>
                {{-- Cart --}}
                <a href="{{ url('cart') }}" class="rv-icon-btn">
                    <i class="ri-shopping-cart-line fs-5"></i>
                    <span class="cart-badge" id="cart-badge" style="display:none;"></span>
                </a>
                {{-- Notifications: for everyone; a guest's dropdown invites them to sign in --}}
                 <div class="position-relative">
                    <button type="button" class="rv-icon-btn" id="notif-bell-btn" onclick="toggleNotifDropdown()" aria-label="Notifications">
                        <i class="ri-notification-3-line fs-5"></i>
                        <span class="cart-badge" id="notif-badge" style="display:none;"></span>
                    </button>
                    <div class="notif-dropdown" id="notif-dropdown">
                        @auth
                        <div class="notif-dropdown-header">
                            <h6>Notifications</h6>
                            <button type="button" onclick="markAllNotificationsRead(event)">Mark all as read</button>
                        </div>
                        <div id="notif-dropdown-list">
                            <div class="notif-dropdown-empty">Loading…</div>
                        </div>
                        <a href="{{ route('notifications.index') }}" class="notif-dropdown-footer">View all</a>
                        @else
                        <div class="notif-dropdown-header">
                            <h6>Notifications</h6>
                        </div>
                        <div class="notif-dropdown-empty">
                            Sign in to see your order updates and notifications.
                            <div class="d-flex gap-2 justify-content-center mt-3">
                                <a href="{{ route('login') }}" class="rv-login-btn">Login</a>
                                <a href="{{ route('register') }}" class="rv-register-btn">Register</a>
                            </div>
                        </div>
                        @endauth
                    </div>
                </div>

                {{-- Profile: signed-in only --}}
                @auth
                <a href="{{ url('/profile') }}" class="rv-avatar-btn" title="{{ auth()->user()->name }}">
                    <img src="{{ auth()->user()->avatar ? asset('images/'.auth()->user()->avatar) : asset('images/default-avatar.png') }}" alt="{{ auth()->user()->name }}">
                </a>
                @else
                    <a href="{{ route('login') }}" class="rv-login-btn d-none d-sm-inline-block">Login</a>
                @endauth

                {{-- Mobile toggle --}}
                <button class="rv-icon-btn d-lg-none"
                        type="button"
                        data-bs-toggle="offcanvas"
                        data-bs-target="#mobileNav"
                        aria-controls="mobileNav">
                    <i class="ri-menu-line fs-5"></i>
                </button>

            </div>
        </div>
    </div>
</nav>

{{-- Mobile Offcanvas --}}
<div class="offcanvas offcanvas-end offcanvas-rv" tabindex="-1" id="mobileNav" style="width:300px;">
    <div class="offcanvas-header border-bottom">
        <h6 class="offcanvas-title fw-bold">Menu</h6>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body d-flex flex-column gap-2 pt-3">

        @foreach($navItems as $item)
            @php
                $path = trim($item['path'], '/');
                $isActive = ($path === '' && $currentPage === '/') || ($path !== '' && $currentPage === $path);
            @endphp
            <a href="{{ url($item['path']) }}"
               class="nav-link-m {{ $isActive ? 'active' : '' }}">
                {{ $item['name'] }}
            </a>
        @endforeach

        <hr>

        @auth
            <a href="{{ url('/profile') }}"
               class="nav-link-m d-flex align-items-center gap-2">
                <img src="{{ auth()->user()->avatar ? asset('images/'.auth()->user()->avatar) : asset('images/default-avatar.png') }}"
                     alt="{{ auth()->user()->name }}"
                     style="width:1.6rem;height:1.6rem;border-radius:50%;object-fit:cover;">
                {{ auth()->user()->name }}
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        class="nav-link-m w-100 text-start border-0 bg-transparent text-danger d-flex align-items-center gap-2">
                    <i class="ri-logout-box-r-line"></i> Logout
                </button>
            </form>
        @else
            <a href="{{ route('login') }}"
               class="rv-login-btn text-center w-100 d-block">Login</a>
            <a href="{{ route('register') }}" class="rv-register-btn text-center w-100 d-block">Register</a>
        @endauth

    </div>
</div>

<script>
    // Back arrow: return to the page the visitor came from (the browser restores it as they left it,
    // including the shop / BTS selections kept in the URL). With no earlier page on this site, follow
    // the link's own href instead.
    function rvGoBack(e) {
        let cameFromThisSite = false;
        try { cameFromThisSite = !!document.referrer && new URL(document.referrer).origin === location.origin; } catch (err) {}

        if (cameFromThisSite && history.length > 1) {
            e.preventDefault();
            history.back();
            return false;
        }
        return true;
    }

    function updateCartBadge() {
        let cart = [];
        try { cart = JSON.parse(localStorage.getItem('rv_cart') || '[]'); } catch (e) {}

        const count = cart.reduce((sum, item) => sum + (item.qty || 0), 0);
        const badge = document.getElementById('cart-badge');
        if (!badge) return;

        if (count > 0) {
            badge.textContent = count > 99 ? '99+' : count;
            badge.style.display = 'flex';
        } else {
            badge.style.display = 'none';
        }
    }
    document.addEventListener('DOMContentLoaded', updateCartBadge);

    function updateWishlistBadge(count) {
        const badge = document.getElementById('wishlist-badge');
        if (!badge) return;

        if (count > 0) {
            badge.textContent = count > 99 ? '99+' : count;
            badge.style.display = 'flex';
        } else {
            badge.style.display = 'none';
        }
    }
    window.updateWishlistBadge = updateWishlistBadge;

    @auth
    const NOTIF_CSRF_TOKEN = '{{ csrf_token() }}';

    function updateNotifBadge(count) {
        const badge = document.getElementById('notif-badge');
        if (!badge) return;

        if (count > 0) {
            badge.textContent = count > 99 ? '99+' : count;
            badge.style.display = 'flex';
        } else {
            badge.style.display = 'none';
        }
    }

    function renderNotifDropdown(notifications) {
        const list = document.getElementById('notif-dropdown-list');
        if (!list) return;

        if (!notifications.length) {
            list.innerHTML = '<div class="notif-dropdown-empty">You don\'t have any notifications yet.</div>';
            return;
        }

        list.innerHTML = notifications.map(n => `
            <a href="#" class="notif-dropdown-item ${n.read ? 'read' : ''}" data-id="${n.id}" data-url="${n.action_url ?? ''}">
                <span class="notif-dot"></span>
                <div>
                    <div>${n.message}</div>
                    <div class="notif-dropdown-time">${n.created_at}</div>
                </div>
            </a>
        `).join('');

        list.querySelectorAll('.notif-dropdown-item').forEach(el => {
            el.addEventListener('click', async (e) => {
                e.preventDefault();
                const id = el.getAttribute('data-id');
                const url = el.getAttribute('data-url');

                try {
                    await fetch(`/notifications/${id}/read`, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': NOTIF_CSRF_TOKEN, 'Accept': 'application/json' },
                    });
                } catch (err) {}

                if (url) {
                    window.location.href = url;
                } else {
                    loadNotifFeed();
                }
            });
        });
    }

    async function loadNotifFeed() {
        try {
            const res = await fetch('{{ route("notifications.feed") }}', {
                headers: { 'Accept': 'application/json' },
            });
            const data = await res.json();

            updateNotifBadge(data.unread_count);
            renderNotifDropdown(data.notifications);
            return data;
        } catch (e) {}
    }

    function toggleNotifDropdown() {
        const dropdown = document.getElementById('notif-dropdown');
        if (!dropdown) return;

        const willShow = !dropdown.classList.contains('show');
        dropdown.classList.toggle('show', willShow);

        if (willShow) {
            loadNotifFeed();
        }
    }

    async function markAllNotificationsRead(event) {
        event.stopPropagation();

        try {
            await fetch('/notifications/read-all', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': NOTIF_CSRF_TOKEN, 'Accept': 'application/json' },
            });
        } catch (e) {}

        loadNotifFeed();
    }

    document.addEventListener('click', (e) => {
        const dropdown = document.getElementById('notif-dropdown');
        const bellBtn = document.getElementById('notif-bell-btn');
        if (!dropdown || !dropdown.classList.contains('show')) return;
        if (dropdown.contains(e.target) || bellBtn.contains(e.target)) return;
        dropdown.classList.remove('show');
    });

    document.addEventListener('DOMContentLoaded', loadNotifFeed);

    // The unread count is checked by layouts/live-updates every few seconds;
    // refresh the bell when it changes and pop up anything new.
    document.addEventListener('live:notifications', async (e) => {
        const data = await loadNotifFeed();
        const newest = data?.notifications?.find(n => !n.read);
        if (e.detail.increased && newest && window.showToast) {
            showToast(newest.message, 'info');
        }
    });
    @else
    // Guests: the bell just opens its "sign in" note
    function toggleNotifDropdown() {
        document.getElementById('notif-dropdown')?.classList.toggle('show');
    }

    document.addEventListener('click', (e) => {
        const dropdown = document.getElementById('notif-dropdown');
        const bellBtn = document.getElementById('notif-bell-btn');
        if (!dropdown || !dropdown.classList.contains('show')) return;
        if (dropdown.contains(e.target) || bellBtn.contains(e.target)) return;
        dropdown.classList.remove('show');
    });
    @endauth
</script>
