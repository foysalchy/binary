<?php

namespace App\Http\Controllers\Web;
use \Cache;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Banner;
use Str;
use Intervention\Image\Facades\Image;
use Auth;
use App\Models\Html;

class BannerController extends Controller
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
            $banners = Banner::orderBy('sequence','ASC')
            ->orderBy('id','DESC')->get();
            return view('backend.admin.banner.index', compact('banners'));
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
            return view('backend.admin.banner.create');
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

        //validate to foysal
        $validatedData = $request->validate([
            'image' => 'required',
            'status' => 'required',

            'title'=>'string',
            'url'=>'string',
            'description'=>'string', 
        ]);
                   
                


        $data = new Banner();
        $data->title = $request->title;
        $data->url = $request->url;
        $data->slug = str::slug($request->title);
        $data->description = $request->description;

        $image = $request->file('image');
        if($image)
        {
            $image_name= $image->getClientOriginalName();
            $image_full_name = $image_name;
            $upload_path = 'images/banner_image/';
            $image_url = $upload_path.$image_full_name;
            $success = $image->move($upload_path, $image_full_name);
            // $img = Image::make($image_url)->resize(840, 360)->save();
            $sizes = [200, 480, 900, 1600];
            $size_name = ['t', 's', 'm', 'l'];
            for($i = 0; $i < 4; $i++) {
                $image = Image::make($upload_path. $image_full_name);
                $image->widen($sizes[$i]);
                $image->save($upload_path .$size_name[$i].'/'. $image_full_name);
            }
            if($success)
            {
                $data->image = $image_url;
            }
        }
        if(!empty($request->sequence)){
            $data->sequence = $request->sequence;
        }else{
            $data->sequence = 20; 
        }
        $data->status = $request->status;
        if($data->save()){
            //update html
            $html = new Html();
            $html->generator('slider');
        }
        $notification=array(
            'message' => 'Successfully Done',
            'alert-type' => 'success'
        );

        $banners = Banner::where('status',1)->orderBy('id', 'DESC')->take(5)->get();
        Cache::put('banners', json_encode($banners), $seconds = 10000000000);

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
            $banner = Banner::findorfail($id);
            return view('backend.admin.banner.edit', compact('banner'));
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
         //validate to foysal
         $validatedData = $request->validate([
            'title'=>'string',
            'url'=>'string',
            'description'=>'string', 
        ]);
              
        $data = Banner::find($id);
      //  dd($data);
        $data->title = $request->title;
        $data->url = $request->url;
        $data->slug = str::slug($request->title);
        $data->description = $request->description;
        $image = $request->file('image');
        if($image)
        {
            $image_name= $image->getClientOriginalName();
            $image_full_name = $image_name;
            $upload_path = 'images/banner_image/';
            $image_url = $upload_path.$image_full_name;
            $success = $image->move($upload_path, $image_full_name);
            // $img = Image::make($image_url)->resize(840, 360)->save();
            $sizes = [200, 480, 900, 1600];
            $size_name = ['t', 's', 'm', 'l'];
            for($i = 0; $i < 4; $i++) {
                $image = Image::make($upload_path. $image_full_name);
                $image->widen($sizes[$i]);
                $image->save($upload_path .$size_name[$i].'/'. $image_full_name);
            }
            
            if($success)
            {
                $old_image = $request->old_image;
                if (file_exists($old_image)) {
                    unlink($request->old_image);
                }
                $data->image = $image_url;
            }
        }
        $data->status = $request->status;
        if(!empty($request->sequence)){
            $data->sequence = $request->sequence;
        }else{
            $data->sequence = 20; 
        }
        if($data->save()){
            //update html
            $html = new Html();
            $html->generator('slider');
        }
        $notification=array(
            'message' => 'Successfully Done',
            'alert-type' => 'success'
        );
        $banners = Banner::where('status',1)->orderBy('id', 'DESC')->take(5)->get();
        Cache::put('banners', json_encode($banners), $seconds = 10000000000);
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
        $imagePath = Banner::select('image')->where('id', $id)->first();
        $filePath = $imagePath->image;
        if (file_exists($filePath)) {
            unlink($filePath);
           $banner = Banner::where('id', $id)->delete();
           if($banner){
            //update html
            $html = new Html();
            $html->generator('slider');
        }
        }else{
           $banner = Banner::where('id', $id)->delete();
           if($banner){
            //update html
            $html = new Html();
            $html->generator('slider');
        }
        }
        
        $notification=array(
            'message' => 'Banner Deleted Successfully !!',
            'alert-type' => 'error'
        );
        
        return redirect()->back()->with($notification);
    }
}
