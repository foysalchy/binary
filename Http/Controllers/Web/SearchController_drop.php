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
use App\Models\ProductSocial;
use App\Models\ProductTerms;
use App\Models\FreeItemForClient;

use Str;
use \Cache;

class SearchController extends Controller
{
    public function MakeUrl($slug, Request $request)
    {

        $SiteSetting = SiteSetting::where('status', 1)->first();


        /*CATEGORY - TEMPLATE*/
        $category = Category::where('slug', $slug)->first();
        if ($category) {

            if($category->status == 1){
                $cat_child = Subcategory::where('category_id', $category->id)
                ->where('status','=',1)
                ->select('name','slug')
                ->get();
                $cat_own = $category;
                /*Sorting request*/
                $pagination_count = 12;
                $sortRequest = $request->get('sort');
                $pageRequest = $request->get('page');

                if(empty($sortRequest) && empty($pageRequest)){
                    if(!empty($request->session()->get('category_sort_'.$category->id))){
                        $request->session()->forget('category_sort_'.$category->id);
                    }
                }
                
                if(!empty($sortRequest)){
                    $request->session()->put('category_sort_'.$category->id, $sortRequest);
                }else{
                    $sortRequest = $request->session()->get('category_sort_'.$category->id);
                }
                // dd($sortRequest);
                $order_field = 'id';
                $order_type = 'desc';

                if(!empty($sortRequest)){
                    switch($sortRequest){
                        case 'price_asc':
                            $order_field = 'special_price';
                            $order_type = 'asc';
                            break;
                        case 'price_desc':
                            $order_field = 'special_price';
                            $order_type = 'desc';
                            break;
                        default:
                            $order_field = 'id';
                            $order_type = 'desc';
                            break;
                    }
                }
                $products =  Product::where('category_id', $category->id)
                    ->where('status', 1)
                    ->orderBy('stock_status', 'asc')
                    ->orderBy($order_field, $order_type)
                    ->paginate($pagination_count);

                $catFaq = CatFaq::where('category_id', $category->id)->get();
                //dd($catFaq);

                $tableName = 'category_id';
                switch($SiteSetting->templete) {
                    case(1):

                         return view('frontend.product.layoutcategory', compact('products', 'cat_own','cat_child','catFaq','category','sortRequest','tableName'));

                        break;
         
                    case(2):

                          return view('templete_two.product.layoutcategory', compact('products', 'cat_own','cat_child','catFaq','sortRequest','tableName'));
                     
                        break;
         
                    default:
                        $msg = 'Something went wrong.';
                }

               
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

                /*Sorting request*/
                $pagination_count = 12;
                $sortRequest = $request->get('sort');
                $pageRequest = $request->get('page');

                if(empty($sortRequest) && empty($pageRequest)){
                    if(!empty($request->session()->get('category2_sort_'.$subcategory->id))){
                        $request->session()->forget('category2_sort_'.$subcategory->id);
                    }
                }
                
                if(!empty($sortRequest)){
                    $request->session()->put('category2_sort_'.$subcategory->id, $sortRequest);
                }else{
                    $sortRequest = $request->session()->get('category2_sort_'.$subcategory->id);
                }
                // dd($sortRequest);
                $order_field = 'id';
                $order_type = 'desc';

                if(!empty($sortRequest)){
                    switch($sortRequest){
                        case 'price_asc':
                            $order_field = 'special_price';
                            $order_type = 'asc';
                            break;
                        case 'price_desc':
                            $order_field = 'special_price';
                            $order_type = 'desc';
                            break;
                        default:
                            $order_field = 'id';
                            $order_type = 'desc';
                            break;
                    }
                }
                $products =  Product::where('sub_category_id', $subcategory->id)
                    ->where('status', 1)
                    ->orderBy('stock_status', 'asc')
                    ->orderBy($order_field, $order_type)
                    ->paginate($pagination_count);

                $catFaq = CatFaq::where('subcategory_id', $subcategory->id)->get();
                // dd($subcategory );

                $tableName = 'sub_category_id';

                switch($SiteSetting->templete) {
                    case(1):

                        return view('frontend.product.layoutcategory', compact('products', 'cat_parent','cat_own','cat_child','catFaq','sortRequest','tableName'));
                         

                        break;
         
                    case(2):

                        return view('templete_two.product.layoutcategory', compact('products', 'cat_parent','cat_own','cat_child','catFaq','sortRequest','tableName'));

                     
                        break;
         
                    default:
                        $msg = 'Something went wrong.';
                }


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
                
                /*Sorting request*/
                $pagination_count = 12;
                $sortRequest = $request->get('sort');
                $pageRequest = $request->get('page');

                if(empty($sortRequest) && empty($pageRequest)){
                    if(!empty($request->session()->get('category3_sort_'.$prosubcategory->id))){
                        $request->session()->forget('category3_sort_'.$prosubcategory->id);
                    }
                }
                
                if(!empty($sortRequest)){
                    $request->session()->put('category3_sort_'.$prosubcategory->id, $sortRequest);
                }else{
                    $sortRequest = $request->session()->get('category3_sort_'.$prosubcategory->id);
                }
                // dd($sortRequest);
                $order_field = 'id';
                $order_type = 'desc';

                if(!empty($sortRequest)){
                    switch($sortRequest){
                        case 'price_asc':
                            $order_field = 'special_price';
                            $order_type = 'asc';
                            break;
                        case 'price_desc':
                            $order_field = 'special_price';
                            $order_type = 'desc';
                            break;
                        default:
                            $order_field = 'id';
                            $order_type = 'desc';
                            break;
                    }
                }
                $products =  Product::where('pro_sub_category_id', $prosubcategory->id)
                    ->where('status', 1)
                    ->orderBy('stock_status', 'asc')
                    ->orderBy($order_field, $order_type)
                    ->paginate($pagination_count);

           
            $catFaq = CatFaq::where('prosubcategory_id', $prosubcategory->id)->get();
            $tableName = 'pro_sub_category_id';
            switch($SiteSetting->templete) {
                case(1):

                    return view('frontend.product.layoutcategory', compact('products','cat_parent_1','cat_parent','cat_own','cat_child','catFaq','sortRequest','tableName'));
                     

                    break;
     
                case(2):

                    return view('templete_two.product.layoutcategory', compact('products','cat_parent_1','cat_parent','cat_own','cat_child','catFaq','sortRequest','tableName'));

                 
                    break;
     
                default:
                    $msg = 'Something went wrong.';
            }


            



            
        }
        /*PRO PRO (SUB) CATEGORY - TEMPLATE*/
        $proprocategory = Proprocategory::where('slug', $slug)->first();
        if ($proprocategory) {
            $cat_parent_2 = Category::where('id', $proprocategory->category_id)->where('status','=',1)->first();
            $cat_parent_1 = Subcategory::where('id', $proprocategory->subcategory_id)->where('status','=',1)->first();
            $cat_parent = Prosubcategory::where('id', $proprocategory->pro_sub_category_id)->where('status','=',1)->first();
            $cat_own = $proprocategory;
            /*Sorting request*/
            $pagination_count = 12;
            $sortRequest = $request->get('sort');
            $pageRequest = $request->get('page');

            if(empty($sortRequest) && empty($pageRequest)){
                if(!empty($request->session()->get('category4_sort_'.$proprocategory->id))){
                    $request->session()->forget('category4_sort_'.$proprocategory->id);
                }
            }
            
            if(!empty($sortRequest)){
                $request->session()->put('category4_sort_'.$proprocategory->id, $sortRequest);
            }else{
                $sortRequest = $request->session()->get('category4_sort_'.$proprocategory->id);
            }
            // dd($sortRequest);
            $order_field = 'id';
            $order_type = 'desc';

            if(!empty($sortRequest)){
                switch($sortRequest){
                    case 'price_asc':
                        $order_field = 'special_price';
                        $order_type = 'asc';
                        break;
                    case 'price_desc':
                        $order_field = 'special_price';
                        $order_type = 'desc';
                        break;
                    default:
                        $order_field = 'id';
                        $order_type = 'desc';
                        break;
                }
            }
            $products =  Product::where('pro_pro_category_id', $proprocategory->id)
                ->where('status', 1)
                ->orderBy('stock_status', 'asc')
                ->orderBy($order_field, $order_type)
                ->paginate($pagination_count);
            
            $catFaq = CatFaq::where('proprocategory_id', $proprocategory->id)->get();
            
            $tableName = 'pro_pro_category_id';
            
            switch($SiteSetting->templete) {
                case(1):

                    return view('frontend.product.layoutcategory', compact('products','cat_parent_2','cat_parent_1','cat_parent','cat_own','catFaq','sortRequest','tableName'));
                     

                    break;
     
                case(2):

                    return view('templete_two.product.layoutcategory', compact('products','cat_parent_2','cat_parent_1','cat_parent','cat_own','catFaq','sortRequest','tableName'));

                 
                    break;
     
                default:
                    $msg = 'Something went wrong.';
            }



            
        }
        $product = Product::where('slug', $slug)->first();
        if ($product) {
            if($product->status == 1){
                 if(empty($product->pro_sub_category_id) ){
                    $ProductBrands = ProductBrand::where('product_id', $product->id)->get();
                     $relatedProducts = Product::where('sub_category_id', $product->sub_category_id)
                    ->where('products.status', 1)
                    ->where('stock_status', 'in_stock')
                    ->take(4)
                    ->get();
                }
                 else{
                    $ProductBrands = ProductBrand::where('product_id', $product->id)->get();
                    $relatedProducts = Product::where('pro_sub_category_id', $product->pro_sub_category_id)
                        ->orwhere('sub_category_id', $product->sub_category_id)
                        ->where('products.status', 1)
                        ->where('stock_status', 'in_stock')
                        ->take(4)
                        ->get();
                }
                                
                   
                $ProductTerms = ProductTerms::where('product_id', $product->id)->orderBy('id', 'desc')->get();
                $ProductHighlights = ProductHighlight::where('product_id', $product->id)->get();
                $ProductFaq = ProductFaq::where('product_id', $product->id)->get();
                $ProductSocial = ProductSocial::where('product_id', $product->id)->first();
                $FreeItemForClients = FreeItemForClient::where('product_id', $product->id)->get();
                
                $siteSetting = SiteSetting::where('status', 1)->first();
                $backgrounds = Background::first();
                
                $productImages = ProductImage::where([
                    ['product_id', '=', $product->id]
                ])->get();


                switch($SiteSetting->templete) {
                    case(1):
         
                        return view('frontend.product.layoutproduct', compact(
                            'backgrounds', 'product', 'ProductBrands', 'productImages',
                            'relatedProducts','siteSetting',
                            'ProductHighlights', 'ProductTerms', 'FreeItemForClients','ProductFaq','ProductSocial'
                        ));

                        break;
         
                    case(2):
                         return view('templete_two.product.layoutproduct', compact(
                            'backgrounds', 'product', 'ProductBrands', 'productImages',
                            'relatedProducts','siteSetting',
                            'ProductHighlights', 'ProductTerms', 'FreeItemForClients','ProductFaq','ProductSocial'
                        ));
                     
                        break;
         
                    default:
                        $msg = 'Something went wrong.';
                }

                
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

                    return view('templete_two.product.layoutproduct', compact(
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

                switch($SiteSetting->templete) {
                    case(1):

                         return view('frontend.product.brandProducts', compact('ProductBrands', 'brand'));

                        break;
         
                    case(2):
                            return view('templete_two.product.brandProducts', compact('ProductBrands', 'brand'));

                        break;
         
                    default:
                        $msg = 'Something went wrong.';
                }


            
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


        // return view('frontend.fourzeofour');
        return response()->view('frontend.fourzeofour')->setStatusCode(404); 

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


        $SiteSetting = SiteSetting::first();
        
        switch($SiteSetting->templete) {
            case(1):

                return view('frontend.product.layoutcategory', compact('products', 'cat_parent','cat_own','cat_child','catFaq'));

                 

                break;
 
            case(2):


                return view('templete_two.product.filterBrandProducts', compact('products', 'filterValue', 'brand'));

             
                break;
 
            default:
                $msg = 'Something went wrong.';
        }


        
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
        $SiteSetting = SiteSetting::where('status', 1)->first();
        
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
        


        switch($SiteSetting->templete) {
            case(1):
 
                return view('frontend.product.producSearchNor', compact('products','search_name'));
 
                break;
 
            case(2):
                 
                return view('templete_two.product.producSearchNor', compact('products','search_name'));
 
                break;
 
            default:
                $msg = 'Something went wrong.';
        }



        
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
