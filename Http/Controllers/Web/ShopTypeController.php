<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ShopType;
use App\Models\Html;
use Str;
use Auth;
use Intervention\Image\Facades\Image;

class ShopTypeController extends Controller
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
            $shop_types = ShopType::get();
            return view('backend.admin.shoptype.index', compact('shop_types'));
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
            return view('backend.admin.shoptype.create');
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
            'title' => 'required|unique:shop_types',
            'short_description' => 'required',
            'image' => 'required',
            'status' => 'required',
            'slug' => 'required'
        ]);


        $data = new ShopType();
        $data->title = $request->title;
        $data->short_description = $request->short_description;
        $data->slug = $request->slug;
        
        $image = $request->file('image');
        
        if($image)
        {
            $image_name= $image->getClientOriginalName();
            $image_full_name = $image_name;
            $upload_path = 'images/shop_type_image/';
            $image_url = $upload_path.$image_full_name;
            $success = $image->move($upload_path, $image_full_name);
            // $img = Image::make($image_url)->resize(250, 260)->save();
            if($success)
            {
                $data->image = $image_url;
            }
            $product_image_path = base_path() . '/images/shop_type_image/';
            $image = Image::make($product_image_path . $image_full_name);
            $image->widen(200);
            $image_name = 'thumb_' . $image_full_name;
            $image->save($product_image_path . $image_name);
        }

        $data->status = $request->status;
        if($data->save()){
            //update html
            $html = new Html();
            $html->generator('category');
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
            $shop = ShopType::findorfail($id);
            return view('backend.admin.shoptype.edit', compact('shop'));
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

        $data = ShopType::find($id);
        $data->title = $request->title;
        $data->slug = $request->slug;
        $data->short_description = $request->short_description;
        $image = $request->file('image');
        if($image)
        {
            $image_name= $image->getClientOriginalName();
            $image_full_name = $image_name;
            $upload_path = 'images/shop_type_image/';
            $image_url = $upload_path.$image_full_name;
            $success = $image->move($upload_path, $image_full_name);
            // $img = Image::make($image_url)->resize(250, 260)->save();
            // if($success)
            // {
            //     $old_image = $request->old_image;
            //     if (file_exists($old_image)) {
            //         unlink($request->old_image);
            //     }
            //     $data->image = $image_url;
            // }
            $product_image_path = base_path() . '/images/shop_type_image/';
            $image = Image::make($product_image_path . $image_full_name);
            $image->widen(200);
            $image_name = 'thumb-' . $image_full_name;
            $image->save($product_image_path . $image_name);
            $data->image = $image_url;
        }

        $data->show_on_top = $request->show_on_top;
        $data->status = $request->status;
        if($data->save()){
            //update html
            $html = new Html();
            $html->generator('category');
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
        $imagePath = ShopType::select('image')->where('id', $id)->first();
        $filePath = $imagePath->image;
        if (file_exists($filePath)) {
            unlink($filePath);
         $delete = ShopType::where('id', $id)->delete();
         if($delete){
            //update html
            $html = new Html();
            $html->generator('category');
        }
        }else{
         $delete = ShopType::where('id', $id)->delete();
         if($delete){
            //update html
            $html = new Html();
            $html->generator('category');
        }
        }
        
        $notification=array(
            'message' => 'ShopType Deleted Successfully !!',
            'alert-type' => 'error'
        );
        
        return redirect()->back()->with($notification);
    }
}
