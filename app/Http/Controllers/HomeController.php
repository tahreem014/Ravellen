<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class HomeController extends Controller
{
    // bias keys used by the BTS Merch page → that member's BTS Merch sub-category
    public const BTS_BIAS_SUB_CATEGORIES = [
        'jungkook' => 'Jungkook',     'taehyung' => 'V (Taehyung)', 'jimin' => 'Jimin',
        'namjoon'  => 'RM (Namjoon)', 'jin'      => 'Jin',          'jhope' => 'J-Hope',
        'suga'     => 'SUGA',         'ot7'      => 'OT7',          'bt21'  => 'BT21',
    ];

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    // public function __construct()
    // {
    //     $this->middleware('auth');
    // }

    public function root()
    {
        $categories = Category::with([
            'subCategories.products.variants',
            'subCategories.products.images',
            'products.variants',
            'products.images',
        ])->orderBy('name')->get();

        return view('pages.home', compact('categories'));
    }

    public function shop()
    {
        $categories = Category::with([
            'subCategories.products.variants',
            'subCategories.products.images',
            'products.variants',
            'products.images',
        ])->orderBy('name')->get();

        return view('pages.shop', compact('categories'));
    }

    /**
     * BTS Merch landing page. Picking a bias shows that member's starting
     * prices, read from the same products the bias product pages sell.
     */
    public function btsMerch()
    {
        $products = \App\Models\Product::with(['variants', 'subCategory'])
            ->whereHas('category', fn ($q) => $q->where('name', 'BTS Merch'))
            ->get();

        $biasPrices = [];

        foreach (self::BTS_BIAS_SUB_CATEGORIES as $bias => $subCategory) {
            $memberProducts = $products->filter(fn ($p) => $p->subCategory?->name === $subCategory);
            $scoop = $memberProducts->first(fn ($p) => str_contains($p->name, 'Scoop'))?->cheapestVariant();
            $balls = $memberProducts->first(fn ($p) => str_contains($p->name, 'Balls'));

            $biasPrices[$bias] = [
                'scoop' => $scoop ? ['price' => $scoop->effectivePrice(), 'original' => $scoop->hasDiscount() ? (float) $scoop->price : null] : null,
                'balls' => $balls ? ($balls->packOptions()[0] ?? null) : null,   // smallest pack
            ];
        }

        return view('pages.bts-merch', compact('biasPrices'));
    }

    public function categoryPage(string $section = 'gift-box')
    {
        // Determine which DB category to load based on the section
        $categoryNameMap = [
            'gift-box'     => ['Gift Boxes'],
            'bts-gift-box' => ['BTS Gift Boxes'],
            'ceramic'      => ['Ceramic Items'],
        ];

        $names = $categoryNameMap[$section] ?? [];

        $dbCategories = Category::with([
            'subCategories.products.variants',
            'subCategories.products.images',
            'products.variants',
            'products.images',
        ])->whereIn('name', $names)->get();

        return view('pages.category', compact('section', 'dbCategories'));
    }

    public function productDetail(\App\Models\Product $product)
    {
        // A "coming soon" category sells nothing yet, so its product pages go to its landing page.
        $comingSoonRoute = Category::COMING_SOON[$product->category?->name] ?? null;
        if ($comingSoonRoute) {
            return redirect()->route($comingSoonRoute);
        }

        $product->load([
            'variants',
            'images',
            'category',
            'subCategory',
            'reviews.user',
            'reviews.images',
        ]);

        $productReviews = $product->reviews->sortByDesc('created_at')->values();
        $reviewCount    = $productReviews->count();
        $avgRating      = $reviewCount ? round($productReviews->avg('rating'), 1) : 0;

        $relatedProducts = \App\Models\Product::with(['images', 'variants'])
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->latest()
            ->take(5)
            ->get();

        $categoryName = $product->category?->name ?? '';
        $type = match(true) {
            str_contains($categoryName, 'Ceramic')        => 'ceramic',
            str_contains($categoryName, 'Gift Boxes')     => 'gift-box',
            str_contains($categoryName, 'BTS Merch')      => 'bts-scoop',
            str_contains($categoryName, 'Mystery Scoops') => 'mystery-scoops',
            str_contains($categoryName, 'Mystery Balls')  => 'mystery-balls',
            default => 'gift-box',
        };

        return view('pages.product-detail', [
            'type'           => 'product-item',
            'product'        => $product,
            'theme_type'     => $type,
            'dbProductMap'   => ['product_id' => null, 'variants' => []],
            'productReviews'   => $productReviews,
            'reviewCount'      => $reviewCount,
            'avgRating'        => $avgRating,
            'relatedProducts'  => $relatedProducts,
        ]);
    }

    /**
     * Generic product-detail pages (mystery-balls, mystery-scoops, bts-scoop,
     * bts-balls, ceramic, gift-box). For the mystery types it looks up the
     * canonical DB product — or, with ?bias= from the BTS Merch page, that
     * member's product — so prices, discounts and the variant ids sent to the
     * cart API all come from the database.
     */
    public function genericProductPage(string $type): \Illuminate\View\View|\Illuminate\Http\RedirectResponse
    {
        // The static "Cute Ceramic Mug" page: ceramic is coming soon, nothing to sell yet.
        if ($type === 'ceramic' && Category::nameIsComingSoon('Ceramic Items')) {
            return redirect()->route(Category::COMING_SOON['Ceramic Items']);
        }

        $categoryMap = [
            'mystery-balls'  => ['category' => 'Mystery Balls',  'name' => 'Original Mystery Balls'],
            'mystery-scoops' => ['category' => 'Mystery Scoops', 'name' => 'Original Mystery Scoop'],
            'bts-scoop'      => ['category' => 'BTS Merch',      'name' => null],
            'bts-balls'      => ['category' => 'BTS Merch',      'name' => null],
        ];

        $biasSubCategories = self::BTS_BIAS_SUB_CATEGORIES;

        $map     = $categoryMap[$type] ?? null;
        $bias    = request('bias');
        $product = null;

        if ($map) {
            $isBalls = str_contains($type, 'balls');
            $query   = \App\Models\Product::with(['variants', 'images', 'reviews.user', 'reviews.images']);

            if ($bias || ! $map['name']) {
                $query->whereHas('category', fn ($q) => $q->where('name', 'BTS Merch'))
                    ->where('name', 'like', $isBalls ? '%Balls%' : '%Scoop%');

                if (isset($biasSubCategories[$bias])) {
                    $query->whereHas('subCategory', fn ($q) => $q->where('name', $biasSubCategories[$bias]));
                }
            } else {
                $query->whereHas('category', fn ($q) => $q->where('name', $map['category']))
                    ->orderByRaw('name = ? desc', [$map['name']]);
            }

            $product = $query->orderBy('id')->first();
        }

        return view('pages.product-detail', [
            'type'           => $type,
            'genericProduct' => $product,
        ]);
    }

    /*Language Translation*/
    public function lang($locale)
    {
        if ($locale) {
            App::setLocale($locale);
            Session::put('lang', $locale);
            Session::save();
            return redirect()->back()->with('locale', $locale);
        } else {
            return redirect()->back();
        }
    }

    public function updateProfile(Request $request, $id)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:1024'],
        ]);

        $user = User::find($id);
        $user->name = $request->get('name');
        $user->email = $request->get('email');

        if ($request->file('avatar')) {
            $avatar = $request->file('avatar');
            $avatarName = time() . '.' . $avatar->getClientOriginalExtension();
            $avatarPath = public_path('/images/');
            $avatar->move($avatarPath, $avatarName);
            $user->avatar =  $avatarName;
        }

        $user->update();
        if ($user) {
            Session::flash('message', 'User Details Updated successfully!');
            Session::flash('alert-class', 'alert-success');
            // return response()->json([
            //     'isSuccess' => true,
            //     'Message' => "User Details Updated successfully!"
            // ], 200); // Status code here
            return redirect()->back();
        } else {
            Session::flash('message', 'Something went wrong!');
            Session::flash('alert-class', 'alert-danger');
            // return response()->json([
            //     'isSuccess' => true,
            //     'Message' => "Something went wrong!"
            // ], 200); // Status code here
            return redirect()->back();

        }
    }

    public function updatePassword(Request $request, $id)
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        if (!(Hash::check($request->get('current_password'), Auth::user()->password))) {
            return response()->json([
                'isSuccess' => false,
                'Message' => "Your Current password does not matches with the password you provided. Please try again."
            ], 200); // Status code
        } else {
            $user = User::find($id);
            $user->password = Hash::make($request->get('password'));
            $user->update();
            if ($user) {
                Session::flash('message', 'Password updated successfully!');
                Session::flash('alert-class', 'alert-success');
                return response()->json([
                    'isSuccess' => true,
                    'Message' => "Password updated successfully!"
                ], 200); // Status code here
            } else {
                Session::flash('message', 'Something went wrong!');
                Session::flash('alert-class', 'alert-danger');
                return response()->json([
                    'isSuccess' => true,
                    'Message' => "Something went wrong!"
                ], 200); // Status code here
            }
        }
    }
}
