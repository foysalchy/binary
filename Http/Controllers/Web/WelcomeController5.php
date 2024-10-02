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
		$SiteSetting = SiteSetting::where('status', 1)->first();
		$ads = Ad::where('status', 1)->get();
        $brands = Brand::where('status', 1)->take(15)->get();
        $bgcolors = BGColor::where('status', 1)->pluck('code','section')->all();
        $banners = Banner::where('status', 1)->get();
		return view('welcome', compact(
            'ads',
            'banners',
            'bgcolors',
            'brands',
            'SiteSetting'
        ));
	}
    
    public function intel()
	{
		$SiteSetting = SiteSetting::where('status', 1)->first();
		$ads = Ad::where('status', 1)->get();
        $bgcolors = BGColor::where('status', 1)->pluck('code','section')->all();
        $banners = Banner::where('status', 1)->get();
		return view('intel', compact(
            'ads',
            'banners',
            'bgcolors',
            'SiteSetting'
        ));
	}

    public function post_details($slug = null)
    {
        $post = Post::where('slug', $slug)->first();
		return view('frontend.post.post_details', compact('post'));

    }

    public function page_details($slug = null)
    {
        $page = [];
        if(!empty($slug)){
            $page = Page::where('slug', $slug)->where('status', 1)->first();
        }
        $pages = Page::where('status', 1)->paginate(10);
        //dd($pages);
		return view('frontend.page.page_details', compact('page','pages'));

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

        //find dependancy
        /*$pro_dep = Product::where('compatible_product_ids','!=',null)
            ->select('id','name','component_id')
            ->get();
        $pro_dep_component = [];
        foreach($pro_dep as $product){
            
        }*/
        //dd($pro_dep);

        // rules child holds many ancestor ids
        // store all product compatible ids from cart products
        
        $ids = []; 
        if (!empty($builder_items)) {
            foreach ($builder_items as $key => $item) {
                //dd($item);
                if($slug == 'mother-board'){
                    //check for dependent exists
                    if(!empty($item['component_depends'])){
                        //$p = Product::find($item['product_id']);
                        $c_ids = json_decode($item['component_depends']);
                        //dd($c_ids);
                        if (is_array($c_ids)) {
                            foreach ($c_ids as $c_id) {
                                array_push($ids, $c_id);
                            }
                        }
                    }
                }elseif($slug == 'ram'){
                    $ids = Product::where('name', 'LIKE', '%DDR4%')->select('id')->get()->toArray();
                }
                
            }
        }
//dd($ids);
        $component = Component::where('slug', $slug)->first();

        // if compatible ids are found then show compatible products
        // else show all products under its components
        $products = [];

        //component exists with slug
        if(!empty($component)){
            //compatible ids are found
            if (count($ids) > 0) {
                //dd('here');
                $products = Product::where('component_id', $component->id)
                    ->where('status', 1)
                    ->where('call_for_price',NULL)
                    ->where(['stock_status'=>'in_stock'])
                    ->whereIn('id', $ids)->orderBy('id', 'desc');
                    //->simplepaginate(30);
            }else {
                $products = Product::where('component_id', $component->id)
                    ->where('status', 1)
                    ->where('call_for_price',NULL)
                    ->where(['stock_status'=>'in_stock'])
                    ->orderBy('id', 'desc')
                    ->simplepaginate(30);
            }
            return view('frontend.product.components_items', compact('products', 'component'));
        }
        //dd($products);
        
    }

    public function AddItem(Request $request, $productSlug, $componentSlug)
    {
        
        $product = Product::where('slug', $productSlug)->first();
        if(empty(is_integer($componentSlug))){
            $component = Component::where('id', $product->component_id)->first();
        }else{
            $component = Component::where('slug', $componentSlug)->first();
        }

        $components = Component::where('status', 1)->get();

        $user_id = Session::get('user_id');


    
        $id = $product->id;
        $product = DB::table('products')->where('id', $id)->first();
        
        if(!empty($product->compatible_product_ids)){
            $dep_ids = json_decode($product->compatible_product_ids);
        }

        $cart = session()->get('cart_session');

        //If Cart is empty
        if(!$cart) {
            $cart = [
                    $id => [
                        "product_id" => $product->id,
                        "component" => $component->name,
                        "component_id" => $component->id,
                        "component_slug" => $component->slug,
                        "component_depends"=>$product->compatible_product_ids,
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
                    "component_id" => $component->id,
                    "component_slug" => $component->slug,
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

//dd('here');


        $cart[$id] = [
            "product_id" => $product->id,
            "component" => $component->name,
            "component_id" => $component->id,
            "component_slug" => $component->slug,
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
       //dd($request);
        if($request->id) {
            $cart = session()->get('cart_session');
            //dd($cart);
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
        $products = ProductStockStatus::select('products.id','name','subtitle','slug','buying_price','discount_price','regular_price',
            'special_price','offer_price','price_highlight','call_for_price','product_image_small',
            'image','image_des','image_alt','status','sku','discount','product_stock_statuses.stock_status')
            ->join('products', 'product_stock_statuses.product_id', '=', 'products.id')
            ->where([['product_stock_statuses.stock_status', 'new_arrived'],['status',1]])
            ->orderBy('products.id', 'desc')
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
        $products = $category = [];
        $category = Category::where('slug', $slug)->first();
        if(!empty($category)){
            $products = Product::orderBy('created_at', 'desc')->where('status', 1)->where('category_id', $category->id)->paginate(12);
        }else{
            $products = "";
        }
        return view('frontend.product.newestArrivals', compact('products','category'));  
   }

   public function topPopular(Request $request, $slug)
   {
    $products = $category = [];
       if(!empty($slug)){
        $category = Category::where('slug', $slug)->first();
        if(!empty($category)){
            $products = Product::orderBy('total_sell', 'desc')->where('status', 1)->where('category_id', $category->id)->simplepaginate(12);
        }
        return view('frontend.product.topPopular', compact('products','category')); 
       }
        
         
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
        $posts = $category = [];
        $postcategory = Category::where('slug', $slug)->first();
        if(!empty($postcategory)){
            $posts = Post::orderBy('id', 'desc')->where('category_id', $postcategory->id)->where('status', 1)->paginate(20);
        }
        return view('frontend.post.cat_posts', compact('posts','postcategory'));
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















