<?php
namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Banner;
use App\Models\Product;
use App\Models\Post;
use App\Models\SiteSetting;
use App\Models\Page;
use App\Models\ProductImage;
use App\Models\Tag;
use App\Models\ProductBrand;
use App\Models\ProductEmi;
use App\Models\BGColor;
use App\Models\Component;
use App\Models\Ad;
use App\Models\ProductStockStatus;
use App\Models\Contact;
use App\Models\HotDealProduct;
use App\Models\FeatureProduct;
use App\Models\ProductRundown;
use App\Models\SliderBoxImage;
use Session;
use DB;
use Cart;


class WelcomeController extends Controller

{

	public function welcome()

	{
        $SiteSetting = SiteSetting::where('status', 1)->first();
        $banners = Banner::orderBy('sequence','ASC')
        ->orderBy('id','DESC')->where('status', 1)->get();
        $ticker = DB::table('ticker')->where('status', 1)->first();
        $sliderBoxImage = SliderBoxImage::select('id','title','img','url')->where('status',1)->first();
        $hotDealProduct = HotDealProduct::select('products.id','name','subtitle','slug','buying_price','discount_price','regular_price',
            'special_price','offer_price','price_highlight','call_for_price','product_image_small',
            'image','image_des','image_alt','products.status','sku','discount','stock_status')
            ->join('products', 'hot_deal_products.product_id', '=', 'products.id')
            ->orderBy('seq', 'ASC')
            ->where('products.stock_status', '=', 'in_stock')
            ->take(12)
            ->get();
        $texts = DB::table('feature_text')->where('status', 1)->orderBy('id','asc')->take(4)->get();
        return view('templete_two.homepage.welcome_new',compact('SiteSetting','banners','ticker','sliderBoxImage','hotDealProduct','texts'));
	}
	
	public function welcomegenerated()

	{
		$SiteSetting = SiteSetting::where('status', 1)->first();
		$ads = Ad::where('status', 1)->get();
        $brands = Brand::where('status', 1)->take(15)->get();
        $bgcolors = BGColor::where('status', 1)->pluck('code','section')->all();
        $banners = Banner::where('status', 1)->get();
        switch($SiteSetting->templete) {
            case(1):
 
                return view('welcome', compact(
                    'ads',
                    'banners',
                    'bgcolors',
                    'brands',
                    'SiteSetting'
                ));
 
                break;
 
            case(2):
                 
                return view('templete_two.homepage.welcomegenerated', compact(

                    'ads',

                    'banners',

                    'bgcolors',

                    'brands',

                    'SiteSetting'

                ));
 
                break;
 
            default:
                $msg = 'Something went wrong.';
        }
	}


    public function welcome_new()

    {

        $SiteSetting = SiteSetting::where('status', 1)->first();
        $ads = Ad::where('status', 1)->get();
        $brands = Brand::where('status', 1)->take(15)->get();
        $bgcolors = BGColor::where('status', 1)->pluck('code','section')->all();
        $banners = Banner::where('status', 1)->get();
        return view('templete_two.homepage.welcome_new', compact(

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

        $SiteSetting = SiteSetting::first();
        switch($SiteSetting->templete) {
            case(1):
 
                return view('frontend.page.page_details', compact('page','pages'));
 
                break;
 
            case(2):
                 
                return view('templete_two.page.page_details', compact('page','pages'));
 
                break;
 
            default:
                $msg = 'Something went wrong.';
        }

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
        
        // session()->put('cart_session', '');
        $SiteSetting = SiteSetting::first();
        $components = Component::where('status', 1)->orderBy('seq', 'ASC')->get();


        switch($SiteSetting->templete) {
            case(1):
 
                return view('frontend.tools.pc_builder', compact('components'));
 
                break;
 
            case(2):
                    
                return view('templete_two.tools.pc_builder', compact('components'));
                break;
 
            default:
                $msg = 'Something went wrong.';
        }

    }
    
    public function getPcBuilderProducts($componentId,$orderField,$orderDir){
        $products = Product::where('status','1')
            ->where('call_for_price',NULL)
            ->where('component_id', $componentId)
            ->where(function($query) {
        			$query->where('stock_status','in_stock')
        			    ->orWhere('stock_status','limited_stock')
        			    ->orWhere('stock_status','new_arrived')
        			    ->orWhere('stock_status','limited')
        				->orWhere('stock_status','with_pc');
            })->orderBy($orderField, $orderDir)->get();
        return $products;
    }
    
    public function ChooseItem($slug, Request $request) {   
        $builder_items = session()->get('cart_session');
        $ids = []; 
        $component = Component::where('slug', $slug)->first();
        
        if (!empty($builder_items)) {
            foreach ($builder_items as $key => $item) {
                if($slug == 'mother-board'){
                    if(!empty($item['component_depends'])){
                        $c_ids = json_decode($item['component_depends']);
                        if (is_array($c_ids)) {
                            foreach ($c_ids as $c_id) {
                                array_push($ids, $c_id);
                            }
                        }
                    }
                }
            }
        }
        
        if($component->slug == 'ram-two'){
            $component_ram = Component::where('slug', 'ram')->first();
            $component_id = $component_ram->id;
        }elseif($component->slug == 'storage-two'){
            $component_ram = Component::where('slug', 'storage')->first();
            $component_id = $component_ram->id;
        }else{
            $component_id = $component->id;
        }
        
        $products = [];
        $arrVals = array_values($ids);
        
        if(!empty($component)){
            if (count($ids) > 0) {
                $products = Product::where('status','1')
                    ->whereIn('id', $arrVals)
                    ->where('call_for_price',NULL)
                    ->where('component_id', $component->id)
                    ->where(function($query) {
                			$query->where('stock_status','in_stock')
                			    ->orWhere('stock_status','limited_stock')
                			    ->orWhere('stock_status','new_arrived')
                			    ->orWhere('stock_status','limited')
                				->orWhere('stock_status','with_pc');
                    })->orderBy('id', 'desc')->get();
            }
            
            elseif($request->get('sort') == 'price_asc') {
          
                $products = $this->getPcBuilderProducts($component_id,'regular_price','asc');
                  
            }
            elseif($request->get('sort') == 'price_desc') {
              
                $products = $this->getPcBuilderProducts($component_id,'regular_price','desc');
                // dd($products);
                  
               }
            elseif($request->get('sort') == 'in_stock') {
                $products = $this->getPcBuilderProducts($component_id,'regular_price','asc');
            }else {
                $products = $this->getPcBuilderProducts($component_id,'id','desc');
            }
            // return view('frontend.product.components_items', compact('products', 'component'));
            $SiteSetting = SiteSetting::first();
    
            switch($SiteSetting->templete) {
                case(1):
     
                    return view('frontend.product.components_items', compact('products', 'component'));
     
                    break;
     
                case(2):
                        
                    return view('templete_two.product.components_items', compact('products', 'component'));
                    break;
     
                default:
                    $msg = 'Something went wrong.';
            }
        }
    }
    //for test only
    public function ChooseItem1($slug) {   
        $builder_items = session()->get('cart_session');
        $ids = []; 
        $component = Component::where('slug', $slug)->first();
        
        if (!empty($builder_items)) {
            foreach ($builder_items as $key => $item) {
                if($slug == 'mother-board'){
                    if(!empty($item['component_depends'])){
                        $c_ids = json_decode($item['component_depends']);
                        if (is_array($c_ids)) {
                            foreach ($c_ids as $c_id) {
                                array_push($ids, $c_id);
                            }
                        }
                    }
                }
            }
        }
        // dd($ids);
        if($component->slug == 'ram-two'){
            $component_ram = Component::where('slug', 'ram')->first();
            $component_id = $component_ram->id;
        }elseif($component->slug == 'storage-two'){
            $component_ram = Component::where('slug', 'storage')->first();
            $component_id = $component_ram->id;
        }else{
            $component_id = $component->id;
        }
        
        $products = [];
        $arrVals = array_values($ids);
        // dd($arrVals);
        if(!empty($component)){
            if (count($ids) > 0) {
                $products = Product::where('component_id', $component_id)
                    ->where('status', 1)
                    ->whereIn('id', $arrVals)
                    ->where('call_for_price',NULL)
                    ->where('stock_status','in_stock')
                    ->orWhere(['stock_status'=>'limited_stock'])
                    ->orderBy('id', 'desc')->get();
                // dd($products);
            }else {
                $products = Product::where('component_id', $component_id)
                    ->where('status', 1)
                    ->where('call_for_price',NULL)
                    ->where(['stock_status'=>'in_stock'])
                    ->orWhere(['stock_status'=>'limited_stock'])
                    ->orderBy('id', 'desc')
                    ->get();
            }
            // return view('frontend.product.components_items', compact('products', 'component'));
            $SiteSetting = SiteSetting::first();
    
            switch($SiteSetting->templete) {
                case(1):
     
                    return view('frontend.product.components_items', compact('products', 'component'));
     
                    break;
     
                case(2):
                        
                    return view('templete_two.product.components_items', compact('products', 'component'));
    
                 
                    break;
     
                default:
                    $msg = 'Something went wrong.';
            }
        }
    }



    public function AddItem(Request $request, $productSlug, $componentSlug)

    {

        // dd($componentSlug);

        $product = Product::where('slug', $productSlug)->first();

        if(empty($product->component_id)){

            $notification=array(

                'message' => ' No product found',

                'alert-type' => 'fail'

            );

            return redirect('tools/pc-builder');

        }

        $component = Component::where('slug', $componentSlug)->first();

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

            return redirect('tools/pc-builder');

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

                return redirect('tools/pc-builder');

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
        return redirect('tools/pc-builder');

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

    $products = Product::select('products.id','name','subtitle','slug','buying_price','discount_price','regular_price',

    'special_price','offer_price','price_highlight','call_for_price','product_image_small',

    'image','image_des','image_alt','status','sku','discount','stock_status')

    ->where('products.status',1)

    ->orderBy('products.id', 'desc')

    ->paginate(12);


        // $products = ProductStockStatus::select('products.id','name','subtitle','slug','buying_price','discount_price','regular_price',

        //     'special_price','offer_price','price_highlight','call_for_price','product_image_small',

        //     'image','image_des','image_alt','status','sku','discount','product_stock_statuses.stock_status')

        //     ->join('products', 'product_stock_statuses.product_id', '=', 'products.id')

        //     ->where([['status',1]])

        //     ->orderBy('products.id', 'desc')

        //     ->paginate(12);

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

        
        $SiteSetting = SiteSetting::where('status', 1)->first();

        switch($SiteSetting->templete) {
            case(1):
 
                return view('frontend.page.contact');
 
                break;
 
            case(2):
                 
                return view('templete_two.page.contact');
 
                break;
 
            default:
                $msg = 'Something went wrong.';
        }

    }


    public function submit_contactus(Request $request) {
          //validate to foysal
        $validated = $request->validate([
            'name' => 'string|required',
            'email' => 'string|required',
            'subject'=>'string',
            'message'=>'string',
        ]);

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
        Session::flash('message', 'Your Message submited !'); 
        Session::flash('alert-class', 'alert-success'); 

        return redirect()->back();
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

    public function ads()
    {
        sleep(4);
        $ads = Ad::where('status', 1)->take(3)->get();

        return view('templete_two.homepage.ajaxComponent.ads', compact('ads'));
    }

    public function hotdeal_product()
    {
        $hotdealProducts = HotDealProduct::select('products.id','name','subtitle','slug','buying_price','discount_price','regular_price',
        'special_price','offer_price','price_highlight','call_for_price','product_image_small',
        'image','image_des','image_alt','products.status','sku','discount','stock_status')
        ->join('products', 'hot_deal_products.product_id', '=', 'products.id')
        ->orderBy('seq', 'ASC')
        ->where('products.stock_status', '=', 'in_stock')
        ->take(12)
        ->get();

        return view('templete_two.homepage.ajaxComponent.hotdeal_product', compact('hotdealProducts'));
    }

    public function feature_product()
    {

        $featureProducts = FeatureProduct::select('products.id','name','subtitle','slug','buying_price','discount_price','regular_price',
        'special_price','offer_price','price_highlight','call_for_price','product_image_small',
        'image','image_des','image_alt','products.status','sku','discount','stock_status')
        ->join('products', 'feature_products.product_id', '=', 'products.id')
        ->orderBy('seq', 'ASC')
        ->where('products.stock_status', '=', 'in_stock')
        ->take(12)
        ->get();


        return view('templete_two.homepage.ajaxComponent.feature_product', compact('featureProducts'));
    }

    public function new_arrival()
    {

         $newProducts = ProductRundown::select('products.id','name','subtitle','slug','buying_price','discount_price','regular_price',
        'special_price','offer_price','price_highlight','call_for_price','product_image_small',
        'image','image_des','image_alt','products.status','sku','discount','stock_status')
        ->join('products', 'product_rundowns.product_id', '=', 'products.id')
        ->orderBy('seq', 'ASC')
        ->where('products.stock_status', '=', 'in_stock')
        ->Orwhere('products.stock_status', '=', 'new_arrived')
        ->take(12)
        ->get();
    return view('templete_two.homepage.ajaxComponent.new_arrival', compact('newProducts'));
    }



    public function load_mobile_menu()
    {

    sleep(2);
        return view('templete_two.homepage.ajaxComponent.load_mobile_menu');
    }

    public function load_web_menu()
    {

     return view('templete_two.homepage.ajaxComponent.load_web_menu');
    }


    public function load_brand()
    {

        $brands = Brand::select('id','name','slug','image')->where('status', 1)->take(15)->get();
        return view('templete_two.homepage.ajaxComponent.load_brand', compact('brands'));
    }


    public function quick_view_ajax($id)
    {
        $product = Product::find($id);
        $productImages = ProductImage::where('product_id', $id)->get();
        return view('templete_two.homepage.components.quick_view', compact('product', 'productImages'));
    }

    public function direction() {
        return view('templete_two.page.direction');
    }
    
    public function private_new(){
        $SiteSetting = SiteSetting::where('status', 1)->first();
        $product = DB::table('products')->where('stock_status', 'private')->first();
        // dd($product);
        return view('templete_two.page.private_product', compact('SiteSetting','product'));
    }
    
    //print product function
     public function print_product(Request $request){
        // dd($request->all());
       $product_id =$request->product_id;
       $products = DB::table('products')->where('id', $product_id)->get();


       return view('templete_two.page.print_product', compact('products'));
    }
    
        public function new_offer(){
            
        $offer= DB::table('offer_page')->where('status', 1)->first();
        $OfferProducts = DB::table('offer')->select('products.id','name','subtitle','slug','buying_price','discount_price','regular_price',
        'special_price','offer_price','price_highlight','call_for_price','product_image_small',
        'image','image_des','image_alt','products.status','sku','discount','stock_status')
        ->join('products', 'offer.product_id', '=', 'products.id')
        ->orderBy('seq', 'asc')
        ->where('products.stock_status', '=', 'in_stock')
        ->get();
        return view('templete_two.page.desktop_offer', compact('OfferProducts','offer'));
    }
    
    public function intel_13(){
        return view('13900k-intel');
    }
    
 public function upload(Request $request)
    {
        if($request->hasFile('upload')) {
            $originName = $request->file('upload')->getClientOriginalName();
            $fileName = pathinfo($originName, PATHINFO_FILENAME);
            $extension = $request->file('upload')->getClientOriginalExtension();
            $fileName = $fileName.'_'.time().'.'.$extension;
        
            $request->file('upload')->move(base_path('images'), $fileName);
   
            $CKEditorFuncNum = $request->input('CKEditorFuncNum');
            $url = asset('images/'.$fileName); 
            $msg = 'Image uploaded successfully'; 
            $response = "<script>window.parent.CKEDITOR.tools.callFunction($CKEditorFuncNum, '$url', '$msg')</script>";
               
            @header('Content-type: text/html; charset=utf-8'); 
            echo $response;
        }
    }
    
    public function post_comment(Request $request){
        $validated = $request->validate([
            'name' => 'required|max:30',
            'email' => 'email:rfc,dns',
            'comment' => 'required',
        ]);
        DB::table('post_comment')->insert(
            [
            'post_id' => $request->post_id,        
            'name' => $request->name,        
            'email' => $request->email,        
            'comment' => $request->comment,   
            'created_at' => now(), 
            'updated_at' => now() 
                 
            ]
        );
        return redirect()->back();
    }
    
        public function complain(){
        return  view('templete_two.page.complain');
    }
    public function complain_store(Request $request){
         //validate to foysal
        // dd($request);
        $validated = $request->validate([
            'name' => 'string|required|max:30',
            'phone' => 'string|required',
            'email' => 'email:rfc,dns',
            'subject' => 'string|required',
            'details' => 'string|required',
            'image' => ['image',
                        'mimes:jpg,webp,png,jpeg,gif,svg',
                        'max:2048'],
        ]);
        
        $image = $request->file('image');
         //dd($image);
        if($image)
        {
            $image_name= $image->getClientOriginalName();
            $image_full_name = $image_name;
            $upload_path = 'images/complain_image/';
            $image_url = $upload_path.$image_full_name;
            $success = $image->move($upload_path, $image_full_name);
        } 
        DB::table('complain')->insert(
            [       
            'name' => $request->name,        
            'phone' => $request->phone,        
            'email' => $request->email,        
            'subject' => $request->subject, 
            'details' => $request->details,
            'image' => $image_url ?? '',
            'created_at' => now(), 
            'updated_at' => now()               
            ]
        );
        return redirect()->back()->with('message','Your Massage has been sent.We will review your request and follow up with you as soon as possible');
    }
    

}
