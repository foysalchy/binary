<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Prosubcategory;
use App\Models\Brand;
use App\Models\Banner;
use App\Models\SliderText;
use App\Models\ShopType;
use App\Models\Product;
use App\Models\Post;
use App\Models\SiteSetting;
use App\Models\Page;
use App\Models\ProductImage;
use App\Models\Tag;
use App\Models\ProductBrand;
use App\Models\ProductEmi;
use App\Models\BGColor;
use App\Models\FeatureBoxFAds;
use App\Models\FeatureBoxSAds;
use App\Models\FeatureBoxTAds;
use App\Models\SidebarAd;
use App\Models\Component;
use App\Models\BuildYourPc;
use App\Models\Background;
use App\Models\Ad;
use App\Models\SliderBoxImage;
use App\Models\ProductStockStatus;
use App\Models\Contact;
use Session;
use DB;
use Cart;
use Artisan;

class WelcomeController extends Controller
{

	public function welcome()
	{
	    
		$categories = Category::where('status', 1)->orderBy('position_id', 'asc')->get();
		$subCategories = Subcategory::where('status', 1)->get();
		//$proSubCategories = Prosubcategory::where('status', 1)->get();
		$brands = Brand::where('status', 1)->take(4)->get();
		$banners = Banner::where('status', 1)->get();
		$sliderTexts = SliderText::where('status', 1)->get();
		//$products = Product::where('status', 1)->get();

		
		$oldestProducts = Product::where('status', 1)->where('stock_status', 'new_arrived')->orderBy('id', 'asc')->take(8)->get();
		
// 		$latestProducts = Product::where('status', 1)->orderBy('id', 'desc')->get();
// 		$upcomingProducts = Product::where('status', 1)->where('stock_status', 'upcoming')->orderBy('id', 'desc')->take(8)->get();
		
        $latestProducts = DB::table('product_stock_statuses')
            ->select('products.id','name','subtitle','slug','buying_price','discount_price','regular_price',
            'special_price','offer_price','price_highlight','call_for_price','product_image_small',
            'image','image_des','image_alt','status','sku','discount')
            ->join('products', 'product_stock_statuses.product_id', '=', 'products.id')
            ->where([['product_stock_statuses.stock_status', 'new_arrived'],['status',1]])
            ->orderBy('products.id', 'desc')
            ->take(4)
            ->get();


        $upcomingProducts = DB::table('product_stock_statuses')
            ->select('products.id','name','subtitle','slug','buying_price','discount_price','regular_price',
            'special_price','offer_price','price_highlight','call_for_price','product_image_small',
            'image','image_des','image_alt','status','sku','discount')
            ->join('products', 'product_stock_statuses.product_id', '=', 'products.id')
            ->where([['product_stock_statuses.stock_status', 'upcoming'],['status',1]])
            ->orderBy('products.id', 'desc')
            ->take(8)
            ->get();
		
		
		
		
		$offeredProducts = Product::where('status', 1)->where('discount', '!=', '')->orderBy('id', 'desc')->take(8)->get();
		$topProducts = Product::where('status', 1)->orderBy('total_sell', 'desc')->take(8)->get();
		//$posts = Post::where('status', 1)->orderBy('id', 'desc')->get();
		$SiteSetting = SiteSetting::where('status', 1)->first();
		$ads = Ad::where('status', 1)->get();



        $first4shopTypes = ShopType::whereIn('id', array(1, 2, 3, 4))->get();

        $second4shopTypes = ShopType::whereIn('id', array(5, 6, 7, 8))->get();
        $third4shopTypes = ShopType::whereIn('id', array(9, 10, 11, 12))->get();


        $f_f_box = BGColor::where('section', '=', 'f_f_box')->where('status', 1)->first();
        $s_f_box = BGColor::where('section', '=', 's_f_box')->where('status', 1)->first();
        $t_f_box = BGColor::where('section', '=', 't_f_box')->where('status', 1)->first();
        $f_box_card = BGColor::where('section', '=', 'f_box_card')->where('status', 1)->first();
        $f_box_card_font_color = BGColor::where('section', '=', 'f_box_card_font_color')->where('status', 1)->first();
        $animated_text_bg = BGColor::where('section', '=', 'animated_text_bg')->where('status', 1)->first();
        $animated_text_font_color = BGColor::where('section', '=', 'animated_text_font_color')->where('status', 1)->first();
        $animated_text_fontsize = BGColor::where('section', '=', 'animated_text_fontsize')->where('status', 1)->first();


        $FeatureBoxFAds = FeatureBoxFAds::where('status', 1)->first();
        $FeatureBoxSAds = FeatureBoxSAds::where('status', 1)->first();
        $FeatureBoxTAds = FeatureBoxTAds::where('status', 1)->first();
        $backgrounds = Background::first();

        $sidebarads = SidebarAd::where('status', 1)->get();
        
        $sliderboximages = SliderBoxImage::where('status', 1)->orderBy('id', 'desc')->take(4)->get();

		return view('welcome', compact(
            'ads',
            'backgrounds',
            'categories',
            'subCategories',
            'brands', 
            'banners', 
            'sliderTexts', 
            'first4shopTypes', 
            'latestProducts', 'oldestProducts', 'offeredProducts', 'topProducts', 'SiteSetting',  'second4shopTypes',  'third4shopTypes', 'f_f_box', 's_f_box', 't_f_box', 'f_box_card', 'f_box_card_font_color', 'FeatureBoxFAds', 'FeatureBoxSAds', 'FeatureBoxTAds', 'animated_text_bg', 'animated_text_font_color', 'animated_text_fontsize', 'sidebarads', 'sliderboximages', 'upcomingProducts'));
        /* 'posts',*/
	}

    public function post_details($slug)
    {
        $post = Post::where('slug', $slug)->first();
		return view('frontend.post.post_details', compact('post'));

    }

    public function page_details($slug)
    {
        $page = Page::where('slug', $slug)->first();
        
        // dd($page);
        
		return view('frontend.page.page_details', compact('page'));

    }

    public function banner_details($slug)
    {
        $banner = Banner::where('slug', $slug)->first();
		return view('frontend.page.banner_details', compact('banner'));

    }

    public function product_details($slug)
    {
        $product = Product::where('slug', $slug)->first();
        $productImages = ProductImage::where('product_id', $product->id)->get();
        $ProductBrands = ProductBrand::where('product_id', $product->id)->get();
        $categories = Category::where('status', 1)->get();
        
        $relatedProducts = Product::where('category_id', $product->category_id)
        					->orwhere('sub_category_id', $product->sub_category_id)
        					->orwhere('pro_sub_category_id', $product->pro_sub_category_id)
        					->where('status', 1)
        					->take(8)
        					->get();
        					
        $ProductEmis = ProductEmi::where('product_id', $product->id)->orderBy('emi_month', 'desc')->get();
        $siteSetting = SiteSetting::where('status', 1)->first();
		return view('frontend.product.product_details', compact('product', 'categories', 'productImages', 'ProductBrands', 'relatedProducts', 'ProductEmis', 'siteSetting'));

    }

    public function viewProductDetails(Request $request)
    {
        $product = Product::where('id', $request->id)->first();
        $productImages = ProductImage::where('product_id', $request->id)->get();
        $ProductBrands = ProductBrand::where('product_id', $request->id)->get();
        $categories = Category::where('status', 1)->get();


        $ProductEmis = ProductEmi::where('product_id', $request->id)->orderBy('emi_month', 'desc')->get();
        $ProductStockStatuses = ProductStockStatus::where('product_id', $request->id)->get();
        
        
        $siteSetting = SiteSetting::where('status', 1)->first();
        return view('frontend.product.ProductDetails', compact('productImages', 'product', 'ProductBrands', 'categories', 'ProductEmis', 'siteSetting', 'ProductStockStatuses'));
    }

    public function PCBuilder()
    {
        $components = Component::where('status', 1)->get();
        return view('frontend.tools.pc_builder', compact('components'));
    }

//     array:1 [▼
//     36 => array:7 [▼
//       "product_id" => 36
//       "component" => "CPU"
//       "name" => "G.Skill Ripjaws SO-DIMM 8GB 3200MHz DDR4L RAM"
//       "special_price" => 5000
//       "regular_price" => 5560
//       "quantity" => 1
//       "image" => "images/product_image/unnamed.png"
//     ]
//   ]
    public function ChooseItem($slug) {   
        // get the cart products
        $builder_items = session()->get('cart_session');

        // rules child holds many ancestor ids
        // store all product compatible ids from cart products
        $ids = []; 
        if (!empty($builder_items)) {
            foreach ($builder_items as $key => $item) {
                $p = Product::find($item['product_id']);
                $c_ids = json_decode($p->compatible_product_ids);
                if (is_array($c_ids)) {
                    foreach ($c_ids as $c_id) {
                        array_push($ids, $c_id);
                    }
                }
          
            }
        }

        $component = Component::where('slug', $slug)->first();

        // if compatible ids are found then show compatible products
        // else show all products under its components
        if (count($ids) > 0) {
            $products = Product::where('component_id', $component->id)
            ->where('status', 1)->whereIn('id', $ids)->simplepaginate(30);
        }else {
            $products = Product::where('component_id', $component->id)->where('status', 1)->simplepaginate(30);
        }
        
        return view('frontend.product.components_items', compact('products', 'component'));
    }

    public function AddItem(Request $request, $productSlug, $componentSlug)
    {
        $component = Component::where('slug', $componentSlug)->first();
        $product = Product::where('slug', $productSlug)->first();

        $components = Component::where('status', 1)->get();

        $user_id = Session::get('user_id');


    
        $id = $product->id;
        $product = DB::table('products')->where('id', $id)->first();
        $cart = session()->get('cart_session');

        //If Cart is empty
        if(!$cart) {
            $cart = [
                    $id => [
                        "product_id" => $product->id,
                        "component" => $component->name,
                        "name" => $product->name,
                        "special_price" => $product->special_price,
                        "regular_price" => $product->regular_price,
                        "quantity" => 1,
                        "image" => $product->image
                    ]
            ];
            session()->put('cart_session', $cart);
            return redirect('tools/pc_builder');
        }


        foreach($cart  as $details) {
            if ($details['component'] == $component->name) {

                $cart[$id] = [
                    "product_id" => $product->id,
                    "component" => $component->name,
                    "name" => $product->name,
                    "special_price" => $product->special_price,
                    "regular_price" => $product->regular_price,
                    "quantity" => 1,
                    "image" => $product->image
                ];


                session()->put('cart_session', $cart);
                return redirect('tools/pc_builder');
            }
        }




        $cart[$id] = [
            "product_id" => $product->id,
            "component" => $component->name,
            "name" => $product->name,
            "special_price" => $product->special_price,
            "regular_price" => $product->regular_price,
            "quantity" => 1,
            "image" => $product->image
        ];
        session()->put('cart_session', $cart);
        
        return redirect('tools/pc_builder');



        // $data = new BuildYourPc();
        // $data->user_id = $user_id;
        // $data->product_id = $product->id;
        // $data->save();

        // return redirect()->route('tools/pc_builder');


    }

   public function removeFromSession(Request $request)
   {
        if($request->id) {
            $cart = session()->get('cart_session');
            if(isset($cart[$request->id])) {
                unset($cart[$request->id]);
                session()->put('cart_session', $cart);
            }
            return redirect()->back();
        }

   }

   public function addToCartArray(Request $request)
   {
        
        $product_id = $request->product_id;

        foreach ($product_id as $key => $value) {
            $product_details = DB::table('products')->where('id', $value)->first();

            $data = array();
            $data['id']=$product_details->id;
            $data['name']=$product_details->name;

            if ($product_details->discount_price) {
                $data['price']=$product_details->discount_price;
            }

            if ($product_details->special_price) {
                $data['price']=$product_details->special_price;
            }


            $data['qty']= 1;
            $data['weight']=$product_details->id;
            $data['options']['image']=$product_details->image;
            $cart = Cart::add($data);

        }

        $notification=array(
            'message' => ' Added Product In Cart!!',
            'alert-type' => 'success'
        );
        // return redirect()->back()->with($notification);
        return redirect()->route('cart');
   }


   public function print_pc(Request $request)
   {
        $product_id = $request->product_id;
        
        return view('frontend.tools.pages.print_your_list',  compact('product_id'));

   }

   public function latestproducts(Request $request)
   {

        $products = DB::table('product_stock_statuses')
            ->join('products', 'product_stock_statuses.product_id', '=', 'products.id')
            ->where('product_stock_statuses.stock_status', 'new_arrived')
            ->orderBy('products.id', 'desc')
            ->where('products.status', 1)
            ->paginate(12);


        return view('frontend.product.latestProducts', compact('products'));  
   }
   

   public function latest_offer_products(Request $request)
   {

        $products = Product::where('category_id', 12)->where('status', 1)->simplepaginate(12);


        return view('frontend.product.latest_offer_products', compact('products'));  
   }   
   

   public function upcomingproducts(Request $request)
   {
        $products = Product::orderBy('id', 'desc')->where('status', 1)->simplepaginate(12);


        $products = DB::table('product_stock_statuses')
            ->join('products', 'product_stock_statuses.product_id', '=', 'products.id')
            ->where('product_stock_statuses.stock_status', 'upcoming')
            ->orderBy('products.id', 'desc')
            ->where('products.status', 1)
            ->simplepaginate(12);

        return view('frontend.product.upcomingproducts', compact('products'));  
   }

   public function newestArrivals(Request $request, $slug)
   {
        $category = Category::where('slug', $slug)->first();
        $products = Product::orderBy('created_at', 'desc')->where('status', 1)->where('category_id', $category->id)->simplepaginate(12);
        return view('frontend.product.newestArrivals', compact('products'));  
   }

   public function topPopular(Request $request, $slug)
   {
        $category = Category::where('slug', $slug)->first();
        $products = Product::orderBy('total_sell', 'desc')->where('status', 1)->where('category_id', $category->id)->simplepaginate(12);
        return view('frontend.product.topPopular', compact('products'));  
   }

    public function contact_us() {
        return view('frontend.page.contact');
    }

    public function submit_contactus(Request $request) {
        $name = $request->name;
        $email = $request->email;
        $subject = $request->subject;
        $message = $request->message;

        $contact = new Contact();
        $contact->name = $name;
        $contact->email = $email;
        $contact->subject = $subject;
        $contact->message = $message;
        $contact->save();
        
        $notification=array(
            'message' => 'Your Message Send Successfully !',
            'alert-type' => 'success'
        );
        return redirect()->back()->with($notification);


    }    
    
    public function posts()
    {
        $posts = Post::orderBy('id', 'desc')->where('status', 1)->simplepaginate(20);
        $categories = Category::orderBy('id', 'desc')->where('status', 1)->get();
        return view('frontend.post.posts', compact('posts', 'categories'));
    }
    
        public function cat_posts($slug) {
        $category = Category::where('slug', $slug)->first();
        $posts = Post::orderBy('id', 'desc')->where('category_id', $category->id)->where('status', 1)->simplepaginate(20);
        return view('frontend.post.cat_posts', compact('posts'));
    }

    public function tag($slug) {
        $tag = Tag::where('slug', $slug)->first();

        $posts = DB::table('post_tags')
            ->join('tags', 'post_tags.tag_id', '=', 'tags.id')
            ->join('posts', 'post_tags.post_id', '=', 'posts.id')
            ->where('post_tags.tag_id', $tag->id)
            ->select('post_tags.tag_id', 'post_tags.post_id', 'tags.id', 'posts.*')
            ->simplepaginate(20);

        return view('frontend.post.tag_posts', compact('posts'));
    }
}















