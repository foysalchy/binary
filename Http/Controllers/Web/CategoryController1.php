<?php

namespace App\Http\Controllers\Web;

use \Cache;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

use App\Models\Category;
use App\Models\CategoryUpdateHistory;
use App\Models\Product;
use App\Models\ProductStockStatus;
use App\Models\Subcategory;

use Str;
use Intervention\Image\Facades\Image;
use Auth;

class CategoryController extends Controller
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
            $categories = Category::get();
            return view('backend.admin.category.index', compact('categories'));
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
            return view('backend.admin.category.create');
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
            'name' => 'required',
            'slug' => 'required|unique:categories',
            'image' => 'required',
            'status' => 'required',
        ]);

        $user_id = Auth::user()->id;
        

        $data = new Category();
        $data->user_id = $user_id;
        $data->name = $request->name;
        $data->slug = Str::slug($request->slug);
        $data->meta_title = $request->meta_title;
        $data->meta_des = $request->meta_des;
        $data->meta_keywords = $request->meta_keywords;
        $data->bg_color = $request->bg_color;
        $image = $request->file('image');
        if($image)
        {
            $image_name= $image->getClientOriginalName();
            $image_full_name = $image_name;
            $upload_path = 'images/category_image/';
            $image_url = $upload_path.$image_full_name;
            $success = $image->move($upload_path, $image_full_name);
            // $img = Image::make($image_url)->resize(400, 200)->save();
            if($success)
            {
                $data->image = $image_url;
            }
        }
        
        $banner_ad = $request->file('banner_ad');
        if($banner_ad)
        {
            $image_name= $banner_ad->getClientOriginalName();
            $image_full_name = $image_name;
            $upload_path = 'images/category_image/';
            $image_url = $upload_path.$image_full_name;
            $success = $banner_ad->move($upload_path, $image_full_name);
            // $img = Image::make($image_url)->resize(400, 200)->save();
            if($success)
            {
                $data->banner_ad = $image_url;
            }
        }        
        
        $data->status = $request->status;
        $data->banner_ad_url = $request->banner_ad_url;
        $data->banner_ad_status = $request->banner_ad_status;
        $data->save();
        $notification=array(
            'message' => 'Successfully Done',
            'alert-type' => 'success'
        );

        Cache::put('category_'.$data->id, json_encode($data), $seconds = 10000000000);
        
        $categories = Category::select(['id','name','slug'])->where('status',1)->orderBy('position_id', 'asc')->get();
        Cache::put('categories', json_encode($categories), $seconds = 10000000000);
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
            $category = Category::findorfail($id);
            $products = Product::where('category_id', $id)->orderBy('id', 'desc')->where('status', 1)->take(4)->get();
            $ProductStockStatuses = ProductStockStatus::where('product_id', $id)->get();

            $subcategories = Subcategory::where('category_id', $id)->take(4)->where('status', 1)->get();
            return view('backend.admin.category.edit', compact('category', 'products', 'ProductStockStatuses', 'subcategories'));
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
        
        $data = Category::find($id);
        $data->name = $request->name;
        $data->slug = Str::slug($request->slug);
        $data->status = $request->status;
        $data->banner_ad_url = $request->banner_ad_url;
        $data->banner_ad_status = $request->banner_ad_status;
        $data->meta_title = $request->meta_title;
        $data->meta_des = $request->meta_des;
        $data->bg_color = $request->bg_color;
        $data->cat_title_color = $request->cat_title_color;
        $data->meta_keywords = $request->meta_keywords;
        $image = $request->file('image');
        if($image)
        {
            $image_name= $image->getClientOriginalName();
            $image_full_name = $image_name;
            $upload_path = 'images/category_image/';
            $image_url = $upload_path.$image_full_name;
            $success = $image->move($upload_path, $image_full_name);
            // $img = Image::make($image_url)->resize(400, 200)->save();
            if($success)
            {   
                $old_image = $request->old_image;
                if (file_exists($old_image)) {
                    unlink($request->old_image);
                }
                $data->image = $image_url;
            }
        }
        
        
        $banner_ad = $request->file('banner_ad');
        if($banner_ad)
        {
            $image_name= $banner_ad->getClientOriginalName();
            $image_full_name = $image_name;
            $upload_path = 'images/category_image/';
            $image_url = $upload_path.$image_full_name;
            $success = $banner_ad->move($upload_path, $image_full_name);
            // $img = Image::make($image_url)->resize(400, 200)->save();
            if($success)
            {
                
                $old_banner_ad = $request->old_banner_ad;
                if (file_exists($old_banner_ad)) {
                    unlink($request->old_banner_ad);
                }                
                
                $data->banner_ad = $image_url;
            }
        }         
        
        $data->save();
        
        
        $category_update = new CategoryUpdateHistory();
        $category_update->user_id = $user_id;
        $category_update->category_id = $id;
        $category_update->save();

        Cache::put('category_'.$id, json_encode($category_update), $seconds = 10000000000);
        
        $categories = Category::select(['id','name','slug'])->where('status',1)->orderBy('position_id', 'asc')->get();
        Cache::put('categories', json_encode($categories), $seconds = 10000000000);



/*$value = Cache::get('category_'.$id);
        if(!empty($value)){
            
        }
        $contents = file_get_contents('category_'.$id.'.txt');
        $new_contents = json_encode($data);
        file_put_contents('category_'.$id.'.txt', $new_contents);
*/
        //Storage::disk('local')->put('category_'.$id.'.txt', json_encode($data));
        //$categories = json_encode(Category::where('status',1)->get());
        //Storage::disk('local')->put('category.txt', json_encode($categories));
        $notification=array(
                 'message' => 'Successfully Done',
                 'alert-type' => 'success'
             );
        return redirect()->to('admin/category');        
        
        // $notification=array(
        //     'message' => 'Successfully Done',
        //     'alert-type' => 'success'
        // );
        // return redirect()->back()->with($notification);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $imagePath = Category::select('image')->where('id', $id)->first();
        $filePath = $imagePath->image;
        if (file_exists($filePath)) {
            unlink($filePath);
            Category::where('id', $id)->delete();
        }else{
            Category::where('id', $id)->delete();
        }
        
        $notification=array(
            'message' => 'Category Deleted Successfully !!',
            'alert-type' => 'error'
        );
        
        return redirect()->back()->with($notification);
    }
    
    public function view_update_history($id){
        
        
        $user_id = Auth::user();
        if($user_id){
            $category = Category::where('id', $id)->first();
            $histories = CategoryUpdateHistory::orderBy('id', 'desc')->where('category_id', $id)->get();
            return view('backend.admin.category.view_update_history', compact('histories', 'category'));
        }else{
            return redirect('login');
        }        
                

        
    } 
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
}
