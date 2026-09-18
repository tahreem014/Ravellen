<?php

use App\Http\Controllers\Admin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Auth::routes();

// Language
Route::get('index/{locale}', [App\Http\Controllers\HomeController::class, 'lang']);

// ── Core pages ──────────────────────────────────────────────────────────────
Route::get('/',        [App\Http\Controllers\HomeController::class, 'root'])->name('root');
Route::get('/shop',    [App\Http\Controllers\HomeController::class, 'shop'])->name('shop');
Route::get('/about',   fn() => view('pages.about'))->name('about');
Route::get('/contact', fn() => view('pages.contact'))->name('contact');

// ── Products ─────────────────────────────────────────────────────────────────
Route::get('/mystery-balls',  [App\Http\Controllers\HomeController::class, 'genericProductPage'])->defaults('type', 'mystery-balls')->name('mystery-balls');
Route::get('/mystery-scoops', [App\Http\Controllers\HomeController::class, 'genericProductPage'])->defaults('type', 'mystery-scoops')->name('mystery-scoops');
Route::get('/bts-merch',      fn() => view('pages.bts-merch'))->name('bts-merch');
Route::get('/ceramic-mug',    [App\Http\Controllers\HomeController::class, 'genericProductPage'])->defaults('type', 'ceramic')->name('ceramic-mug');
Route::get('/gift-box',       [App\Http\Controllers\HomeController::class, 'genericProductPage'])->defaults('type', 'gift-box')->name('gift-box');
Route::get('/category',        [App\Http\Controllers\HomeController::class, 'categoryPage'])->name('category');
Route::get('/gift-boxes',      fn() => app(App\Http\Controllers\HomeController::class)->categoryPage('gift-box'))->name('gift-boxes');
Route::get('/bts-gift-boxes',  fn() => app(App\Http\Controllers\HomeController::class)->categoryPage('bts-gift-box'))->name('bts-gift-boxes');
Route::get('/ceramic-items',   fn() => app(App\Http\Controllers\HomeController::class)->categoryPage('ceramic'))->name('ceramic-items');
Route::get('/product/{type}', [App\Http\Controllers\HomeController::class, 'genericProductPage'])->name('product-detail');

// ── Product detail by slug ────────────────────────────────────────────────────
Route::get('/product-item/{product:slug}', [App\Http\Controllers\HomeController::class, 'productDetail'])->name('product-item');

// ── Internal catalog data browser ──────────────────────────────────────────────
Route::get('/catalog-browser', [App\Http\Controllers\CatalogController::class, 'index'])->name('catalog.browser');

// ── Orders ────────────────────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::post('/orders', [App\Http\Controllers\OrderController::class, 'store'])->name('orders.store');
    Route::get('/orders/{order}', [App\Http\Controllers\OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/pay-remaining', [App\Http\Controllers\OrderController::class, 'payRemaining'])->name('orders.pay-remaining');
    Route::get('/checkout/shipping-fee', [App\Http\Controllers\CheckoutController::class, 'shippingFee'])->name('checkout.shipping-fee');
});

Route::get('/cart',     fn() => view('pages.add-to-cart'))->name('cart');
Route::get('/checkout', function () {
    $addresses = Auth::check() ? Auth::user()->addresses()->latest()->get() : collect();
    $defaultAddress = $addresses->firstWhere('is_default', true) ?? $addresses->first();

    return view('pages.proceed-to-checkout', compact('defaultAddress', 'addresses'));
})->name('checkout');

// ── Cart API (authenticated users persist to DB; guests use localStorage) ─────
Route::middleware('auth')->group(function () {
    Route::get('/cart/items',           [App\Http\Controllers\CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/items',          [App\Http\Controllers\CartController::class, 'store'])->name('cart.store');
    Route::put('/cart/items/{id}',      [App\Http\Controllers\CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/items/{id}',   [App\Http\Controllers\CartController::class, 'destroy'])->name('cart.destroy');
    Route::delete('/cart/items',        [App\Http\Controllers\CartController::class, 'clear'])->name('cart.clear');
});

// ── Wishlist ──────────────────────────────────────────────────────────────────
Route::get('/wish-list',         [App\Http\Controllers\WishlistController::class, 'index'])->name('wish-list');
Route::post('/wish-list/toggle', [App\Http\Controllers\WishlistController::class, 'toggle'])->name('wish-list.toggle');
Route::post('/wish-list/remove', [App\Http\Controllers\WishlistController::class, 'remove'])->name('wish-list.remove');

// ── User pages ────────────────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/track-order',        [App\Http\Controllers\TrackOrderController::class, 'index'])->name('track-order');
    Route::get('/track-order/lookup', [App\Http\Controllers\TrackOrderController::class, 'lookup'])->name('track-order.lookup');
});

// ── Notifications ─────────────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/notifications',            [App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/feed',       [App\Http\Controllers\NotificationController::class, 'feed'])->name('notifications.feed');
    Route::post('/notifications/{id}/read', [App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all',  [App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
});

// ── Profile ──────────────────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/profile',      [App\Http\Controllers\ProfileController::class, 'show'])->name('profile');
    Route::get('/profile/data', [App\Http\Controllers\ProfileController::class, 'get'])->name('profile.get');
    Route::put('/profile',          [App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile/password', [App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::delete('/profile',       [App\Http\Controllers\ProfileController::class, 'delete'])->name('profile.delete');

    Route::post('/reviews', [App\Http\Controllers\ReviewController::class, 'store'])->name('reviews.store');

    Route::post('/refund-requests',                 [App\Http\Controllers\RefundRequestController::class, 'store'])->name('refund-requests.store');
    Route::put('/refund-requests/{refundRequest}',  [App\Http\Controllers\RefundRequestController::class, 'update'])->name('refund-requests.update');
    Route::post('/refund-requests/{refundRequest}/cancel', [App\Http\Controllers\RefundRequestController::class, 'cancel'])->name('refund-requests.cancel');

    Route::get('/addresses',              [App\Http\Controllers\AddressController::class, 'index'])->name('addresses.index');
    Route::get('/addresses/{address}',    [App\Http\Controllers\AddressController::class, 'show'])->name('addresses.show');
    Route::post('/addresses',             [App\Http\Controllers\AddressController::class, 'store'])->name('addresses.store');
    Route::put('/addresses/{address}',    [App\Http\Controllers\AddressController::class, 'update'])->name('addresses.update');
    Route::delete('/addresses/{address}', [App\Http\Controllers\AddressController::class, 'destroy'])->name('addresses.destroy');

    // ── Wishlist (DB-backed CRUD, distinct from the session-based /wish-list heart-button feature) ──
    Route::get('/wishlist',               [App\Http\Controllers\WishlistController::class, 'list'])->name('wishlist.index');
    Route::put('/wishlist/{wishlist}',    [App\Http\Controllers\WishlistController::class, 'update'])->name('wishlist.update');
    Route::delete('/wishlist/{wishlist}', [App\Http\Controllers\WishlistController::class, 'destroy'])->name('wishlist.destroy');
});

// ── Google OAuth ──────────────────────────────────────────────────────────────
Route::get('/auth/google',          [App\Http\Controllers\Auth\LoginController::class, 'redirectToGoogle'])->name('google.login');
Route::get('/auth/google/callback', [App\Http\Controllers\Auth\LoginController::class, 'handleGoogleCallback'])->name('google.callback');

// ── Profile updates ───────────────────────────────────────────────────────────
Route::post('/update-profile/{id}',  [App\Http\Controllers\HomeController::class, 'updateProfile'])->name('updateProfile');
Route::post('/update-password/{id}', [App\Http\Controllers\HomeController::class, 'updatePassword'])->name('updatePassword');

// ── Admin ─────────────────────────────────────────────────────────────────────
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');

    Route::resource('products', Admin\ProductController::class)->except(['show']);
    Route::post('products/{product}/variants', [Admin\ProductVariantController::class, 'store'])->name('products.variants.store');
    Route::put('products/{product}/variants/{variant}', [Admin\ProductVariantController::class, 'update'])->name('products.variants.update');
    Route::delete('products/{product}/variants/{variant}', [Admin\ProductVariantController::class, 'destroy'])->name('products.variants.destroy');
    Route::post('products/{product}/images', [Admin\ProductImageController::class, 'store'])->name('products.images.store');
    Route::delete('products/{product}/images/{image}', [Admin\ProductImageController::class, 'destroy'])->name('products.images.destroy');

    Route::post('categories', [Admin\CategoryController::class, 'store'])->name('categories.store');
    Route::delete('categories/{category}', [Admin\CategoryController::class, 'destroy'])->name('categories.destroy');
    Route::post('sub-categories', [Admin\SubCategoryController::class, 'store'])->name('sub-categories.store');
    Route::delete('sub-categories/{subCategory}', [Admin\SubCategoryController::class, 'destroy'])->name('sub-categories.destroy');

    Route::get('orders', [Admin\OrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{order}', [Admin\OrderController::class, 'show'])->name('orders.show');
    Route::put('orders/{order}', [Admin\OrderController::class, 'update'])->name('orders.update');

    Route::get('pre-orders', [Admin\OrderController::class, 'preOrders'])->name('pre-orders.index');
    Route::post('pre-orders/{order}/collect-remaining', [Admin\OrderController::class, 'collectRemaining'])->name('pre-orders.collect-remaining');

    Route::get('refund-requests', [Admin\RefundRequestController::class, 'index'])->name('refund-requests.index');
    Route::put('refund-requests/{refundRequest}', [Admin\RefundRequestController::class, 'update'])->name('refund-requests.update');

    Route::get('users', [Admin\UserController::class, 'index'])->name('users.index');
    Route::post('users', [Admin\UserController::class, 'store'])->name('users.store');
    Route::put('users/{user}', [Admin\UserController::class, 'update'])->name('users.update');
    Route::post('users/{user}/toggle-block', [Admin\UserController::class, 'toggleBlock'])->name('users.toggle-block');
    Route::delete('users/{user}', [Admin\UserController::class, 'destroy'])->name('users.destroy');

    Route::get('settings', [Admin\SettingController::class, 'edit'])->name('settings.edit');
    Route::put('settings', [Admin\SettingController::class, 'update'])->name('settings.update');

    Route::get('shipping-rates', [Admin\ShippingRateController::class, 'index'])->name('shipping-rates.index');
    Route::post('shipping-rates', [Admin\ShippingRateController::class, 'store'])->name('shipping-rates.store');
    Route::put('shipping-rates/{shippingRate}', [Admin\ShippingRateController::class, 'update'])->name('shipping-rates.update');
    Route::post('shipping-rates/{shippingRate}/toggle-active', [Admin\ShippingRateController::class, 'toggleActive'])->name('shipping-rates.toggle-active');
    Route::delete('shipping-rates/{shippingRate}', [Admin\ShippingRateController::class, 'destroy'])->name('shipping-rates.destroy');
});

// ── Catch-all (must be last) ──────────────────────────────────────────────────
Route::get('{any}', [App\Http\Controllers\HomeController::class, 'index'])->name('index');
