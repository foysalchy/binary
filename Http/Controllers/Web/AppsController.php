<?php
namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Banner;
use App\Models\Brand;
use App\Models\ShopType;
use App\Models\SliderBoxImage;
use App\Models\Post;
use App\Models\Category;

use App\Models\Product;
use App\Models\ProductStockStatus;
use App\Models\ProductRundown;
use App\Models\Popup;
//use App\Models\Coupon;
//use App\Models\Order;
//use App\Models\OrderDetail;
//use Cart;
//use Session;
use Response;

$sliderboximages = SliderBoxImage::where('status', 1)->orderBy('id', 'desc')->take(4)->get();

class AppsController extends Controller
{
    public function index($page,$id=null){
        if($page == 'posts'){
            $posts = Post::where('status', 1)->orderBy('id', 'desc')->get();
            return Response::json( $posts );
        }elseif($page == 'banner'){
            $banners = Banner::where('status', 1)->get();
            return view('frontend.ajaxBanner', compact('banners'));
        }elseif($page == 'brands'){
            $brands = Brand::where('status', '=', 1)->select('name','slug','image')->take(4)->get();
            return Response::json( $brands );
        }elseif($page == 'latest'){
            // $latestProducts = Product::select('products.id','name','subtitle','slug','buying_price','discount_price','regular_price',
            // 'special_price','offer_price','price_highlight','call_for_price','product_image_small',
            // 'image','image_des','image_alt','status','sku','discount','stock_status')
            // ->where('products.status',1)
            // ->orderBy('products.id', 'desc')
            // ->take(8)
            // ->get();
            $latestProducts = ProductRundown::select('products.id','name','subtitle','slug','buying_price','discount_price','regular_price',
            'special_price','offer_price','price_highlight','call_for_price','product_image_small',
            'image','image_des','image_alt','status','sku','discount','stock_status')
            ->join('products', 'product_rundowns.product_id', '=', 'products.id')
            ->orderBy('seq', 'ASC')
            ->take(12)
            ->get();

            return view('frontend.ajaxLatest', compact('latestProducts'));
        }elseif($page == 'offer'){
            $offer_products = Product::select(
                'products.id','name','subtitle','slug','buying_price','discount_price','discount','regular_price',
                'special_price','offer_price','price_highlight','call_for_price','product_image_small',
                'image','image_alt','image_des','status','sku','stock_status'
                )
                ->where('category_id', 12)
                ->where('status', 1)
                ->take(4)
                ->orderBy('products.id', 'desc')
                ->get();
            return view('frontend.ajaxOffer', compact('offer_products'));
        }elseif($page == 'sliderbox'){
            $sliderboximages = SliderBoxImage::where('status', 1)->orderBy('id', 'desc')->take(4)->get();
            return view('frontend.ajaxSliderbox', compact('sliderboximages'));
        }elseif($page == 'hcat'){
            // $categories = Category::where('categories.status', 1)
            //     ->where('position_home','>',0)
            //     ->orderBy('position_home', 'asc')
            //     ->get();
            // //dd($categories);
            // return view('frontend.ajaxCatproduct', compact('categories'));
            $first4shopTypes = ShopType::where('status',1)->get();
            return view('frontend.ajaxFeaturedBox', compact('first4shopTypes'));
        }elseif($page == 'popup'){
            $popups = Popup::where('status',1)->orderBy('id', 'desc')->first();
            //return Response::json( $popups );
            return view('frontend.ajaxPopup', compact('popups'));
        }
        
        
    }
}
