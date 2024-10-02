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
use App\Models\ProductTerms;
use App\Models\FreeItemForClient;
use Redirect;


use Str;
use Auth;

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

    public function index()
    {
        
        $user_id = Auth::user();
        if($user_id){
            $products = Product::orderBy('id', 'desc')->get();
            
            return view('backend.admin.product.index', compact('products'));
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
        $validatedData = $request->validate([
            'name' => 'required|unique:products',
            'slug' => 'unique:products',
            'category_id' => 'required',
            'image' => 'required',
            'compatible_product_ids.*' => 'nullable|integer',
            'price_highlight' => 'required',
            'status' => 'required',
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
        
        $data->price_highlight = $request->price_highlight;
        
        $data->qty = $request->qty;
        $data->total_sell = $request->total_sell;
        $data->total_product = $request->total_product;
        $data->barcode = $request->barcode;
        $data->description = $request->description;
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
        $data->call_for_price = $request->call_for_price;
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
            $image_full_name = $image_name;
            $upload_path = 'images/product_image/';
            $image_url = $upload_path.$image_full_name;
            $success = $image->move($upload_path, $image_full_name);
            // $img = Image::make($image_url)->resize(255, 300)->save();
            if($success) {
                $data->image = $image_url;
            }

            // making 4 diffrent sizes of the featured image
            $product_image_path = base_path() . '/images/product_image/';
            $names = ['thumb', 'small', 'medium', 'large'];
            $sizes = [160, 480, 800, 1600];
            $table_fields = ['product_image_thumb', 'product_image_small', 'product_image_medium', 'product_image_large'];
            for($i = 0; $i < 4; $i++) {
                $image = Image::make($product_image_path . $image_full_name);
                $image->widen($sizes[$i]);
                $image->save($product_image_path . $names[$i] . $image_full_name);
                $field_name = $table_fields[$i];
                $data->$field_name = 'images/product_image/' . $names[$i].$image_full_name;
            }
        }





        $data->status = $request->status;
        $data->home_delivery = $request->home_delivery;
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

        if ($free_item_image) {
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
                    $image_name = 'thumb_' . $image_full_name;
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
        return Redirect::to('https://www.binarylogic.com.bd/admin/product');
        //return redirect('admin/product')->back()->with($notification);
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
            $freeitems = FreeItemForClient::where('product_id', $id)->get();

            $productTerms = ProductTerms::where('product_id', $id)->get();

            $shop_types = ShopType::where('status', 1)->get();
    
            $products = Product::where('component_id',5)->get();
        
            $components = Component::where('status', 1)->get();
            return view('backend.admin.product.edit', compact('product', 'brands', 'categories', 'suppliers', 'sub_categories', 'pro_sub_categories', 'productBrands', 'shop_types', 'productImages', 'productShops', 'productEmis', 'components', 'products', 'ProductStockStatuses', 'productHighlights', 'productTerms', 'freeitems'));

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
        $data->compatible_product_ids = json_encode($request->compatible_product_ids);
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
        
        $data->price_highlight = $request->price_highlight;
        
        $data->qty = $request->qty;
        $data->total_sell = $request->total_sell;
        $data->total_product = $request->total_product;
        $data->barcode = $request->barcode;
        $data->description = $request->description;
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
        $data->call_for_price = $request->call_for_price;
        $data->warranty = $request->warranty;
        $data->image_alt = $request->image_alt;
        $data->image_des = $request->image_des;
        $data->stock_status= $request->stock_status;

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
            $sizes = [160, 480, 800, 1600];
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
        $data->status = $request->status;
        $data->home_delivery = $request->home_delivery;
        $data->save();

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

      
        
       

        // if ($request->product_image) {
            // $ProductImages = ProductImage::select('product_image')->where('product_id', $id)->get();
            // foreach ($ProductImages as $key => $value) {
            //     $image_absolute_path = base_path() . '/' . $value->product_image;
            //     if (file_exists($image_absolute_path)) {
            //         try {
            //             unlink($image_absolute_path);
            //         } catch (\Throwable $th) {
                    
            //         }
                    
                    
            //     }
            // }

            // remove old thumbnail
            
            // foreach ($ProductImages as $key => $value) {
            //     $image_absolute_path = base_path() . '/' . $value->product_image_thumb;
            //     if (file_exists($image_absolute_path)) {
            //         try {
            //             unlink($image_absolute_path);
            //         } catch (\Throwable $th) {

            //         }
            //     }
            // }


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

        return redirect('admin/product')->with($notification);
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
