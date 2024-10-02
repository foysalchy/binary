<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\Category;
use App\Models\CatFaq;
use App\Models\Subcategory;
use App\Models\Prosubcategory;
use App\Models\Proprocategory;
use App\Models\Product;
use App\Models\Brand;
use App\Models\ProductBrand;
use App\Models\ProductImage;
use App\Models\ProductEmi;
use App\Models\SiteSetting;
use Hash;
use App\Models\ShopType;
use App\Models\Customer;
use App\Models\Office;
use App\Models\Post;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Page;
use App\Models\Banner;
use App\Models\Component;
use App\Models\Background;
use App\Models\ProductStockStatus;
use App\Models\Post_tag;
use App\Models\ProductHighlight;
use App\Models\ProductFaq;
use App\Models\ProductTerms;
use App\Models\FreeItemForClient;

use Str;
use \Cache;

class SearchController extends Controller
{
    public function MakeUrl($slug)
    {
        /*CATEGORY - TEMPLATE*/
        $category = Category::where('slug', $slug)->first();
        if ($category) {
            if($category->status == 1){
                $cat_child = Subcategory::where('category_id', $category->id)
                ->where('status','=',1)
                ->select('name','slug')
                ->get();
                $cat_own = $category;
                $products = Product::where('category_id', $category->id)->where('status', 1)->orderBy('created_at', 'desc')->paginate(12);
                $catFaq = CatFaq::where('category_id', $category->id)->get();
                //dd($catFaq);
                return view('frontend.product.layoutcategory', compact('products', 'cat_own','cat_child','catFaq'));
            }else{
                return view('frontend.fourzeofour');
            }

        }
        /*SUB CATEGORY - TEMPLATE*/
        $subcategory = Subcategory::where('slug', $slug)->first();
        if ($subcategory) {
            if($subcategory->status == 1){
                $cat_parent = Category::where('id', $subcategory->category_id)->where('status','=',1)->first();
                $cat_own = $subcategory;
                $cat_child = Prosubcategory::where('subcategory_id', $subcategory->id)
                ->where('status','=',1)
                ->select('name','slug')
                ->get();
                $products = Product::where('category_id', $subcategory->category_id)->where('status', 1)->where('sub_category_id', $subcategory->id)->orderBy('created_at', 'desc')->paginate(12);
                $catFaq = CatFaq::where('subcategory_id', $subcategory->id)->get();
                // dd($subcategory );
                return view('frontend.product.layoutcategory', compact('products', 'cat_parent','cat_own','cat_child','catFaq'));
            }else{
                return view('frontend.fourzeofour');
            }            
        }
        /*PRO SUB CATEGORY - TEMPLATE*/
        $prosubcategory = Prosubcategory::where('slug', $slug)->first();
        if ($prosubcategory) {
            $cat_parent_1 = Category::where('id', $prosubcategory->category_id)->where('status','=',1)->first();
            $cat_parent = Subcategory::where('id', $prosubcategory->subcategory_id)->where('status','=',1)->first();
            $cat_own = $prosubcategory;
            $cat_child = Proprocategory::where('pro_sub_category_id', $prosubcategory->id)
                ->where('status','=',1)
                ->select('name','slug')
                ->get();
            $products = Product::where('category_id', $prosubcategory->category_id)
                ->where('status', 1)
                ->where('sub_category_id', $prosubcategory->subcategory_id)
                ->where('pro_sub_category_id', $prosubcategory->id)
                ->orderBy('created_at', 'desc')->paginate(12);
                // dd($products);
            $catFaq = CatFaq::where('prosubcategory_id', $prosubcategory->id)->get();
            return view('frontend.product.layoutcategory', compact('products','cat_parent_1','cat_parent','cat_own','cat_child','catFaq'));
        }
        /*PRO PRO (SUB) CATEGORY - TEMPLATE*/
        $proprocategory = Proprocategory::where('slug', $slug)->first();
        if ($proprocategory) {
            $cat_parent_2 = Category::where('id', $proprocategory->category_id)->where('status','=',1)->first();
            $cat_parent_1 = Subcategory::where('id', $proprocategory->subcategory_id)->where('status','=',1)->first();
            $cat_parent = Prosubcategory::where('id', $proprocategory->pro_sub_category_id)->where('status','=',1)->first();
            $cat_own = $proprocategory;
            $products = Product::where('category_id', $proprocategory->category_id)->where('status', 1)->where('sub_category_id', $proprocategory->subcategory_id)->where('pro_sub_category_id', $proprocategory->pro_sub_category_id)->where('pro_pro_category_id', $proprocategory->id)->orderBy('created_at', 'desc')->paginate(12);
            $catFaq = CatFaq::where('proprocategory_id', $proprocategory->id)->get();
            return view('frontend.product.layoutcategory', compact('products','cat_parent_2','cat_parent_1','cat_parent','cat_own','catFaq'));
        }
        $product = Product::where('slug', $slug)->first();
        if ($product) {
            if($product->status == 1){
                                
                $ProductBrands = ProductBrand::where('product_id', $product->id)->get();
                $relatedProducts = Product::where('category_id', $product->category_id)
                    ->orwhere('sub_category_id', $product->sub_category_id)
                    ->orwhere('pro_sub_category_id', $product->pro_sub_category_id)
                    ->where('products.status', 1)
                    ->inRandomOrder()
                    ->take(8)
                    ->get();
                   
                $ProductTerms = ProductTerms::where('product_id', $product->id)->orderBy('id', 'desc')->get();
                $ProductHighlights = ProductHighlight::where('product_id', $product->id)->get();
                $ProductFaq = ProductFaq::where('product_id', $product->id)->get();
                $FreeItemForClients = FreeItemForClient::where('product_id', $product->id)->get();
                
                $siteSetting = SiteSetting::where('status', 1)->first();
                $backgrounds = Background::first();
                
                $productImages = ProductImage::where([
                    ['product_id', '=', $product->id]
                ])->get();

                return view('frontend.product.layoutproduct', compact(
                    'backgrounds', 'product', 'ProductBrands', 'productImages',
                    'relatedProducts','siteSetting',
                    'ProductHighlights', 'ProductTerms', 'FreeItemForClients','ProductFaq'
                ));
            }else{
                $user = Auth::user();
                if(!empty($user)){
                    $ProductBrands = ProductBrand::where('product_id', $product->id)->get();
                    $relatedProducts = Product::where('category_id', $product->category_id)
                        ->orwhere('sub_category_id', $product->sub_category_id)
                        ->orwhere('pro_sub_category_id', $product->pro_sub_category_id)
                        ->where('products.status', 1)
                        ->inRandomOrder()
                        ->take(8)
                        ->get();
                    
                    $ProductTerms = ProductTerms::where('product_id', $product->id)->orderBy('id', 'desc')->get();
                    $ProductHighlights = ProductHighlight::where('product_id', $product->id)->get();
                    $ProductFaq = ProductFaq::where('product_id', $product->id)->get();
                    $FreeItemForClients = FreeItemForClient::where('product_id', $product->id)->get();
                    
                    $siteSetting = SiteSetting::where('status', 1)->first();
                    $backgrounds = Background::first();
                    
                    $productImages = ProductImage::where([
                        ['product_id', '=', $product->id]
                    ])->get();

                    return view('frontend.product.layoutproduct', compact(
                        'backgrounds', 'product', 'ProductBrands', 'productImages',
                        'relatedProducts','siteSetting',
                        'ProductHighlights', 'ProductTerms', 'FreeItemForClients','ProductFaq'
                    ));
                }
                return view('frontend.fourzeofour');
            }
        }

        $post = Post::where('slug', $slug)->first();
        if ($post) {
            if($post->status == 1){
                $post_tags = Post_tag::where('post_id', $post->id)->get();
                return view('frontend.post.post_details', compact('post', 'post_tags'));
            }else{
                return view('frontend.fourzeofour');
            }            
        }


        // $page = Page::where('slug', $slug)->first();
        // if ($page) {
        //     if($page->status == 1){
        //         return view('frontend.page.page_details', compact('page'));
        //     }else{
        //         return view('frontend.fourzeofour');
        //     }
            
        // }

        $banner = Banner::where('slug', $slug)->first();
        if ($banner) {
            return view('frontend.page.banner_details', compact('banner'));
        }

        $brand = Brand::where('slug', $slug)->where('status', 1)->first();
        if ($brand) {
            // dd($slug);
            // $ProductBrands = ProductBrand::where('brand_id', $brand->id)->get();
            /*->select(['products.id','name','subtitle','slug','buying_price','discount_price','regular_price',
            'special_price','offer_price','price_highlight','call_for_price','product_image_small',
            'image','image_des','image_alt','status','sku','discount'])    */
            $ProductBrands = ProductBrand::where('brand_id', $brand->id)
            ->join('products', function ($join) {
                $join->on('products.id', '=', 'product_brands.product_id')
                    ->where('products.status', '=', 1);
                
                })
            ->orderBy('product_id', 'desc')
            ->paginate(12);
            return view('frontend.product.brandProducts', compact('ProductBrands', 'brand'));
        }

        $shop_type = ShopType::where('slug', $slug)->first();
        if ($shop_type) {
            $products = Product::join('product_shops', 'products.id', '=', 'product_shops.product_id')->where('product_shops.shop_type_id', $shop_type->id)->where('products.status', 1)->orderBy('products.id', 'desc')->simplepaginate(12);

            return view('frontend.product.workstationProduct', compact('products', 'shop_type'));
        }

        // $b_t_b_shop = $slug;
        // if ($b_t_b_shop) {
        //     $products = Product::where('shop', $b_t_b_shop)->where('status', 1)->simplepaginate(12);
        //     return view('frontend.product.b2bProducts', compact('products'));
        // }


        // $r_shop = $slug;
        // if ($r_shop) {
        //     $products = Product::where('shop', $r_shop)->where('status', 1)->simplepaginate(12);
        //     return view('frontend.product.retailProducts', compact('products'));
        // }


        return view('frontend.fourzeofour');
    }



    public function subCategoryProducts($catSlug, $slug)
    {
        $category = Category::where('slug', $catSlug)->first();
        $subcategory = Subcategory::where('slug', $slug)->first();
        
        $products = [];
        if($category && $subcategory){
            $products = Product::where('category_id', $category->id)->where('status', 1)->where('sub_category_id', $subcategory->id)->simplepaginate(12);
                return view('frontend.product.subCategoryProducts', compact('products', 'category', 'subcategory'));
        }else{
            return view('frontend.fourzeofour');
        }



    }
    public function proSubCategoryProducts($catSlug, $subSlug, $slug)
    {
        $category = Category::where('slug', $catSlug)->first();
        $subcategory = Subcategory::where('slug', $subSlug)->first();
        $prosubcategory = Prosubcategory::where('slug', $slug)->first();
        $products = Product::where('category_id', $category->id)->where('sub_category_id', $subcategory->id)->where('pro_sub_category_id', $prosubcategory->id)->where('status', 1)->simplepaginate(12);
        return view('frontend.product.proSubCategoryProducts', compact('products', 'category', 'subcategory', 'prosubcategory'));
    }
    public function proProcategoryProducts($catSlug, $subSlug, $proSubSlug, $slug)
    {
        $category = Category::where('slug', $catSlug)->first();
        $subcategory = Subcategory::where('slug', $subSlug)->first();
        $prosubcategory = Prosubcategory::where('slug', $proSubSlug)->first();
        $proprocategory = Proprocategory::where('slug', $slug)->first();

        $products = Product::where('category_id', $category->id)->where('sub_category_id', $subcategory->id)->where('pro_sub_category_id', $prosubcategory->id)->where('pro_pro_category_id', $proprocategory->id)->where('status', 1)->simplepaginate(12);

        return view('frontend.product.proProcategoryProducts', compact('products', 'category', 'subcategory', 'prosubcategory', 'proprocategory'));
    }
    
    public function brands()
    {
        $brands = Brand::where('status', '=', 1)
            ->get();
        if($brands){
            return view('frontend.brands', compact('brands'));
        }else{
            return view('frontend.brands');
        }
    }
    
    public function allproduct()
    {
        dd(1);
    }
    public function filterCategoryProducts(Request $request, $id, $filterValue)
    {

        $filterValue = $filterValue;
        $category_id = $id;
        $category = Category::where('id', $category_id)->first();
        $products = array();
        if ($filterValue == "default") {
            $products = Product::where('category_id', $category_id)->where('status', 1)->get();
        } elseif ($filterValue == "a_z") {
            $products = Product::where('category_id', $category_id)->where('status', 1)->orderBy('id', 'asc')->get();
        } elseif ($filterValue == "z_a") {
            $products = Product::where('category_id', $category_id)->where('status', 1)->orderBy('id', 'desc')->get();
        } elseif ($filterValue == "lowestPrice") {
            $products = Product::where('category_id', $category_id)->where('status', 1)->orderBy('special_price', 'asc')->get();
        } elseif ($filterValue == "HighestPrice") {
            $products = Product::where('category_id', $category_id)->where('status', 1)->orderBy('special_price', 'desc')->get();
        } elseif ($filterValue == "BestSeller") {
            $products = Product::where('category_id', $category_id)->where('status', 1)->orderBy('total_sell', 'desc')->get();
        }
        return view('frontend.product.filterCategoryProducts', compact('products', 'filterValue', 'category'));
    }

    public function filterSubCategoryProducts($cat_id, $id, $filterValue)
    {

        $filterValue = $filterValue;
        $subcategory_id = $id;
        $category_id = $cat_id;
        $subcategory = Subcategory::where('id', $subcategory_id)->first();
        $products = array();
        if ($filterValue == "default") {
            $products = Product::where('category_id', $category_id)->where('status', 1)->where('sub_category_id', $subcategory_id)->get();
        } elseif ($filterValue == "a_z") {
            $products = Product::where('category_id', $category_id)->where('status', 1)->where('sub_category_id', $subcategory_id)->orderBy('id', 'asc')->get();
        } elseif ($filterValue == "z_a") {
            $products = Product::where('category_id', $category_id)->where('status', 1)->where('sub_category_id', $subcategory_id)->orderBy('id', 'desc')->get();
        } elseif ($filterValue == "lowestPrice") {
            $products = Product::where('category_id', $category_id)->where('status', 1)->where('sub_category_id', $subcategory_id)->orderBy('special_price', 'asc')->get();
        } elseif ($filterValue == "HighestPrice") {
            $products = Product::where('category_id', $category_id)->where('status', 1)->where('sub_category_id', $subcategory_id)->orderBy('special_price', 'desc')->get();
        } elseif ($filterValue == "BestSeller") {
            $products = Product::where('category_id', $category_id)->where('status', 1)->where('sub_category_id', $subcategory_id)->orderBy('total_sell', 'desc')->get();
        }
        return view('frontend.product.filterSubCategoryProducts', compact('products', 'filterValue', 'subcategory'));
    }

    public function filterprosubcategoryProducts($cat_id, $subcategory_id, $prosubcategory_id, $filterValue)
    {

        $category_id = $cat_id;
        $subcategory_id = $subcategory_id;
        $prosubcategory_id = $prosubcategory_id;
        $filterValue = $filterValue;
        $prosubcategory = Prosubcategory::where('id', $prosubcategory_id)->first();
        $products = array();
        if ($filterValue == "default") {
            $products = Product::where('category_id', $category_id)
                ->where('sub_category_id', $subcategory_id)->where('status', 1)
                ->where('pro_sub_category_id', $prosubcategory_id)
                ->get();
        } elseif ($filterValue == "a_z") {
            $products = Product::where('category_id', $category_id)
                ->where('sub_category_id', $subcategory_id)->where('status', 1)
                ->where('pro_sub_category_id', $prosubcategory_id)
                ->orderBy('id', 'asc')
                ->get();
        } elseif ($filterValue == "z_a") {
            $products = Product::where('category_id', $category_id)
                ->where('sub_category_id', $subcategory_id)->where('status', 1)
                ->where('pro_sub_category_id', $prosubcategory_id)
                ->orderBy('id', 'desc')
                ->get();
        } elseif ($filterValue == "lowestPrice") {
            $products = Product::where('category_id', $category_id)
                ->where('sub_category_id', $subcategory_id)->where('status', 1)
                ->where('pro_sub_category_id', $prosubcategory_id)
                ->orderBy('special_price', 'asc')
                ->get();
        } elseif ($filterValue == "HighestPrice") {
            $products = Product::where('category_id', $category_id)
                ->where('sub_category_id', $subcategory_id)->where('status', 1)
                ->where('pro_sub_category_id', $prosubcategory_id)
                ->orderBy('special_price', 'desc')
                ->get();
        } elseif ($filterValue == "BestSeller") {
            $products = Product::where('category_id', $category_id)
                ->where('sub_category_id', $subcategory_id)->where('status', 1)
                ->where('pro_sub_category_id', $prosubcategory_id)
                ->orderBy('total_sell', 'desc')
                ->get();
        }
        return view('frontend.product.filterprosubcategoryProducts', compact('products', 'filterValue', 'prosubcategory'));
    }
    public function brandProducts($slug)
    {
        $brand = Brand::where('slug', $slug)->first();
        $products = [];
        if($brand){
            $ProductBrands = ProductBrand::where('brand_id', $brand->id)
            ->select(['products.id','name','subtitle','slug','buying_price','discount_price','regular_price',
            'special_price','offer_price','price_highlight','call_for_price','product_image_small',
            'image','image_des','image_alt','status','sku','discount'])    
            ->join('products', function ($join) {
                $join->on('products.id', '=', 'product_brands.product_id')
                    ->where('products.status', '=', 1);
                
                })
            ->paginate(12);
            return view('frontend.product.brandProducts', compact('ProductBrands', 'brand'));
        }else{
            return view('frontend.fourzeofour');
        }
    }
    public function filterBrandProducts(Request $request, $id, $filterValue)
    {

        $filterValue = $filterValue;


        $brand_id = $id;
        $brand = Brand::where('id', $brand_id)->first();

        $products = array();
        if ($filterValue == "default") {
            $products = Product::join('product_brands', 'products.id', '=', 'product_brands.product_id')
                ->select('product_brands.*', 'products.*')
                ->where('products.status', 1)
                ->where('product_brands.brand_id', $brand_id)
                ->get();
        } elseif ($filterValue == "a_z") {
            $products = Product::join('product_brands', 'products.id', '=', 'product_brands.product_id')
                ->select('product_brands.*', 'products.*')
                ->where('products.status', 1)
                ->where('product_brands.brand_id', $brand_id)
                ->orderBy('products.id', 'asc')
                ->get();
        } elseif ($filterValue == "z_a") {
            $products = Product::join('product_brands', 'products.id', '=', 'product_brands.product_id')
                ->select('product_brands.*', 'products.*')
                ->where('products.status', 1)
                ->where('product_brands.brand_id', $brand_id)
                ->orderBy('products.id', 'desc')
                ->get();
        } elseif ($filterValue == "lowestPrice") {
            $products = Product::join('product_brands', 'products.id', '=', 'product_brands.product_id')
                ->select('product_brands.*', 'products.*')
                ->where('products.status', 1)
                ->where('product_brands.brand_id', $brand_id)
                ->orderBy('products.special_price', 'asc')
                ->get();
        } elseif ($filterValue == "HighestPrice") {
            $products = Product::join('product_brands', 'products.id', '=', 'product_brands.product_id')
                ->select('product_brands.*', 'products.*')
                ->where('products.status', 1)
                ->where('product_brands.brand_id', $brand_id)
                ->orderBy('products.special_price', 'desc')
                ->get();
        } elseif ($filterValue == "BestSeller") {
            $products = Product::join('product_brands', 'products.id', '=', 'product_brands.product_id')
                ->select('product_brands.*', 'products.*')
                ->where('products.status', 1)
                ->where('product_brands.brand_id', $brand_id)
                ->orderBy('products.total_sell', 'desc')
                ->get();
        }
        return view('frontend.product.filterBrandProducts', compact('products', 'filterValue', 'brand'));
    }
    public function workstationProduct($slug)
    {
        $shop_type = ShopType::where('slug', $slug)->first();


        $products = Product::join('product_shops', 'products.id', '=', 'product_shops.product_id')->where('product_shops.shop_type_id', $shop_type->id)->where('products.status', 1)->orderBy('products.id', 'desc')->simplepaginate(12);

        return view('frontend.product.workstationProduct', compact('products', 'shop_type'));
    }
    public function filterShoptypeProducts(Request $request, $id, $filterValue)
    {

        $filterValue = $filterValue;
        $shop_type_id = $id;
        $shop_type = ShopType::where('id', $shop_type_id)->first();
        $products = array();
        if ($filterValue == "default") {

            $products = Product::join('product_shops', 'products.id', '=', 'product_shops.product_id')->where('product_shops.shop_type_id', $shop_type_id)->where('products.status', 1)->get();
        } elseif ($filterValue == "a_z") {

            $products = Product::join('product_shops', 'products.id', '=', 'product_shops.product_id')->where('product_shops.shop_type_id', $shop_type_id)->where('products.status', 1)->orderBy('products.id', 'asc')->get();
        } elseif ($filterValue == "z_a") {

            $products = Product::join('product_shops', 'products.id', '=', 'product_shops.product_id')->where('product_shops.shop_type_id', $shop_type_id)->where('products.status', 1)->orderBy('products.id', 'desc')->get();
        } elseif ($filterValue == "lowestPrice") {

            $products = Product::join('product_shops', 'products.id', '=', 'product_shops.product_id')->where('product_shops.shop_type_id', $shop_type_id)->where('products.status', 1)->orderBy('products.special_price', 'asc')->get();
        } elseif ($filterValue == "HighestPrice") {

            $products = Product::join('product_shops', 'products.id', '=', 'product_shops.product_id')->where('product_shops.shop_type_id', $shop_type_id)->where('products.status', 1)->orderBy('products.special_price', 'desc')->get();
        } elseif ($filterValue == "BestSeller") {

            $products = Product::join('product_shops', 'products.id', '=', 'product_shops.product_id')->where('product_shops.shop_type_id', $shop_type_id)->where('products.status', 1)->orderBy('products.total_sell', 'desc')->get();
        }
        return view('frontend.product.filterShoptypeProducts', compact('products', 'filterValue', 'shop_type'));
    }

    public function productSearch(Request $request)
    {
        $search_name = $request->product_name;
        $search_slug = str::slug($search_name);
        //$products = Cache::get('search_title_'.$search_slug);
        if (empty($products)){
            if (empty($products)){
                $products = Product::whereRaw(
                    "MATCH(name) AGAINST(?)", 
                    array($search_name)
                    )->where('status', 1)->limit(40)->get();
                if(sizeof($products) == 0){
                    //dd('here',sizeof($products));
                    $products = Product::where('name', 'LIKE', '%' . $search_name . '%')
                    ->where('status', 1)
                    ->select(['products.id','name','subtitle','slug','buying_price','discount_price','regular_price',
                    'special_price','offer_price','price_highlight','call_for_price','product_image_small',
                    'image','image_des','image_alt','status','sku','discount'])
                    ->take(40)->get();
                }
                if(sizeof($products) == 0){
                    Cache::put('search_title_'.$search_slug, $products, $seconds = 10000000000);
                }
            }
        }
        return view('frontend.product.productSearch', compact('products'));
    }

    public function producSearchNor(Request $request)
    {
        if(!empty($request)){
            if(!empty($request->product_name)){
                $search_name = $request->product_name;
                $search_slug = str::slug($search_name);
                //$products = Cache::get('search_title_'.$search_slug);
                if (empty($products)){
                    $products = Product::whereRaw(
                        "MATCH(name) AGAINST(?)", 
                        array($search_name)
                        )->where('status', 1)->limit(40)->get();
                    if(sizeof($products) == 0){
                        //dd('here',sizeof($products));
                        $products = Product::where('name', 'LIKE', '%' . $search_name . '%')
                        ->where('status', 1)
                        ->select(['products.id','name','subtitle','slug','buying_price','discount_price','regular_price',
                        'special_price','offer_price','price_highlight','call_for_price','product_image_small',
                        'image','image_des','image_alt','status','sku','discount'])
                        ->take(40)->get();
                    }
                    if(sizeof($products) == 0){
                        Cache::put('search_title_'.$search_slug, $products, $seconds = 10000000000);
                    }
                }
            }
        }
        
        return view('frontend.product.producSearchNor', compact('products','search_name'));
    }

    public function filterPrice(Request $request)
    {
        $min = $request->min;
        $max = $request->max;
        $products = Product::whereBetween('special_price', [$min, $max])
            ->where('status', 1)
            ->simplepaginate(12);
        return view('frontend.product.filterPrice', compact('products'));
    }

    public function allProducts()
    {
        $products = Product::where('status', 1)->simplepaginate(12);
        return view('frontend.product.allProducts', compact('products'));
    }

    public function filterProducts($filterValue)
    {

        $filterValue = $filterValue;
        $products = array();
        if ($filterValue == "default") {
            $products = Product::where('status', 1)->get();
        } elseif ($filterValue == "a_z") {
            $products = Product::where('status', 1)->orderBy('id', 'asc')->get();
        } elseif ($filterValue == "z_a") {
            $products = Product::where('status', 1)->orderBy('id', 'desc')->get();
        } elseif ($filterValue == "lowestPrice") {
            $products = Product::where('status', 1)->orderBy('special_price', 'asc')->get();
        } elseif ($filterValue == "HighestPrice") {
            $products = Product::where('status', 1)->orderBy('special_price', 'desc')->get();
        } elseif ($filterValue == "BestSeller") {
            $products = Product::where('status', 1)->orderBy('total_sell', 'desc')->get();
        }
        return view('frontend.product.filterProducts', compact('products', 'filterValue'));
    }


    public function filterComponentProducts(Request $request, $id, $filterValue)
    {

        $filterValue = $filterValue;
        $component_id = $id;
        $component = Component::where('id', $component_id)->first();
        $products = array();
        if ($filterValue == "default") {
            $products = Product::where('component_id', $component_id)->where('status', 1)->get();
        } elseif ($filterValue == "a_z") {
            $products = Product::where('component_id', $component_id)->where('status', 1)->orderBy('id', 'asc')->get();
        } elseif ($filterValue == "z_a") {
            $products = Product::where('component_id', $component_id)->where('status', 1)->orderBy('id', 'desc')->get();
        } elseif ($filterValue == "lowestPrice") {
            $products = Product::where('component_id', $component_id)->where('status', 1)->orderBy('special_price', 'asc')->get();
        } elseif ($filterValue == "HighestPrice") {
            $products = Product::where('component_id', $component_id)->where('status', 1)->orderBy('special_price', 'desc')->get();
        } elseif ($filterValue == "BestSeller") {
            $products = Product::where('component_id', $component_id)->where('status', 1)->orderBy('total_sell', 'desc')->get();
        }
        return view('frontend.product.filterComponentProducts', compact('products', 'filterValue', 'component'));
    }
}
