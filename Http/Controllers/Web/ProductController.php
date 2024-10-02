<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductImage;
use App\Models\ProductBrand;
use App\Models\Product;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Prosubcategory;
use App\Models\Supplier;
use App\Models\Brand;
use App\Models\ShopType;
use App\Models\ProductShop;
use App\Models\ProductEmi;
use App\Models\Component;
use App\Models\ProductStockStatus;
use App\Models\ProductHighlight;
use App\Models\ProductRundown;
use App\Models\ProductFaq;
use App\Models\ProductSocial;
use App\Models\ProductTerms;
use App\Models\FreeItemForClient;
use App\Models\HotDealProduct;
use App\Models\FeatureProduct;
use App\Models\Html;
use Redirect;
use Cache;

use Str;
use Auth;

use Illuminate\Support\Facades\DB;

use Intervention\Image\Facades\Image;
use Maatwebsite\Excel\Excel;
use App\Imports\ProductImport;
use App\Exports\ProductExport;


class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    private $excel;
    
    public function setCacheForHighlight(){
        
        $rows = ProductHighlight::orderBy('product_id')->get();
        // dd($rows);
        $set_product_id = '';
        $data = [];
        $i=0;
        foreach($rows as $row){
            $data[$row->product_id][$i] = $row->highlights;
            $i++;
            Cache::put('highlights_'.$row->product_id,json_encode($data[$row->product_id]));
        }
        // Cache::put('highlights',json_encode($data[$row->product_id]));
        // $value = Cache::get('highlights_6');
        // dd($data);
        // dd($value);
        // die();
        return true;
        
    }

    public function index(Request $request)
    {
        // $this->setCacheForHighlight();
        // die();
        $user_id = Auth::user();
        
        if($user_id){
            $rundowns = ProductRundown::pluck('product_id')->toArray();
            $hotdeals = HotDealProduct::pluck('product_id')->toArray();
            $features = FeatureProduct::pluck('product_id')->toArray();
            $offers = DB::table('offer')->pluck('product_id')->toArray();
            $upcomings = DB::table('upcoming')->pluck('product_id')->toArray();
   
          $products = Product::query();
          if (request('term')) {
          $searchString = explode(" ", request('term'));
          foreach($searchString as $value)
                {
                    $products = $products->where(function ($q) use ($value) {
                                    $q->orWhere('name', 'like', "%{$value}%");
                                    $q->orWhere('stock_status', 'like', "%{$value}%");
                                    
                                });
                }
            }
    
            $products= $products->orderBy('id', 'DESC')->paginate(50);
            return view('backend.admin.product.index', compact('products','rundowns', 'hotdeals', 'features','offers','upcomings'));
        }else{
            return redirect('login');
        }   
    }

    /**
     * Create image slug
     */
    function createUrlSlug($urlString){
        $slug=preg_replace('/[^A-Za-z0-9-]+/', '-', $urlString);
        return $slug;
    }

    public function send_home($id){
        $product = Product::where('id',$id)->first();
        //dd($product);
        $rundown = ProductRundown::where('product_id',$id)->first();
        
        if(!empty($rundown)){
            $notification=array(
                'message' => 'Already in home list',
                'alert-type' => 'success'
            );
            return redirect('admin/product')->with($notification);
        }else{
            $rundown = new ProductRundown();
            $rundown->product_id = $id;
            if($rundown->save()){
                    //update html
                    $html = new Html();
                    $html->generator('latest');
                }
            $notification=array(
                'message' => 'Successfully Done',
                'alert-type' => 'success'
            );
            return redirect('admin/product')->with($notification);
        }
        //return view('backend.admin.product.producthome',compact('product'));
    }

    public function send_to_feature($id)
    {
        $product = Product::where('id',$id)->first();
        $feature = FeatureProduct::where('product_id', $id)->first();
        $last = FeatureProduct::orderBy('seq', 'desc')->count();

        if(!empty($feature)){
            $notification=array(
                'message' => 'Already in feature list',
                'alert-type' => 'success'
            );
            return redirect('admin/product')->with($notification);
        }else{
            $feature = new FeatureProduct();
            $feature->product_id = $id;
            $feature->seq = $last + 1;
            $feature->status = 1;
            if( $feature->save()){
                //update html
                $html = new Html();
                $html->generator('feature');
            }
            $notification=array(
                'message' => 'Successfully Done',
                'alert-type' => 'success'
            );
            return redirect('admin/product')->with($notification);
        }


    }

    public function remove_from_feature($id){
        $feature = FeatureProduct::where('product_id',$id)->first();
        if(!empty($feature)){
            $Productfeature = FeatureProduct::where('product_id', $id)->delete();
             if($Productfeature){
                //update html
                $html = new Html();
                $html->generator('feature');
            }
            $notification=array(
                'message' => 'Removed from feature list',
                'alert-type' => 'success'
            );
            return redirect('admin/product')->with($notification);
        }else{
            $notification=array(
                'message' => 'Not found',
                'alert-type' => 'warning'
            );
            return redirect('admin/product')->with($notification);
        }
        //return view('backend.admin.product.producthome',compact('product'));
    }

    public function feature_product_seq(){
        $features = FeatureProduct::select('feature_products.seq','products.id','name','subtitle','slug','buying_price','discount_price','regular_price',
            'special_price','offer_price','price_highlight','call_for_price','product_image_small',
            'image','product_image_thumb','image_des','image_alt','sku','discount')
            ->join('products', 'feature_products.product_id', '=', 'products.id')
            ->orderBy('seq', 'ASC')
            ->get();
        if(empty($features)){
            $notification=array(
                'message' => 'Please select product for hotdeal first',
                'alert-type' => 'warning'
            );
            return redirect('admin/product')->with($notification);
        }else{
            return view('backend.admin.product.feature', compact('features'));  
        }
        //return view('backend.admin.product.producthome',compact('product'));
    }


    public function save_feature_seq(Request $request){
        FeatureProduct::truncate();
        $newFeatureProduct = $request->product;
        if ($newFeatureProduct) {
            $i=1;
            foreach ($newFeatureProduct as $key => $value ){
                $rundown = new FeatureProduct();
                $rundown->product_id = $key;
                $rundown->seq = $i;
                $rundown->status = 1;
                if($rundown->save()){
                    //update html
                    $html = new Html();
                    $html->generator('feature');
                }
                $i++;
            } 
            $notification=array(
                'message' => 'Successfully Feature Saved',
                'alert-type' => 'success'
            );  
            return redirect('admin/feature-product-seq')->with($notification);
        }
        else{
            $html = new Html();
            $html->generator('feature');
            $notification=array(
                'message' => 'feature section empty',
                'alert-type' => 'warning'
            ); 
        } 
        return redirect('admin/feature-product-seq')->with($notification);
    }
    
      //offer product function starts

    public function send_to_offer($id)
    {
        $product = Product::where('id',$id)->first();
        // dd($product);
        $offer = DB::table('offer')->where('product_id', $id)->first();
        $last = DB::table('offer')->orderBy('seq', 'desc')->count();

        if(!empty($offer)){
            $notification=array(
                'message' => 'Already in offer list',
                'alert-type' => 'success'
            );
            return redirect('admin/product')->with($notification);
        }else{
            DB::table('offer')->insert(
                [
                    'product_id' => $id,
                    'seq' => $last + 1,
                    'status' =>  1,
                ]
            );

            $notification=array(
                'message' => 'Successfully Done',
                'alert-type' => 'success'
            );
            return redirect('admin/product')->with($notification);
        }


    }
    
        public function remove_from_offer($id){
        $offer =DB::table('offer')->where('product_id',$id)->first();
        if(!empty($offer)){
            $Productoffer = DB::table('offer')->where('product_id', $id)->delete();
            if($Productoffer){
                //update html
                $html = new Html();
                $html->generator('feature');
            }
            $notification=array(
                'message' => 'Removed from Offer list',
                'alert-type' => 'success'
            );
            return redirect('admin/product')->with($notification);
        }else{
            $notification=array(
                'message' => 'Not found',
                'alert-type' => 'warning'
            );
            return redirect('admin/product')->with($notification);
        }
        //return view('backend.admin.product.producthome',compact('product'));
    }
    
        public function offer_product_seq(){
        $offers = DB::table('offer')->select('offer.seq','products.id','name','subtitle','slug','buying_price','discount_price','regular_price',
            'special_price','offer_price','price_highlight','call_for_price','product_image_small',
            'image','product_image_thumb','image_des','image_alt','sku','discount')
            ->join('products', 'offer.product_id', '=', 'products.id')
            ->orderBy('seq', 'ASC')
            ->get();
            // dd($offers);
        if(empty($offers)){
            $notification=array(
                'message' => 'Please select product for Offer first',
                'alert-type' => 'warning'
            );
            return redirect('admin/product')->with($notification);
        }else{
            return view('backend.admin.product.offer', compact('offers'));  
        }
        //return view('backend.admin.product.producthome',compact('product'));
    }
    
        public function save_offer_seq(Request $request){
        DB::table('offer')->truncate();
        $newOfferProduct = $request->product;
        if ($newOfferProduct) {
            $i=1;
            foreach ($newOfferProduct as $key => $value ){
               
                DB::table('offer')->insert(
                    [
                        'product_id' => $key,
                        'seq' => $i,
                        'status' =>  1,
                    ]
                );
                $i++;
                // dd($newOfferProduct);
            } 
            $notification=array(
                'message' => 'Successfully Feature Saved',
                'alert-type' => 'success'
            );  
            return redirect('admin/offer-product-seq')->with($notification);
        }
        $notification=array(
            'message' => 'Something error',
            'alert-type' => 'warning'
        ); 
        return redirect('admin/offer-product-seq')->with($notification);
    }
    
    
      //upcoming product sequence starts
    public function send_to_upcoming($id)
    {
        $product = Product::where('id',$id)->first();
        // dd($product);
        $offer = DB::table('upcoming')->where('product_id', $id)->first();
        $last = DB::table('upcoming')->orderBy('seq', 'desc')->count();

        if(!empty($offer)){
            $notification=array(
                'message' => 'Already in upcoming list',
                'alert-type' => 'success'
            );
            return redirect('admin/product')->with($notification);
        }else{
           $upcoming_save= DB::table('upcoming')->insert(
                [
                    'product_id' => $id,
                    'seq' => $last + 1,
                    'status' =>  1,
                ]
            );
            if($upcoming_save){
                //update html
                $html = new Html();
                $html->generator('upcoming');
            }

            $notification=array(
                'message' => 'Successfully Done',
                'alert-type' => 'success'
            );
            return redirect('admin/product')->with($notification);
        }


    }


    public function remove_from_upcoming($id){
        $offer =DB::table('upcoming')->where('product_id',$id)->first();
        if(!empty($offer)){
            $Productoffer = DB::table('upcoming')->where('product_id', $id)->delete();
            if($Productoffer){
                //update html
                $html = new Html();
                $html->generator('upcoming');
            }
            $notification=array(
                'message' => 'Removed from upcoming list',
                'alert-type' => 'success'
            );
            return redirect('admin/product')->with($notification);
        }else{
            $notification=array(
                'message' => 'Not found',
                'alert-type' => 'warning'
            );
            return redirect('admin/product')->with($notification);
        }
        //return view('backend.admin.product.producthome',compact('product'));
    }


    public function upcoming_product_seq(){
        $upcoming = DB::table('upcoming')->select('upcoming.seq','products.id','name','subtitle','slug','buying_price','discount_price','regular_price',
            'special_price','offer_price','price_highlight','call_for_price','product_image_small',
            'image','product_image_thumb','image_des','image_alt','sku','discount')
            ->join('products', 'upcoming.product_id', '=', 'products.id')
            ->orderBy('seq', 'ASC')
            ->get();
            // dd($offers);
        if(empty($upcoming)){
            $notification=array(
                'message' => 'Please select product for upcoming first',
                'alert-type' => 'warning'
            );
            return redirect('admin/product')->with($notification);
        }else{
            return view('backend.admin.product.upcoming', compact('upcoming'));  
        }
        //return view('backend.admin.product.producthome',compact('product'));
    }


    public function save_upcoming_seq(Request $request){
        DB::table('upcoming')->truncate();
        $newUpcomingProduct = $request->product;
        if ($newUpcomingProduct) {
            $i=1;
            foreach ($newUpcomingProduct as $key => $value ){               
              $upcoming_save=  DB::table('upcoming')->insert(
                    [
                        'product_id' => $key,
                        'seq' => $i,
                        'status' =>  1,
                    ]
                );
                if($upcoming_save){
                    //update html
                    $html = new Html();
                    $html->generator('upcoming');
                }
                $i++;
                // dd($newUpcomingProduct);
            } 
            $notification=array(
                'message' => 'Successfully upcoming Saved',
                'alert-type' => 'success'
            );  
            return redirect('admin/upcoming-product-seq')->with($notification);
        }
        else{
            $html = new Html();
            $html->generator('upcoming');
        }
        $notification=array(
            'message' => 'Upcoming Product section is empty',
            'alert-type' => 'warning'
        ); 
        return redirect('admin/upcoming-product-seq')->with($notification);
    }


   //upcoming product sequence end

    

    public function send_to_hot_deal($id)
    {
        $product = Product::where('id',$id)->first();
        $hotdeal = HotDealProduct::where('product_id', $id)->first();
        $last = HotDealProduct::orderBy('seq', 'desc')->count();

        if(!empty($hotdeal)){
            $notification=array(
                'message' => 'Already in hotdeal list',
                'alert-type' => 'success'
            );
            return redirect('admin/product')->with($notification);
        }else{
            $hotdeal = new HotDealProduct();
            $hotdeal->product_id = $id;
            $hotdeal->seq = $last + 1;
            $hotdeal->status = 1;
            $hotdeal->save();
            $notification=array(
                'message' => 'Successfully Done',
                'alert-type' => 'success'
            );
            return redirect('admin/product')->with($notification);
        }


    }

    public function remove_from_hot_deal($id){
        $hotdeal = HotDealProduct::where('product_id',$id)->first();
        if(!empty($hotdeal)){
            $Producthotdeal = HotDealProduct::where('product_id', $id)->delete();
            $notification=array(
                'message' => 'Removed from hotdeal list',
                'alert-type' => 'success'
            );
            return redirect('admin/product')->with($notification);
        }else{
            $notification=array(
                'message' => 'Not found',
                'alert-type' => 'warning'
            );
            return redirect('admin/product')->with($notification);
        }
        //return view('backend.admin.product.producthome',compact('product'));
    }

    public function hotdeal_product_seq(){
        $hotdeals = HotDealProduct::select('hot_deal_products.seq','products.id','name','subtitle','slug','buying_price','discount_price','regular_price',
            'special_price','offer_price','price_highlight','call_for_price','product_image_small',
            'image','product_image_thumb','image_des','image_alt','sku','discount')
            ->join('products', 'hot_deal_products.product_id', '=', 'products.id')
            ->orderBy('seq', 'ASC')
            ->get();
        if(empty($hotdeals)){
            $notification=array(
                'message' => 'Please select product for hotdeal first',
                'alert-type' => 'warning'
            );
            return redirect('admin/product')->with($notification);
        }else{
            return view('backend.admin.product.hotdeal', compact('hotdeals'));  
        }
        //return view('backend.admin.product.producthome',compact('product'));
    }

    public function save_hotdeal_seq(Request $request){
        HotDealProduct::truncate();
        $newHotDealProduct = $request->product;
        if ($newHotDealProduct) {
            $i=1;
            foreach ($newHotDealProduct as $key => $value ){
                $rundown = new HotDealProduct();
                $rundown->product_id = $key;
                $rundown->seq = $i;
                $rundown->status = 1;
                $rundown->save();
                $i++;
            } 
            $notification=array(
                'message' => 'Successfully hot deal product  Saved',
                'alert-type' => 'success'
            );  
            return redirect('admin/hotdeal-product-seq')->with($notification);
        }
        $notification=array(
            'message' => 'hot deal product empty',
            'alert-type' => 'warning'
        ); 
        return redirect('admin/hotdeal-product-seq')->with($notification);
    }

    public function send_seq(){
        // $rundowns = ProductRundown::get()->join('products');
        $rundowns = ProductRundown::select('products.id','name','subtitle','slug','buying_price','discount_price','regular_price',
            'special_price','offer_price','price_highlight','call_for_price','product_image_small',
            'image','product_image_thumb','image_des','image_alt','status','sku','discount')
            ->join('products', 'product_rundowns.product_id', '=', 'products.id')
            ->orderBy('seq', 'ASC')
            ->get();
        // dd($rundowns);
        if(empty($rundowns)){
            $notification=array(
                'message' => 'Please select product for home page first',
                'alert-type' => 'warning'
            );
            return redirect('admin/product')->with($notification);
        }else{
            return view('backend.admin.product.rundown', compact('rundowns'));  
        }
        //return view('backend.admin.product.producthome',compact('product'));
    }

    public function save_seq(Request $request){
        ProductRundown::truncate();
        $newProductRundown = $request->product;
        if ($newProductRundown) {
            $i=1;
            foreach ($newProductRundown as $key => $value ){
                $rundown = new ProductRundown();
                $rundown->product_id = $key;
                $rundown->seq = $i;
                if($rundown->save()){
                    //update html
                    $html = new Html();
                    $html->generator('latest');
                }
                $i++;
            } 
            $notification=array(
                'message' => 'Successfully Rundown Saved',
                'alert-type' => 'success'
            );  
            return redirect('admin/send-seq')->with($notification);
        }
        $notification=array(
            'message' => 'Something error',
            'alert-type' => 'warning'
        ); 
        return redirect('admin/send-seq')->with($notification);
    }

    public function send_home_remove($id){
        $rundown = ProductRundown::where('product_id',$id)->first();
        if(!empty($rundown)){
            $ProductRundown = ProductRundown::where('product_id', $id)->delete();
            if($ProductRundown){
                //update html
                $html = new Html();
                $html->generator('feature');
            }
            $notification=array(
                'message' => 'Removed from home list',
                'alert-type' => 'success'
            );
            return redirect('admin/product')->with($notification);
        }else{
            $notification=array(
                'message' => 'Not found',
                'alert-type' => 'warning'
            );
            return redirect('admin/product')->with($notification);
        }
        //return view('backend.admin.product.producthome',compact('product'));
    }

    public function sethome($id)
    {
        dd($id);
        return view('backend.admin.product.producthome', compact('products','search_name'));  
    }

    public function productSearch(Request $request)
    {
        if(!empty($request->product_name)){
            $rundowns = ProductRundown::pluck('product_id')->toArray();
            $search_name = $request->product_name;
            $products = Product::whereRaw(
                "MATCH(name) AGAINST(?)", 
                array($search_name)
                )->where('status', 1)->limit(50)->get();
            return view('backend.admin.product.productSearch', compact('products','search_name','rundowns'));
        }else{
            return redirect('login');
        }     
    }

    public function __index()
    {
        $user_id = Auth::user();
        if($user_id){
            $products = Product::orderBy('id', 'desc')->get();
            return view('backend.admin.product.top', compact('products'));
        }else{
            return redirect('login');
        }        
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        
        $user_id = Auth::user();
        if($user_id){
            $brands = Brand::where('status', 1)->get();
            $categories = Category::where('status', 1)->get();
            $suppliers = Supplier::where('status', 1)->get();
            $shop_types = ShopType::where('status', 1)->get();
            $components = Component::where('status', 1)->get();
            $products = Product::all();
            return view('backend.admin.product.create', compact('products', 'brands', 'categories', 'suppliers', 'shop_types', 'components'));
        }else{
            return redirect('login');
        }        
        

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // validataion removed from management
        $validatedData = $request->validate([
            'name' => 'required|unique:products',
            'slug' => 'unique:products',
            'category_id' =>'required',
            'price_highlight' =>'required',
            'stock_status' =>'required',
            'image' =>'required',
        ]);

        $user_id = Auth::user()->id;

        $data = new Product();
        // compatitable product add
        $data->name = $request->name;
        $data->subtitle = $request->subtitle;
        $data->slug = str::slug($request->name);
        $data->user_id = $user_id;
        $data->supplier_id = $request->supplier_id;
        $data->category_id = $request->category_id;
        $data->sub_category_id = $request->sub_category_id;
        $data->pro_sub_category_id = $request->pro_sub_category_id;
        $data->pro_pro_category_id = $request->pro_pro_category_id;
        $data->component_id = $request->component_id;

        $data->max_order_qty = $request->max_order_qty;
        $data->min_order_qty = $request->min_order_qty;

        $sku = rand(1,90000);
        $data->sku = $sku;

        $data->buying_price = $request->buying_price ?? '0';
        $data->regular_price = $request->regular_price;
        $data->special_price = $request->special_price;
        $data->offer_price = $request->offer_price;
        $data->discount_price = $request->discount_price;
        $data->discount = $request->discount;
        $data->video = $request->video;

        $data->price_highlight = $request->price_highlight;
        
        $data->qty = $request->qty;
        $data->total_sell = $request->total_sell;
        $data->total_product = $request->total_product;
        $data->barcode = $request->barcode;
        $data->description = $request->description;
        $data->section_1 = $request->section_1;
        $data->section_2 = $request->section_2;
        $data->section_3 = $request->section_3;
        $data->product_faq = $request->product_faq;
        $data->specification = $request->specification;
        $data->product_highlights = $request->product_highlights;
        if(!empty($request->note_link)){
            $data->note = $request->note.'----'.$request->note_link;
        }else{
            $data->note = $request->note;
        }
        $data->shop = $request->shop;


        //New Add Hobe
        $data->meta_title = $request->meta_title;
        $data->meta_keywords = $request->meta_keywords;
        $data->meta_des = $request->meta_des;
        $data->canonical_url = $request->canonical_url;
        $data->call_for_price = $request->call_for_price;
        $data->estimated_price = $request->estimated_price;
        $data->warranty = $request->warranty;
        $data->image_alt = $request->image_alt;
        $data->image_des = $request->image_des;
        $data->stock_status= $request->stock_status;

        // store ids
        if(!empty($validatedData['compatible_product_ids'])) {
            $data->compatible_product_ids = json_encode($validatedData['compatible_product_ids']);
        }

        $image = $request->file('image');

        $upload_path     = null;
        if($image) {

            $image_name= $image->getClientOriginalName();
            // $image_full_name = $image_name;
            $image_name = $image->getClientOriginalName();
            $image_name_array = explode('.',$image_name);
            $image_name_slug = $this->createUrlSlug($image_name_array[0]);
            $image_full_name = $image_name_slug.'.'.$image_name_array[1];

            $upload_path = 'images/product_image/';
            $image_url = $upload_path.$image_full_name;
            $success = $image->move($upload_path, $image_full_name);
            // $img = Image::make($image_url)->resize(255, 300)->save();
            if($success) {
                $data->image = $image_url;
            }

            // making 4 diffrent sizes of the featured image
            $product_image_path = base_path() . '/images/product_image/';
            $names = ['thumb-', 'small-', 'medium-', 'large-'];
            $sizes = [160, 285, 400, 1600];
            $table_fields = ['product_image_thumb', 'product_image_small', 'product_image_medium', 'product_image_large'];
            for($i = 0; $i < 4; $i++) {
                $image = Image::make($product_image_path . $image_full_name);
                $image->widen($sizes[$i]);
                $image->save($product_image_path . $names[$i] . $image_full_name);
                $field_name = $table_fields[$i];
                $data->$field_name = 'images/product_image/' . $names[$i].$image_full_name;
            }
        }

        $data->product_indexing = $request->product_indexing;
        $data->status = $request->status;
        $data->home_delivery = $request->home_delivery;
        $data->offer = $request->offer;
        $data->save();

        $brand_id= $request->brand_id;
        if ($brand_id) {
            foreach ($brand_id as $key => $value ){
                if(!empty($value)){
                    $product_brand= new ProductBrand();
                    $product_brand->product_id =$data->id;
                    $product_brand->brand_id=$value;
                    $product_brand->save(); 
                }
            }   
        }

        $shop_type_id= $request->shop_type_id;
        if ($shop_type_id) {
            foreach ($shop_type_id as $key => $value ){
                if(!empty($value)){
                    $product_brand= new ProductShop();
                    $product_brand->product_id =$data->id;
                    $product_brand->shop_type_id=$value;
                    $product_brand->save();
                }
            }   
        }

        $highlights= $request->highlights;
        if ($highlights) {
            foreach ($highlights as $key => $value ){
                if(!empty($value)){
                    $product_brand= new ProductHighlight();
                    $product_brand->product_id =$data->id;
                    $product_brand->highlights=$value;
                    $product_brand->save();
                }
            }   
        }

        $faqs = $request->faq;
        if ($faqs) {
            foreach ($faqs as $faq){
                if(!empty($faq['question'])){
                    $product_faq = new ProductFaq();
                    $product_faq->product_id = $data->id;
                    $product_faq->question = $faq['question'];
                    $product_faq->answer = $faq['answer'];
                    $product_faq->point = $faq['point'];
                    $product_faq->save();
                }
            }   
        }

  

        $emi_month= $request->emi_month;
        $emi_price= $request->emi_price;
        if ($emi_month) {
            foreach ($emi_month as $key => $value ){
                if(!empty($value)){
                    $product_brand= new ProductEmi();
                    $product_brand->product_id =$data->id;
                    $product_brand->emi_month=$value;
                    $product_brand->emi_price=$emi_price[$key];
                    $product_brand->save();
                }
            }   
        }

        $terms= $request->terms;
        if ($terms) {
            foreach ($terms as $key => $value ){
                if(!empty($value)){
                    $product_terms= new ProductTerms();
                    $product_terms->product_id =$data->id;
                    $product_terms->terms=$value;
                    $product_terms->save();
                }
            }   
        }

        $images = $request->file('product_image');
        $product_image_alt= $request->product_image_alt;
        $product_image_des= $request->product_image_des;

        if ($images) {
            foreach ($images as $key => $value ){
                $product_image = new ProductImage();
                $image_name=$value->getClientOriginalName();
                $image_full_name = $image_name;
                $upload_path = 'images/product_more_image/';
                $image_url = $upload_path.$image_full_name;
                $success = $value->move($upload_path, $image_full_name);
                // $img = Image::make($image_url)->resize(255, 300)->save();
                $product_image->product_id = $data->id;
                $product_image->product_image = $image_url;

                $product_image->product_image_alt=$product_image_alt[$key];
                $product_image->product_image_des=$product_image_des[$key];

                // create thumbnail of productimages
                $product_image_path = base_path() . '/images/product_more_image/';
                $image = Image::make($product_image_path . $image_full_name);
                $image->widen(160);
                $image_name = 'thumb_' . $image_full_name;
                $image->save($product_image_path . $image_name);
                $product_image->product_image_thumb = $upload_path . $image_name;
                // end create thumbnail

                $product_image->save();
            }
        }

        $free_item_image = $request->file('free_item_image');
        $free_item_title = $request->free_item_title;
        $free_item_alt = $request->free_item_alt;
        $free_item_des = $request->free_item_des;

        if (!empty($free_item_image)) {
            foreach ($free_item_image as $key => $value ){

                if(!empty($value)){
                    $free_item = new FreeItemForClient();
                    $image_name=str::random(5);
                    $ext = strtolower($value->getClientOriginalExtension());
                    $image_full_name = $image_name. '.' .$ext;
                    $upload_path = 'images/free_item_image/';
                    $image_url = $upload_path.$image_full_name;
                    $success = $value->move($upload_path, $image_full_name);

                    if($success)
                    {
                        $free_item->free_item_image = $image_url;
                    }


                    $free_item->product_id = $data->id;
                    $free_item->free_item_title = $free_item_title[$key];
                    $free_item->free_item_alt = $free_item_alt[$key];
                    $free_item->free_item_des = $free_item_des[$key];
                    
                    
                    // create thumbnail of productimages
                    $product_image_path = base_path() . '/images/free_item_image/';
                    $image = Image::make($product_image_path . $image_full_name);
                    $image->widen(160);
                    $image_name = $image_full_name;
                    $image->save($product_image_path . $image_name);
                    $free_item->free_item_thumb = $upload_path . $image_name;
                    // end create thumbnail
                    
                    
                    $free_item->save();
                }
            }
        }




        $notification=array(
            'message' => 'Successfully Done',
            'alert-type' => 'success'
        );
        //return Redirect::to('https://www.binarylogic.com.bd/admin/product');
        return redirect('admin/product')->with($notification);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        
        $user_id = Auth::user();
        if($user_id){
            
            $productSqc = array(1=>'new_arrival',2=>'hot_deal',3=>'feature',4=>'offer_product');

            $product = Product::find($id);
            $brands = Brand::where('status', 1)->get();
            $categories = Category::where('status', 1)->get();
            $sub_categories = Subcategory::where('status', 1)->get();
            $pro_sub_categories = Prosubcategory::where('status', 1)->get();
            $suppliers = Supplier::where('status', 1)->get();
    
            $productBrands = ProductBrand::where('product_id', $id)->get();
            $productImages = ProductImage::where('product_id', $id)->get();
            $productShops = ProductShop::where('product_id', $id)->get();
            $productEmis = ProductEmi::where('product_id', $id)->get();
            $ProductStockStatuses = ProductStockStatus::where('product_id', $id)->get();
            $productHighlights = ProductHighlight::where('product_id', $id)->get();
            $productFaqs = ProductFaq::where('product_id', $id)->get();
            $productSocial = ProductSocial::where('product_id', $id)->first();
            $freeitems = FreeItemForClient::where('product_id', $id)->get();

            $productTerms = ProductTerms::where('product_id', $id)->get();

            $shop_types = ShopType::where('status', 1)->get();
    
            $products = Product::get();
        
            $components = Component::where('status', 1)->get();
            $offertype = DB::table('offer_type')->orderBy('id','desc')->get();
            return view('backend.admin.product.edit', compact(
                'product', 'brands', 'categories', 'suppliers', 'sub_categories', 'pro_sub_categories', 
                'productBrands', 'shop_types', 'productImages', 'productShops', 'productEmis', 
                'components', 'products', 'ProductStockStatuses', 'productHighlights', 'productTerms',
                'freeitems','productFaqs','productSocial','productSqc','offertype'
            ));

        }else{
            return redirect('login');
        }        
        

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // DB::insert('insert into product_faqs (product_id, question, answer, point) values (?, ?, ?, ?)', [10, 'q1', 'q1 ans',5]);
        $validatedData = $request->validate([
            'name' => 'required',
            'slug' => 'required',
            'category_id' =>'required',
            'price_highlight' =>'required',
        ]);
        $user_id = Auth::user()->id;

        $data = Product::find($id);
        $data->name = $request->name;
        $data->subtitle = $request->subtitle;

        $data->slug = Str::slug($request->slug);        
        
        $data->user_id = $user_id;
        $data->supplier_id = $request->supplier_id;
        $data->category_id = $request->category_id;
        $data->sub_category_id = $request->sub_category_id;
        $data->pro_sub_category_id = $request->pro_sub_category_id;
        $data->pro_pro_category_id = $request->pro_pro_category_id;
        $data->component_id = $request->component_id;
        $data->compatible_product_ids = $request->compatible_product_ids;

        // set updater id & time
        if ($data->update_current_serial === 1) {
            $data->updated_by_1 = $user_id;
            $data->update_current_serial = 2;
        }else {
            $data->update_current_serial = 1;
            $data->updated_by_2 = $user_id;
        }


        $data->max_order_qty = $request->max_order_qty;
        $data->min_order_qty = $request->min_order_qty;


        $data->buying_price = $request->buying_price ?? '0';
        $data->regular_price = $request->regular_price;
        $data->special_price = $request->special_price;
        $data->offer_price = $request->offer_price;
        $data->discount_price = $request->discount_price;
        $data->discount = $request->discount;
        $data->video = $request->video;

        $data->price_highlight = $request->price_highlight;
        
        $data->qty = $request->qty;
        $data->total_sell = $request->total_sell;
        $data->total_product = $request->total_product;
        $data->barcode = $request->barcode;
        $data->description = $request->description;
        $data->section_1 = $request->section_1;
        $data->section_2 = $request->section_2;
        $data->section_3 = $request->section_3;
        $data->product_faq = $request->product_faq;
        $data->specification = $request->specification;
        $data->product_summery = $request->product_summery;
        $data->product_highlights = $request->product_highlights;
        
        if(!empty($request->note_link)){
            $data->note = $request->note.'----'.$request->note_link;
        }else{
            $data->note = $request->note;
        }

        $data->shop = $request->shop;

        //New Add Hobe
        $data->meta_title = $request->meta_title;
        $data->meta_keywords = $request->meta_keywords;
        $data->meta_des = $request->meta_des;
        $data->canonical_url = $request->canonical_url;
        $data->call_for_price = $request->call_for_price;
        $data->estimated_price = $request->estimated_price;
        $data->warranty = $request->warranty;
        $data->image_alt = $request->image_alt;
        $data->image_des = $request->image_des;
        
        if(!empty($request->stock_status)){
            $data->stock_status= $request->stock_status;
        }

        $image = $request->file('image');
        if($image)
        {
            $image_name= $image->getClientOriginalName();
            $image_full_name = $image_name;
            $upload_path = 'images/product_image/';
            $image_url = $upload_path.$image_full_name;
            $success = $image->move($upload_path, $image_full_name);
            // $img = Image::make($image_url)->resize(255, 300)->save();
            if($success)
            {
                $old_image = $request->old_image;
                if (file_exists($old_image)) {
                    try {
                        unlink($request->old_image);
                    } catch (\Throwable $th) {

                    }
                    
                }
                $data->image = $image_url;
            }

            // making 4 diffrent sizes of the featured image & delete old images
            $product_image_path = base_path() . '/images/product_image/';
            $names = ['thumb', 'small', 'medium', 'large'];
            $sizes = [160, 285, 400, 1600];
            $table_fields = ['product_image_thumb', 'product_image_small', 'product_image_medium', 'product_image_large'];
            for($i = 0; $i < 4; $i++) {
                // remove old images
                $field_name = $table_fields[$i];
                $image_path = base_path() . '/' .$data->$field_name;
                if (file_exists($image_path)) {
                   try {
                        unlink($image_path);
                    } catch (\Throwable $th) {

                    }
                }
                // create new different size of images
                $image = Image::make($product_image_path . $image_full_name);
                $image->widen($sizes[$i]);
                $image->save($product_image_path . $names[$i] . $image_full_name);
                $data->$field_name = 'images/product_image/' . $names[$i].$image_full_name;
            }
        }
        
        $data->desktop_offer = $request->desktop_offer;
        $data->offer_date = $request->offer_date;
        $data->product_indexing = $request->product_indexing;
        $data->status = $request->status;
        $data->home_delivery = $request->home_delivery;
        $data->offer = $request->offer;
        if ($request->product_seq == 1){
            $data->seq_product = 1;
    
        }
        elseif ($request->product_seq == 2){
            $data->seq_product = 2;
        }
        elseif ($request->product_seq == 3){
            $data->seq_product = 3;
        }
        elseif ($request->product_seq == 4){
            $data->seq_product = 4;
        }
        
        if($data->save()){
            $html = new Html();
            $html->generator('upcoming');
            $html->generator('latest');
            $html->generator('feature');
          }
        switch($request->product_seq){
            case 2:
                $last = HotDealProduct::orderBy('seq', 'desc')->count();
                $hotdeal = new HotDealProduct();
                $hotdeal->product_id = $id;
                $hotdeal->seq = $last + 1;
                $hotdeal->status = 1;
                $hotdeal->save();
                break;  
            case 3:
                $last = FeatureProduct::orderBy('seq', 'desc')->count();
                $feature = new FeatureProduct();
                $feature->product_id = $id;
                $feature->seq = $last + 1;
                $feature->status = 1;
                $feature->save();
                break;  
            case 1:
                $last = ProductRundown::orderBy('seq', 'desc')->count();
                $rundown = new ProductRundown();
                $rundown->product_id = $id;
                $rundown->seq = $last + 1;
                $rundown->rundowntype = 1;
                $rundown->save();
                break;  
            case 4:
                $last = DB::table('offer')->orderBy('seq', 'desc')->count();
                $offer = [
                    'product_id' => $id,
                    'seq' => $last + 1,
                    'status' => 1,
                ];
                DB::table('offer')->insert($offer);
                break;
        }

        $ProductBrands = ProductBrand::where('product_id', $id)->delete();
        $brand_id= $request->brand_id;
        if ($brand_id) {
            foreach ($brand_id as $key => $value ){
                if(!empty($value)){
                    $product_brand = new ProductBrand();
                    $product_brand->product_id =$data->id;
                    $product_brand->brand_id=$value;
                    $product_brand->save();
                }
            }   
        }

        $ProductShops = ProductShop::where('product_id', $id)->delete();
        $shop_type_id= $request->shop_type_id;
        if ($shop_type_id) {
            foreach ($shop_type_id as $key => $value ){
                if(!empty($value)){
                    $product_brand= new ProductShop();
                    $product_brand->product_id =$data->id;
                    $product_brand->shop_type_id=$value;
                    $product_brand->save();
                }
            }   
        }


        $ProductHighlights = ProductHighlight::where('product_id', $id)->delete();
        $highlights= $request->highlights;
        if ($highlights) {
            foreach ($highlights as $key => $value ){
                if(!empty($value)){
                    $product_brand= new ProductHighlight();
                    $product_brand->product_id =$data->id;
                    $product_brand->highlights=$value;
                    $product_brand->save();
                }
            }   
        }

        $ProductFaq = ProductFaq::where('product_id', $id)->delete();
        $faqs = $request->faq;
        if ($faqs) {
            // dd($faqs);
            foreach ($faqs as $faq){
                // dd($faq);
                if(!empty($faq['question'])){
                    $product_faq = new ProductFaq();
                    $product_faq->product_id = $id;
                    $product_faq->question = $faq['question'];
                    $product_faq->answer = $faq['answer'];
                    $product_faq->point = $faq['point'];
                    $product_faq->save();
                }
            }   
        }

        /*
        * share
        */
        // $productSocial = ProductSocial::where('product_id', $id)->delete();
        // dd($request);
        $productSocialData = ProductSocial::where('product_id', $id)->first();
        if(empty($productSocialData)){
            $product_social = new ProductSocial();
            $product_social->product_id = $id;
            $product_social->socialTitle = $request->socialTitle;
            $product_social->socialDescription = $request->socialDescription;
            $social_image = $request->file('socialImage');
            if(!empty($social_image)){
                $social_image_name= $social_image->getClientOriginalName();
                $social_image_name_array = explode('.',$social_image_name);
                $social_image_name_slug = $this->createUrlSlug($social_image_name_array[0]);
                $social_image_full_name = $social_image_name_slug.'.'.$social_image_name_array[1];
                $social_upload_path = 'images/social_image/';
                $social_image_url = $social_upload_path.$social_image_full_name;
                $social_image->move($social_upload_path, $social_image_full_name);
                $product_social->socialImage = $social_image_full_name;
            }
            $product_social->save();
        }else{
            $product_social = ProductSocial::find($productSocialData->id);
            $product_social->socialTitle = $request->socialTitle;
            $product_social->socialDescription = $request->socialDescription;
            $social_image = $request->file('socialImage');
            if(!empty($social_image)){
                $social_image_name= $social_image->getClientOriginalName();
                $social_image_name_array = explode('.',$social_image_name);
                $social_image_name_slug = $this->createUrlSlug($social_image_name_array[0]);
                $social_image_full_name = $social_image_name_slug.'.'.$social_image_name_array[1];
                $social_upload_path = 'images/social_image/';
                $social_image_url = $social_upload_path.$social_image_full_name;
                $social_image->move($social_upload_path, $social_image_full_name);
                $product_social->socialImage = $social_image_full_name;
            }
            $product_social->save();
        }
        /*
        * share
        */


        $ProductEmis = ProductEmi::where('product_id', $id)->delete();
        $emi_month= $request->emi_month;
        $emi_price= $request->emi_price;
        if ($emi_month) {
            foreach ($emi_month as $key => $value ){
                if(!empty($value)){
                    $product_brand= new ProductEmi();
                    $product_brand->product_id =$data->id;
                    $product_brand->emi_month=$value;
                    $product_brand->emi_price=$emi_price[$key];
                    $product_brand->save();
                }
            }   
        }

       ProductTerms::where('product_id', $id)->delete();
        $terms= $request->terms;
        if ($terms) {
            foreach ($terms as $key => $value ){
                if(!empty($value)){
                    $product_terms= new ProductTerms();
                    $product_terms->product_id =$data->id;
                    $product_terms->terms=$value;
                    $product_terms->save();
                }
            }   
        }



            $images = $request->product_image;
            $old_image_id = $request->old_image_id;
            $product_image_alt = $request->product_image_alt;
            
            $product_image_status = $request->product_image_status;
            if(!empty($product_image_status)){
                foreach ($product_image_status as $key => $value ){
                if($value != 1){
                    $p_i = ProductImage::where('id', $request->old_image_id[$key])->delete();
                    unset($product_image_alt[$key]);
                    unset($old_image_id[$key]);
                }
                }
            }
            
            
            if ($product_image_alt) {
                foreach ($product_image_alt as $key => $value ){
                    if($value || $request->product_image_alt[$key] || $request->product_image_des[$key] || $images){
                        if($old_image_id){
                            if(array_key_exists($key,$old_image_id)){
                                $image_id = $old_image_id[$key];
                                    if($image_id){
                                          $product_image = ProductImage::find($image_id);
                                    }else{
                                        $product_image =new ProductImage();
                                    }
                                }else{
                                    $product_image =new ProductImage();
                                }
                            }else{
                            $product_image =new ProductImage();
                        }
                        
                        if($images){
                            if(array_key_exists($key,$images)){
                                $image_file = $images[$key];
                                $image_name = $image_file->getClientOriginalName();
                                $image_full_name = $image_name;
                                $upload_path = 'images/product_more_image/';
                                $image_url = $upload_path.$image_full_name;
                                $success = $image_file->move($upload_path, $image_full_name);
                              
                                if($success)
                                {
                                    $product_image->product_image = $image_url;
                                    
                                    $product_image_path = base_path() . '/images/product_more_image/';
                                    $image = Image::make($product_image_path . $image_full_name);
                                    $image->widen(160);
                                    $image_name = 'thumb_' . $image_full_name;
                                    $image->save($product_image_path . $image_name);
                                    $product_image->product_image_thumb = $upload_path . $image_name;
                                    // end create thumbnail
                                }
                            }
                        }
            
                        $product_image->product_id = $data->id;
                        $product_image->product_image_alt=$request->product_image_alt[$key];
                        $product_image->product_image_des=$request->product_image_des[$key];
                        $product_image->save();
                        
                    }
                    
                    
                }
            }
           
        // }



        // if ($request->free_item_image) {

        //     $FreeItemForClients = FreeItemForClient::select('free_item_image')->where('product_id', $id)->get();
        //     foreach ($FreeItemForClients as $key => $value) {
        //         $image_absolute_path = base_path() . '/' . $value->free_item_image;
        //         if (file_exists($image_absolute_path)) {
        //             try {
        //                 unlink($image_absolute_path);
        //             } catch (\Throwable $th) {
                    
        //             }
                    
                    
        //         }
        //     }


            $free_item_images = $request->free_item_image;

            $free_item_title = $request->free_item_title;
            $free_item_alt = $request->free_item_alt;
            $free_item_des = $request->free_item_des;
            $old_free_image_id = $request->old_free_image_id;



            if($free_item_title){
                    foreach ($free_item_title as $key =>$value ){
                        
                    if($old_free_image_id){
                         if(array_key_exists($key,$old_free_image_id)){
                        
                                $image_id = $old_free_image_id[$key];
                                
                                if($image_id){
                                      $product_image = FreeItemForClient::find($image_id);
                                }else{
                                    $product_image =new FreeItemForClient();
                                }
                            }else{
                            $product_image =new FreeItemForClient();
                        }
                    }else{
                        $product_image =new FreeItemForClient();
                    }
                    
                    if($free_item_images){
                        if(array_key_exists($key,$free_item_images)){
                            $image_file = $free_item_images[$key];

                            $image_name = str::random(5);
                            $ext = strtolower($image_file->getClientOriginalExtension());
                            $image_full_name = $image_name. '.' .$ext;
                            
                            $upload_path = 'images/free_item_image/';
                            $image_url = $upload_path.$image_full_name;
                            $success = $image_file->move($upload_path, $image_full_name);

                            if($success)
                            {
                                $product_image->free_item_image = $image_url;
                            }
                        }
                    }
                        
                        
                    $product_image->product_id = $data->id;
                    $product_image->free_item_title = $free_item_title[$key];
                    $product_image->free_item_alt = $free_item_alt[$key];
                    $product_image->free_item_des = $free_item_des[$key];
                    

                    // create thumbnail of productimages
                    
                    // if($free_item_images){
                    // $product_image_path = base_path() . '/images/free_item_image/';
                    // $image = Image::make($product_image_path . $image_full_name);
                    // $image->widen(160);
                    // $image_name = 'thumb_' . $image_full_name;
                    // $image->save($product_image_path . $image_name);
                    // $product_image->free_item_thumb = $upload_path . $image_name;
                    // }
                    
                    // end create thumbnail                    
                    
                    
                    $product_image->save();
                 }
            }
        // }




        $notification=array(
            'message' => 'Successfully Done',
            'alert-type' => 'success'
        );

        // return redirect('admin/product')->with($notification);
        return redirect()->back()->with($notification);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $imagePath = Product::select('image')->where('id', $id)->first();
        $filePath = $imagePath->image;
        if (file_exists($filePath)) {
            try {
                unlink($filePath);
            } catch (\Throwable $th) {
            
            }
            
            
            Product::where('id', $id)->delete();
        }else{
            Product::where('id', $id)->delete();
        }


        $ProductImages = ProductImage::select('product_image')->where('product_id', $id)->get();
        foreach ($ProductImages as $key => $value) {
            try {
                unlink($value->product_image);
            } catch (\Throwable $th) {
            
            }
            
        }

        // remove old thumbnail
        foreach ($ProductImages as $key => $value) {
            $image_absolute_path = base_path() . '/' . $value->product_image_thumb;
            if (file_exists($image_absolute_path)) {
                try {
                    unlink($image_absolute_path);
                } catch (\Throwable $th) {
                    //throw $th;
                }
                
            }
        } 

        $ProductBrand = ProductBrand::where('product_id', $id)->delete();
        $ProductShops = ProductShop::where('product_id', $id)->delete(); 
        $ProductTerms = ProductTerms::where('product_id', $id)->delete(); 
        $ProductHighlight = ProductHighlight::where('product_id', $id)->delete(); 
        $ProductStockStatus = ProductStockStatus::where('product_id', $id)->delete(); 
        
        $notification=array(
            'message' => 'Product Deleted Successfully !!',
            'alert-type' => 'error'
        );
        return redirect()->back()->with($notification);
    }

    public function import()
    {
        
        $user_id = Auth::user();
        if($user_id){
            return view('backend.admin.product.import_csv');
        }else{
            return redirect('login');
        }        
        
        
    }

    public function imports()
    {
        
        $user_id = Auth::user();
        // dd($user_id);
        if($user_id){
            return view('backend.admin.product.imports');
        }else{
            return redirect('login');
        }        
        
        
    }

    public function __construct(Excel $excel)
    {
        $this->excel = $excel;
    }

    public function export()
    {
        return $this->excel->download(new ProductExport, 'products.csv');
    }

    public function importFile(Request $request)
    {
        if ($request->hasFile('file')) {

            $file = request()->file('file')->store('import');

            $import = new ProductImport;
            $import->import($file);


            // Excel::import(new ProductImport, $file);

            session()->flash('notif', "Imported Done ...");
            return redirect()->back();



        }
    }
    
    public function remove_component()
    {
        Product::where('component_id', 3)->update(['component_id' => Null]);
    }    
    
    
}
