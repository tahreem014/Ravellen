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

    // How each member is named in "coming soon" messages
    public const BTS_BIAS_NAMES = [
        'jungkook' => 'Jungkook', 'taehyung' => 'Taehyung', 'jimin' => 'Jimin',
        'namjoon'  => 'Namjoon',  'jin'      => 'Jin',      'jhope' => 'J-Hope',
        'suga'     => 'Suga',     'ot7'      => 'OT7',      'bt21'  => 'BT21',
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

        return view('pages.shop', [
            'categories'    => $categories,
            'btsMemberUrls' => $this->btsMemberUrls($categories),
        ]);
    }

    /**
     * Where the shop sends the BTS Merch products, keyed by product id: the member pages the BTS Merch
     * page's picker opens (/mystery-scoops?bias=… and /mystery-balls?bias=…), not the plain product page.
     * Those pages show a member's first Scoop / Balls product, so only that product gets the link.
     *
     * @return array<int, string>
     */
    private function btsMemberUrls($categories): array
    {
        $members = array_flip(self::BTS_BIAS_SUB_CATEGORIES);   // sub-category name => bias key
        $urls    = [];

        foreach ($categories->firstWhere('name', 'BTS Merch')?->subCategories ?? [] as $subCategory) {
            $bias = $members[$subCategory->name] ?? null;

            foreach (['Scoop' => 'mystery-scoops', 'Balls' => 'mystery-balls'] as $word => $route) {
                $first = $bias ? $subCategory->products->sortBy('id')->first(fn ($product) => \Illuminate\Support\Str::contains($product->name, $word, ignoreCase: true)) : null;

                if ($first) {
                    $urls[$first->id] = route($route, ['bias' => $bias]);
                }
            }
        }

        return $urls;
    }

    /**
     * BTS Merch landing page. Picking a bias shows the products that member really has in the database:
     * their first Scoop and first Balls (the same two the member pages /mystery-scoops?bias=… and
     * /mystery-balls?bias=… open). A member with neither shows "Coming soon", nothing is made up.
     */
    public function btsMerch()
    {
        $products = \App\Models\Product::with(['variants', 'images', 'subCategory'])
            ->whereHas('category', fn ($q) => $q->where('name', 'BTS Merch'))
            ->orderBy('id')
            ->get();

        $biasProducts = [];

        foreach (self::BTS_BIAS_SUB_CATEGORIES as $bias => $subCategory) {
            $memberProducts = $products->filter(fn ($p) => $p->subCategory?->name === $subCategory);

            $biasProducts[$bias] = [
                'scoop' => $this->btsCard($memberProducts->first(fn ($p) => \Illuminate\Support\Str::contains($p->name, 'Scoop', ignoreCase: true)), 'scoop', $bias),
                'balls' => $this->btsCard($memberProducts->first(fn ($p) => \Illuminate\Support\Str::contains($p->name, 'Balls', ignoreCase: true)), 'balls', $bias),
            ];
        }

        // No BTS Merch products yet: the page says "Coming soon" instead of offering a bias to pick
        $comingSoon = $products->isEmpty();

        return view('pages.bts-merch', compact('biasProducts', 'comingSoon'));
    }

    /**
     * What a BTS Merch card shows for one of a member's products: its name, photo, description, starting
     * price (sale price applied) and the member page it opens. Null when the member has no such product.
     */
    private function btsCard(?\App\Models\Product $product, string $kind, string $bias): ?array
    {
        if (! $product) {
            return null;
        }

        $pack     = $kind === 'balls' ? ($product->packOptions()[0] ?? null) : null;   // smallest pack
        $cheapest = $product->cheapestVariant();

        if ($pack) {
            $price = ['label' => $pack['qty'] . ' balls for', 'amount' => $pack['price'], 'original' => $pack['original']];
        } elseif ($cheapest) {
            $price = ['label' => 'Starting from', 'amount' => $cheapest->effectivePrice(), 'original' => $cheapest->hasDiscount() ? (float) $cheapest->price : null];
        } else {
            $price = null;
        }

        return [
            'id'          => $product->id,
            'name'        => $product->name,
            'description' => $product->description,
            'image'       => $product->images->map(fn ($image) => $image->image_url)->filter()->first(),   // photos only, not video rows
            'min'         => $pack['qty'] ?? null,
            'price'       => $price,
            'url'         => route($kind === 'balls' ? 'mystery-balls' : 'mystery-scoops', ['bias' => $bias]),
        ];
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
     * Generic product-detail pages (mystery-balls / mystery-scoops with ?bias=,
     * bts-scoop, bts-balls, ceramic, gift-box). For the mystery types it looks
     * up the canonical DB product — or, with ?bias= from the BTS Merch page,
     * that member's product — so prices, discounts and the variant ids sent to
     * the cart API all come from the database. Mystery Scoops / Balls without
     * ?bias= redirect to the shop's product page (product-item).
     */
    public function genericProductPage(string $type): \Illuminate\View\View|\Illuminate\Http\RedirectResponse
    {
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

        // Whichever category the page sells from: with no products in it there is nothing to show but "Coming soon".
        $categoryName = match (true) {
            $map !== null        => $bias ? 'BTS Merch' : $map['category'],
            $type === 'ceramic'  => 'Ceramic Items',
            $type === 'gift-box' => 'Gift Boxes',
            default              => null,
        };
        if ($categoryName && ! \App\Models\Product::whereHas('category', fn ($q) => $q->where('name', $categoryName))->exists()) {
            return view('pages.coming-soon', ['name' => $categoryName]);
        }

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

            // A member who has no such product yet: "Coming soon", not a product page with nothing behind it
            if (! $product && isset($biasSubCategories[$bias])) {
                return view('pages.coming-soon', [
                    'name'    => 'BTS Merch',
                    'heading' => self::BTS_BIAS_NAMES[$bias] . "'s " . ($isBalls ? 'Mystery Balls are on their way' : 'Mystery Scoop is on its way'),
                ]);
            }

            // Without ?bias= (the BTS Merch pages' member picker), /mystery-scoops and /mystery-balls
            // are the shop's product page for that category — there is no page of their own.
            if ($product && ! $bias && $map['name']) {
                return redirect()->route('product-item', $product->slug);
            }
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
