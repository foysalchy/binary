<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Brand;
use Str;
use Intervention\Image\Facades\Image;
use Auth;
use App\Models\Html;

class BrandController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        
        $user_id = Auth::user();
        if($user_id){
            $brands = Brand::get();
            return view('backend.admin.brand.index', compact('brands'));
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
            return view('backend.admin.brand.create');
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
            'name' => 'required|unique:brands',
            'slug' => 'required|unique:brands',
            'image' => 'required',
            'status' => 'required',
        ]);


        $data = new Brand();
        $data->name = $request->name;
        $data->slug = Str::slug($request->slug);
        
        $data->meta_title = $request->meta_title;
        $data->meta_des = $request->meta_des;
        $data->meta_keywords = $request->meta_keywords;        
        
        $image = $request->file('image');
        if($image)
        {
            $image_name= $image->getClientOriginalName();
            $image_full_name = $image_name;
            $upload_path = 'images/brand_image/';
            $image_url = $upload_path.$image_full_name;
            $success = $image->move($upload_path, $image_full_name);
            // $img = Image::make($image_url)->resize(155, 55)->save();
            if($success)
            {
                $data->image = $image_url;
            }
        }
        

        $meta_image = $request->file('meta_image');
        if($meta_image)
        {
            $image_name= $meta_image->getClientOriginalName();
            $image_full_name = $image_name;
            $upload_path = 'images/brand_image/';
            $image_url = $upload_path.$image_full_name;
            $success = $meta_image->move($upload_path, $image_full_name);
            // $img = Image::make($image_url)->resize(155, 55)->save();
            if($success)
            {
                $data->meta_image = $image_url;
            }
        }        
        
        
        $data->status = $request->status;
        if( $data->save()){
            //update html
            $html = new Html();
            $html->generator('brand');
        }
        $notification=array(
            'message' => 'Successfully Done',
            'alert-type' => 'success'
        );
        return redirect()->back()->with($notification);
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
            $brand = Brand::findorfail($id);
            return view('backend.admin.brand.edit', compact('brand'));
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
        $data = Brand::find($id);
        $data->name = $request->name;
        $data->slug = Str::slug($request->slug);

        
        $data->meta_title = $request->meta_title;
        $data->meta_des = $request->meta_des;
        $data->meta_keywords = $request->meta_keywords;
        $data->description = $request->description;
        $data->description_two = $request->description_two; 
        
        $data->status = $request->status;
        $image = $request->file('image');
        if($image)
        {
            $image_name= $image->getClientOriginalName();
            $upload_path = 'images/brand_image/';
            $image_url = $upload_path.$image_name;

            $success = $image->move($upload_path, $image_name);
            if($success)
            {   
                $old_image = $request->old_image;
                // if (file_exists($old_image)) {
                //     unlink($request->old_image);
                // }
                $data->image = $image_url;
            }

            $product_image_path = base_path() . '/images/brand_image/';
            // dd($product_image_path);
            $names = ['small','medium'];
            $sizes = [120,400];
            $table_fields = [ 'image','brand_small_image'];
            for($i = 0; $i < 2; $i++) {
                $field_name = $table_fields[$i];
                // create new different size of images           
                $image = Image::make($product_image_path . $image_name);
                //dd($image);
                $image->widen($sizes[$i]);
                $image->save($product_image_path . $names[$i] . $image_name);
                $data->$field_name = 'images/brand_image/' . $names[$i].$image_name;
           
            }
        }
        
        $meta_image = $request->file('meta_image');
        if($meta_image)
        {
            $image_name= $meta_image->getClientOriginalName();
            $image_full_name = $image_name;
            $upload_path = 'images/brand_image/';
            $image_url = $upload_path.$image_full_name;
            $success = $meta_image->move($upload_path, $image_full_name);
            // $img = Image::make($image_url)->resize(155, 55)->save();
            if($success)
            {
                
                $old_meta_image = $request->old_meta_image;
                if (file_exists($old_meta_image)) {
                    unlink($request->old_meta_image);
                }                
                
                $data->meta_image = $image_url;
            }
        }         
        
        if( $data->save()){
            //update html
            $html = new Html();
            $html->generator('brand');
        }
        $notification=array(
            'message' => 'Successfully Done',
            'alert-type' => 'success'
        );
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
        $imagePath = Brand::select('image')->where('id', $id)->first();
        $filePath = $imagePath->image;
        if (file_exists($filePath)) {
            unlink($filePath);
        $brands =  Brand::where('id', $id)->delete();
          if($brands){
            //update html
            $html = new Html();
            $html->generator('brand');
         }
        }
        else{
        $brands =  Brand::where('id', $id)->delete();
          if($brands){
            //update html
            $html = new Html();
            $html->generator('brand');
            }
        }
        
        $notification=array(
            'message' => 'Brand Deleted Successfully !!',
            'alert-type' => 'error'
        );
        
        return redirect()->back()->with($notification);
    }
}
