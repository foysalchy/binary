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
     function addProductToRecentlyViewed($productId, $limit = 5) {
        // Initialize the session array if not already set
        $recentlyViewed = session('recently_viewed', []);
    
        // Remove the product ID if it already exists in the array
        $recentlyViewed = array_diff($recentlyViewed, [$productId]);
    
        // Add the new product ID to the beginning of the array
        array_unshift($recentlyViewed, $productId);
    
        // Trim the array to the specified limit
        $recentlyViewed = array_slice($recentlyViewed, 0, $limit);
    
        // Update the session data
        session(['recently_viewed' => $recentlyViewed]);
    }
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
                
                // $pagination_count = 12;
                // limit
                $limitRequest = $request->get('limit');
                if(empty($limitRequest)){$pagination_count = 12;}else{$pagination_count = $request->get('limit');}
                
                // sort price
                $order_field = 'stock_status';$order_type = 'ASC';
                $sortRequest = $request->get('sort');
                if(!empty($request->get('sort'))){
                    switch($sortRequest){
                        case 'asc': $order_field = 'special_price';$order_type = 'asc';break;
                        case 'desc': $order_field = 'special_price';$order_type = 'desc';break;
                        default: $order_field = 'stock_status';$order_type = 'ASC';break;
                    }
                }

                // table properties
                $tableName = 'category_id';
                $tableValue = $category->id;

                // stock status
                $arr_stock = array(1=>'in_stock',2=>'coming',3=>'new_arrived',4=>'preorder');
                $stockRequest = $request->get('stock');
                if(!empty($stockRequest)){$product_stock = explode(',',$stockRequest);}else{$product_stock = "";}
                
                // price range
                $priceRequest = $request->get('price');
                if(!empty($priceRequest)){$product_price = explode(',',$priceRequest);}else{$product_price = "";}
                
                
                if(!empty($request->get('sort'))){
                // get product list
                $products = Product::where($tableName,$tableValue)
                    ->where('status',1)
                    ->where(function ($query) use ($product_stock,$arr_stock,$product_price) {
                        //price exists
                        if(!empty($product_price)){$query->whereBetween('special_price', [$product_price[0], $product_price[1]]);}
                        //stock exists
                        if(!empty($product_stock)){$query->where('stock_status', "=", $arr_stock[$product_stock[0]]);if(sizeof($product_stock)>1){for($i=1;$i<sizeof($product_stock);$i++){$query->orWhere('stock_status', "=", $arr_stock[$product_stock[$i]]);}}}
                    })
                    ->select('products.id','name','slug','regular_price','special_price','offer_price','call_for_price','stock_status','image','product_image_small','status','products.created_at')
                    ->orderBy($order_field,$order_type)
                    ->paginate($pagination_count); 
                }
                else{
                // get product list
                $products = Product::where($tableName,$tableValue)
                ->where('status',1)
                ->where(function ($query) use ($product_stock,$arr_stock,$product_price) {
                    //price exists
                    if(!empty($product_price)){$query->whereBetween('special_price', [$product_price[0], $product_price[1]]);}
                    //stock exists
                    if(!empty($product_stock)){$query->where('stock_status', "=", $arr_stock[$product_stock[0]]);if(sizeof($product_stock)>1){for($i=1;$i<sizeof($product_stock);$i++){$query->orWhere('stock_status', "=", $arr_stock[$product_stock[$i]]);}}}
                })
                ->select('products.id','name','slug','regular_price','special_price','offer_price','call_for_price','stock_status','image','product_image_small','status','products.created_at')
                ->orderBy($order_field,$order_type)
                ->orderBy('created_at', 'desc')
                ->paginate($pagination_count); 

                }
                    
                $product_lowest = Product::where($tableName, $tableValue)->where('status',1)->select('special_price')->orderBy('special_price','asc')->first();
                $product_highest = Product::where($tableName, $tableValue)->where('status',1)->select('special_price')->orderBy('special_price','desc')->first();

                $product_brands = Product::where($tableName, $tableValue)->where('status',1)->select('special_price')->orderBy('special_price','asc')->first();
                
                $catFaq = CatFaq::where($tableName, $tableValue)->get();

                switch($SiteSetting->templete) {
                    case(1):

                         return view('frontend.product.layoutcategory', compact(
                                'products', 'cat_own','cat_child','catFaq','category','sortRequest','tableName','product_lowest','product_highest'
                         ));

                        break;
         
                    case(2):

                          return view('templete_two.product.layoutcategory', compact(
                                'products', 'cat_own','cat_child','category','catFaq','sortRequest','tableName','product_lowest','product_highest'
                          ));
                     
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

                // $pagination_count = 12;
                // limit
                $limitRequest = $request->get('limit');
                if(empty($limitRequest)){$pagination_count = 12;}else{$pagination_count = $request->get('limit');}
                
                // sort price
                $order_field = 'stock_status';$order_type = 'ASC';
                $sortRequest = $request->get('sort');
                if(!empty($request->get('sort'))){
                    switch($sortRequest){
                        case 'asc': $order_field = 'special_price';$order_type = 'asc';break;
                        case 'desc': $order_field = 'special_price';$order_type = 'desc';break;
                        default: $order_field = 'stock_status';$order_type = 'ASC';break;
                    }
                }

                // table properties
                $tableName = 'sub_category_id';
                $tableValue = $subcategory->id;

                // stock status
                $arr_stock = array(1=>'in_stock',2=>'coming',3=>'new_arrived',4=>'preorder');
                $stockRequest = $request->get('stock');
                if(!empty($stockRequest)){$product_stock = explode(',',$stockRequest);}else{$product_stock = "";}
                
                // price range
                $priceRequest = $request->get('price');
                if(!empty($priceRequest)){$product_price = explode(',',$priceRequest);}else{$product_price = "";}
              
                if(!empty($request->get('sort'))){
                // get product list
                $products = Product::where($tableName,$tableValue)
                    ->where('status',1)
                    ->where(function ($query) use ($product_stock,$arr_stock,$product_price) {
                        //price exists
                        if(!empty($product_price)){$query->whereBetween('special_price', [$product_price[0], $product_price[1]]);}
                        //stock exists
                        if(!empty($product_stock)){$query->where('stock_status', "=", $arr_stock[$product_stock[0]]);if(sizeof($product_stock)>1){for($i=1;$i<sizeof($product_stock);$i++){$query->orWhere('stock_status', "=", $arr_stock[$product_stock[$i]]);}}}
                    })
                    ->select('products.id','name','slug','regular_price','special_price','offer_price','call_for_price','stock_status','image','product_image_small','status','products.created_at')
                    ->orderBy($order_field,$order_type)
                    ->paginate($pagination_count); 
                }
                else{
                // get product list
                $products = Product::where($tableName,$tableValue)
                ->where('status',1)
                ->where(function ($query) use ($product_stock,$arr_stock,$product_price) {
                    //price exists
                    if(!empty($product_price)){$query->whereBetween('special_price', [$product_price[0], $product_price[1]]);}
                    //stock exists
                    if(!empty($product_stock)){$query->where('stock_status', "=", $arr_stock[$product_stock[0]]);if(sizeof($product_stock)>1){for($i=1;$i<sizeof($product_stock);$i++){$query->orWhere('stock_status', "=", $arr_stock[$product_stock[$i]]);}}}
                })
                ->select('products.id','name','slug','regular_price','special_price','offer_price','call_for_price','stock_status','image','product_image_small','status','products.created_at')
                ->orderBy($order_field,$order_type)
                ->orderBy('created_at', 'desc')
                ->paginate($pagination_count); 

                }
                
                $product_lowest = Product::where($tableName, $tableValue)->where('status',1)->select('special_price')->orderBy('special_price','asc')->first();
                $product_highest = Product::where($tableName, $tableValue)->where('status',1)->select('special_price')->orderBy('special_price','desc')->first();
                
                $catFaq = CatFaq::where('subcategory_id', $subcategory->id)->get();

                switch($SiteSetting->templete) {
                    case(1):

                        return view('frontend.product.layoutcategory', compact(
                            'products', 'cat_parent','cat_own','cat_child','catFaq','sortRequest','tableName','product_lowest','product_highest'));
                         

                        break;
         
                    case(2):

                        return view('templete_two.product.layoutcategory', compact(
                            'products', 'cat_parent','cat_own','cat_child','catFaq','sortRequest','tableName','product_lowest','product_highest'));

                     
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
                // $pagination_count = 12;
                // limit
                $limitRequest = $request->get('limit');
                if(empty($limitRequest)){$pagination_count = 12;}else{$pagination_count = $request->get('limit');}
                
                // sort price
                $order_field = 'stock_status';$order_type = 'ASC';
                $sortRequest = $request->get('sort');
                if(!empty($request->get('sort'))){
                    switch($sortRequest){
                        case 'asc': $order_field = 'special_price';$order_type = 'asc';break;
                        case 'desc': $order_field = 'special_price';$order_type = 'desc';break;
                        default: $order_field = 'stock_status';$order_type = 'ASC';break;
                    }
                }

                // table properties
                $tableName = 'pro_sub_category_id';
                $tableValue = $prosubcategory->id;

                // stock status
                $arr_stock = array(1=>'in_stock',2=>'coming',3=>'new_arrived',4=>'preorder');
                $stockRequest = $request->get('stock');
                if(!empty($stockRequest)){$product_stock = explode(',',$stockRequest);}else{$product_stock = "";}
                
                // price range
                $priceRequest = $request->get('price');
                if(!empty($priceRequest)){$product_price = explode(',',$priceRequest);}else{$product_price = "";}
                
                if(!empty($request->get('sort'))){
                // get product list
                $products = Product::where($tableName,$tableValue)
                    ->where('status',1)
                    ->where(function ($query) use ($product_stock,$arr_stock,$product_price) {
                        //price exists
                        if(!empty($product_price)){$query->whereBetween('special_price', [$product_price[0], $product_price[1]]);}
                        //stock exists
                        if(!empty($product_stock)){$query->where('stock_status', "=", $arr_stock[$product_stock[0]]);if(sizeof($product_stock)>1){for($i=1;$i<sizeof($product_stock);$i++){$query->orWhere('stock_status', "=", $arr_stock[$product_stock[$i]]);}}}
                    })
                    ->select('products.id','name','slug','regular_price','special_price','offer_price','call_for_price','stock_status','image','product_image_small','status','products.created_at')
                    ->orderBy($order_field,$order_type)
                    ->paginate($pagination_count); 
                }
                else{
                // get product list
                $products = Product::where($tableName,$tableValue)
                ->where('status',1)
                ->where(function ($query) use ($product_stock,$arr_stock,$product_price) {
                    //price exists
                    if(!empty($product_price)){$query->whereBetween('special_price', [$product_price[0], $product_price[1]]);}
                    //stock exists
                    if(!empty($product_stock)){$query->where('stock_status', "=", $arr_stock[$product_stock[0]]);if(sizeof($product_stock)>1){for($i=1;$i<sizeof($product_stock);$i++){$query->orWhere('stock_status', "=", $arr_stock[$product_stock[$i]]);}}}
                })
                ->select('products.id','name','slug','regular_price','special_price','offer_price','call_for_price','stock_status','image','product_image_small','status','products.created_at')
                ->orderBy($order_field,$order_type)
                ->orderBy('created_at', 'desc')
                ->paginate($pagination_count); 

                }
                    
                
                $product_lowest = Product::where($tableName, $tableValue)->where('status',1)->select('special_price')->orderBy('special_price','asc')->first();
                $product_highest = Product::where($tableName, $tableValue)->where('status',1)->select('special_price')->orderBy('special_price','desc')->first();
                
           
            $catFaq = CatFaq::where('prosubcategory_id', $prosubcategory->id)->get();

            switch($SiteSetting->templete) {
                case(1):
                    return view('frontend.product.layoutcategory', compact('products','cat_parent_1','cat_parent','cat_own','cat_child','catFaq','sortRequest','tableName','product_lowest','product_highest'));
                    break;
                case(2):
                    return view('templete_two.product.layoutcategory', compact('products','cat_parent_1','cat_parent','cat_own','cat_child','catFaq','sortRequest','tableName','product_lowest','product_highest'));
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
                // $pagination_count = 12;
                // limit
                $limitRequest = $request->get('limit');
                if(empty($limitRequest)){$pagination_count = 12;}else{$pagination_count = $request->get('limit');}
                
                // sort price
                $order_field = 'stock_status';$order_type = 'ASC';
                $sortRequest = $request->get('sort');
                if(!empty($request->get('sort'))){
                    switch($sortRequest){
                        case 'asc': $order_field = 'special_price';$order_type = 'asc';break;
                        case 'desc': $order_field = 'special_price';$order_type = 'desc';break;
                        default: $order_field = 'stock_status';$order_type = 'ASC';break;
                    }
                }

                // table properties
                $tableName = 'pro_pro_category_id';
                $tableValue = $proprocategory->id;

                // stock status
                $arr_stock = array(1=>'in_stock',2=>'coming',3=>'new_arrived',4=>'preorder');
                $stockRequest = $request->get('stock');
                if(!empty($stockRequest)){$product_stock = explode(',',$stockRequest);}else{$product_stock = "";}
                
                // price range
                $priceRequest = $request->get('price');
                if(!empty($priceRequest)){$product_price = explode(',',$priceRequest);}else{$product_price = "";}
                
                if(!empty($request->get('sort'))){
                // get product list
                $products = Product::where($tableName,$tableValue)
                    ->where('status',1)
                    ->where(function ($query) use ($product_stock,$arr_stock,$product_price) {
                        //price exists
                        if(!empty($product_price)){$query->whereBetween('special_price', [$product_price[0], $product_price[1]]);}
                        //stock exists
                        if(!empty($product_stock)){$query->where('stock_status', "=", $arr_stock[$product_stock[0]]);if(sizeof($product_stock)>1){for($i=1;$i<sizeof($product_stock);$i++){$query->orWhere('stock_status', "=", $arr_stock[$product_stock[$i]]);}}}
                    })
                    ->select('products.id','name','slug','regular_price','special_price','offer_price','call_for_price','stock_status','image','product_image_small','status','products.created_at')
                    ->orderBy($order_field,$order_type)
                    ->paginate($pagination_count); 
                }
                else{
                // get product list
                $products = Product::where($tableName,$tableValue)
                ->where('status',1)
                ->where(function ($query) use ($product_stock,$arr_stock,$product_price) {
                    //price exists
                    if(!empty($product_price)){$query->whereBetween('special_price', [$product_price[0], $product_price[1]]);}
                    //stock exists
                    if(!empty($product_stock)){$query->where('stock_status', "=", $arr_stock[$product_stock[0]]);if(sizeof($product_stock)>1){for($i=1;$i<sizeof($product_stock);$i++){$query->orWhere('stock_status', "=", $arr_stock[$product_stock[$i]]);}}}
                })
                ->select('products.id','name','slug','regular_price','special_price','offer_price','call_for_price','stock_status','image','product_image_small','status','products.created_at')
                ->orderBy($order_field,$order_type)
                ->orderBy('created_at', 'desc')
                ->paginate($pagination_count); 

                }
                
                $product_lowest = Product::where($tableName, $tableValue)->where('status',1)->select('special_price')->orderBy('special_price','asc')->first();
                $product_highest = Product::where($tableName, $tableValue)->where('status',1)->select('special_price')->orderBy('special_price','desc')->first();
                
            $catFaq = CatFaq::where('proprocategory_id', $proprocategory->id)->get();

            // $tableName = 'pro_pro_category_id';

            switch($SiteSetting->templete) {
                case(1):
                    return view('frontend.product.layoutcategory', compact('products','cat_parent_2','cat_parent_1','cat_parent','cat_own','catFaq','sortRequest','tableName','product_lowest','product_highest'));
                    break;
                case(2):
                    return view('templete_two.product.layoutcategory', compact('products','cat_parent_2','cat_parent_1','cat_parent','cat_own','catFaq','sortRequest','tableName','product_lowest','product_highest'));
                    break;
                default:
                    $msg = 'Something went wrong.';
            }
        }
        
        $product = Product::where('slug', $slug)->first();
        if ($product) {
             $recentView =  $this->addProductToRecentlyViewed($product->id);
            if($product->status == 1){
                 if(!empty($product->pro_sub_category_id) ){
                    $relatedProducts = Product::select(['id','name','slug','product_image_thumb','image','estimated_price','offer_price','regular_price','special_price'])
                   ->where('pro_sub_category_id', $product->pro_sub_category_id)
                   ->orWhere('sub_category_id', $product->sub_category_id)
                   ->where('products.status', 1)
                   ->where('stock_status', 'in_stock')
                   ->orderBy('id', 'desc')
                   ->take(4)
                   ->get();
                }
                 else{
                     $relatedProducts = Product::select(['id','name','slug','product_image_thumb','image','estimated_price','offer_price','regular_price','special_price'])
                        ->where('sub_category_id', $product->sub_category_id)
                        ->where('products.status', 1)
                        ->where('stock_status', 'in_stock')
                        ->orderBy('id', 'desc')
                        ->take(4)
                        ->get();
                }
                                
                $ProductBrands = ProductBrand::where('product_id', $product->id)->get();
                $ProductTerms = ProductTerms::where('product_id', $product->id)->orderBy('id', 'desc')->get();
                $ProductHighlights = ProductHighlight::where('product_id', $product->id)->get();
                $ProductFaq = ProductFaq::where('product_id', $product->id)->get();
                $ProductSocial = ProductSocial::where('product_id', $product->id)->first();
                $siteSetting = SiteSetting::where('status', 1)->first();
                
                
                $productImages = ProductImage::where([
                    ['product_id', '=', $product->id]
                ])->get();


                switch($SiteSetting->templete) {
                    case(1):
         
                        return view('frontend.product.layoutproduct', compact(
                            'product', 'ProductBrands', 'productImages',
                            'relatedProducts','siteSetting',
                            'ProductHighlights', 'ProductTerms','ProductFaq','ProductSocial'
                        ));

                        break;
         
                    case(2):
                         return view('templete_two.product.layoutproduct', compact(
                            'product', 'ProductBrands', 'productImages',
                            'relatedProducts','siteSetting',
                            'ProductHighlights', 'ProductTerms','ProductFaq','ProductSocial'
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
  $limitRequest = $request->get('limit');
            if(empty($limitRequest)){$pagination_count = 12;}else{$pagination_count = $request->get('limit');}
            
            // sort price
            $order_field = 'stock_status';$order_type = 'ASC';
            $sortRequest = $request->get('sort');
            if(!empty($request->get('sort'))){
                switch($sortRequest){
                    case 'asc': $order_field = 'special_price';$order_type = 'asc';break;
                    case 'desc': $order_field = 'special_price';$order_type = 'desc';break;
                    default: $order_field = 'stock_status';$order_type = 'ASC';break;
                }
            }
             // table properties
             $tableName = 'brand_id';
             $tableValue = $brand->id;

             // stock status
             $arr_stock = array(1=>'in_stock',2=>'coming',3=>'new_arrived',4=>'preorder');
             $stockRequest = $request->get('stock');
             if(!empty($stockRequest)){$product_stock = explode(',',$stockRequest);}else{$product_stock = "";}
             
             // price range
             $priceRequest = $request->get('price');
             if(!empty($priceRequest)){$product_price = explode(',',$priceRequest);}else{$product_price = "";}

             $ProductBrands = ProductBrand::where($tableName,$tableValue)
                ->where('status' , 1)
                ->join('products', function ($join)  use ($product_stock,$arr_stock,$product_price,$order_field,$order_type){
                    $join->on('products.id', '=', 'product_brands.product_id')
                            ->where(function ($query) use ($product_stock,$arr_stock,$product_price,$order_field,$order_type) {
                                //price exists
                                if(!empty($product_price)){$query->whereBetween('special_price', [$product_price[0], $product_price[1]]);}
                                //stock exists
                                if(!empty($product_stock)){$query->where('stock_status', "=", $arr_stock[$product_stock[0]]);if(sizeof($product_stock)>1){for($i=1;$i<sizeof($product_stock);$i++){$query->orWhere('stock_status', "=", $arr_stock[$product_stock[$i]]);}}}
                            })->select('products.id','name','slug','regular_price','special_price','offer_price','call_for_price','stock_status','image','product_image_small','status','products.created_at');  
                    })->orderBy($order_field,$order_type)
                ->paginate($pagination_count);

            // dd($ProductBrands);
             $product_lowest = ProductBrand::where($tableName,$tableValue)
             ->where('status' , 1)
             ->join('products', function ($join) {
                 $join->on('products.id', '=', 'product_brands.product_id')
                     ->where('products.status', '=', 1);    
                 })
             ->select('special_price')->orderBy('special_price', 'asc')->first();

             $product_highest = ProductBrand::where($tableName,$tableValue)
             ->where('status' , 1)
             ->join('products', function ($join) {
                 $join->on('products.id', '=', 'product_brands.product_id')
                     ->where('products.status', '=', 1);    
                 })
             ->select('special_price')->orderBy('special_price', 'desc')->first();
           
            
             $brands = Brand::where('status', 1)->select('brands.id','brands.name','slug')->get();
                switch($SiteSetting->templete) {
                    case(1):

                         return view('frontend.product.brandProducts', compact('ProductBrands', 'brand','brands'));

                        break;
         
                    case(2):
                           return view('templete_two.product.brandProducts', compact('ProductBrands', 'brand','sortRequest','tableName','product_lowest','product_highest'));

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
                
                    $products = Product::where('name', 'LIKE', '%' . $search_name . '%')
                    ->where('status', 1)
                    ->select(['products.id','name','subtitle','slug','buying_price','discount_price','regular_price',
                    'special_price','offer_price','price_highlight','call_for_price','product_image_small',
                    'image','image_des','image_alt','status','sku','discount'])
                    ->take(40)->get();
                if(sizeof($products) == 0){
                    //dd('here',sizeof($products));
                    $products = Product::whereRaw(
                    "MATCH(name) AGAINST(?)", 
                    array($search_name)
                    )->where('status', 1)->limit(40)->get();
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
                    
                        $products = Product::where('name', 'LIKE', '%' . $search_name . '%')
                       
                        ->where('status', 1)
                        ->select(['products.id','name','subtitle','slug','buying_price','discount_price','regular_price',
                        'special_price','offer_price','price_highlight','call_for_price','product_image_small',
                        'image','image_des','image_alt','status','sku','discount'])
                        
                        ->take(40)->get();
                    if(sizeof($products) == 0){
                        //dd('here',sizeof($products));
                        $products = Product::whereRaw(
                        "MATCH(name) AGAINST(?)", 
                        array($search_name)
                        )->where('status', 1)->limit(40)->get();
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
    // for schema search now global
    
    public function search($slug,Request $request)
    {
        // dd($slug);
        $SiteSetting = SiteSetting::where('status', 1)->first();
        
        if(!empty($slug)){
            $search_name = $slug;
            $search_slug = str::slug($search_name);
            //$products = Cache::get('search_title_'.$search_slug);
            $limitRequest = $request->get('limit');
            if(empty($limitRequest)){$pagination_count = 12;}else{$pagination_count = $request->get('limit');}
            
            // sort price
            $order_field = 'stock_status';$order_type = 'ASC';
            $sortRequest = $request->get('sort');
            if(!empty($request->get('sort'))){
                switch($sortRequest){
                    case 'asc': $order_field = 'special_price';$order_type = 'asc';break;
                    case 'desc': $order_field = 'special_price';$order_type = 'desc';break;
                    default: $order_field = 'stock_status';$order_type = 'ASC';break;
                }
            }

            // stock status
            $arr_stock = array(1=>'in_stock',2=>'coming',3=>'new_arrived',4=>'preorder');
            $stockRequest = $request->get('stock');
            if(!empty($stockRequest)){$product_stock = explode(',',$stockRequest);}else{$product_stock = "";}
            
            // price range
            $priceRequest = $request->get('price');
            if(!empty($priceRequest)){$product_price = explode(',',$priceRequest);}else{$product_price = "";}
            
            if (empty($products)){
                // get product list
                
                
                    $products = Product::where('name', 'LIKE', '%' . $search_name . '%')
                    
                    ->where('status', 1)
                    ->where(function ($query) use ($product_stock,$arr_stock,$product_price) {
                        //price exists
                        if(!empty($product_price)){$query->whereBetween('special_price', [$product_price[0], $product_price[1]]);}
                        //stock exists
                        if(!empty($product_stock)){$query->where('stock_status', "=", $arr_stock[$product_stock[0]]);if(sizeof($product_stock)>1){for($i=1;$i<sizeof($product_stock);$i++){$query->orWhere('stock_status', "=", $arr_stock[$product_stock[$i]]);}}}
                    })
                    ->select('products.id','name','slug','regular_price','special_price','offer_price','call_for_price','stock_status','image','product_image_small','status','products.created_at')
                    ->orderBy($order_field,$order_type)
                    ->paginate($pagination_count); 
                   
                if(sizeof($products) == 0){
                    //dd('here',sizeof($products));
                    
                    // $products = Product::whereRaw(
                    // "MATCH(name) AGAINST(".$search_name.")"
                    // )->where('status', 1)
                    // ->where(function ($query) use ($product_stock,$arr_stock,$product_price) {
                    //     //price exists
                    //     if(!empty($product_price)){$query->whereBetween('special_price', [$product_price[0], $product_price[1]]);}
                    //     //stock exists
                    //     if(!empty($product_stock)){$query->where('stock_status', "=", $arr_stock[$product_stock[0]]);if(sizeof($product_stock)>1){for($i=1;$i<sizeof($product_stock);$i++){$query->orWhere('stock_status', "=", $arr_stock[$product_stock[$i]]);}}}
                    // })
                    // ->select('products.id','name','slug','regular_price','special_price','offer_price','call_for_price','stock_status','image','product_image_small','status','products.created_at')
                    // ->orderBy($order_field,$order_type)
                    // ->paginate($pagination_count); 
                }
            }
            $product_lowest = Product::where('status',1)->select('special_price')->orderBy('special_price','asc')->first();
            $product_highest = Product::where('status',1)->select('special_price')->orderBy('special_price','desc')->first();
        }else{
            return redirect()->route('/');
        }

        switch($SiteSetting->templete) {
            case(1):
 
                return view('frontend.product.search', compact('products','search_name','product_lowest','product_highest','slug'));
 
                break;
 
            case(2):
                 
                return view('templete_two.product.search', compact('products','search_name','product_lowest','product_highest','slug'));
 
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
