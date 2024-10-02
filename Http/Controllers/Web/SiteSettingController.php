<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SiteSetting;
use Str;
use Intervention\Image\Facades\Image;
use Auth;
use App\Models\Html;

class SiteSettingController extends Controller
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
            $sitesettings = SiteSetting::get();
            return view('backend.admin.sitesetting.index', compact('sitesettings'));
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
            return view('backend.admin.sitesetting.create');
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
        //foysal
        $validatedData = $request->validate([
            'name' => 'string|required|unique:site_settings',
            'address' => 'string|required',
             'phone' => 'string',
            'email' => 'string|required',
            'templete' => 'required',
            'status' => 'required',

            'meta_title' => 'string',
            'meta_des' => 'string',
            'meta_keywords' => 'string',
            'meta_title' => 'string',
        ]);


        $data = new SiteSetting();
        $data->name = $request->name;
        
        $data->meta_title = $request->meta_title;
        $data->meta_des = $request->meta_des;
        $data->meta_keywords = $request->meta_keywords;        
        $data->canonical = $request->canonical;        
        $data->meta_robots = $request->meta_robots;
        
        $data->address = $request->address;
        $data->phone = $request->phone;
        $data->email = $request->email;
        $data->facebook = $request->facebook;
        $data->twitter = $request->twitter;
        $data->linkedin = $request->linkedin;
        $data->youtube = $request->youtube;
        $data->intagram = $request->intagram;
        $data->robots_txt = $request->robots_txt;
        $data->templete = $request->templete;

        $image = $request->file('image');
        if($image)
        {
            $image_name= $image->getClientOriginalName();
            $image_full_name = $image_name;
            $upload_path = 'images/site_image/';
            $image_url = $upload_path.$image_full_name;
            $success = $image->move($upload_path, $image_full_name);
            if($success)
            {
                $data->image = $image_url;
            }
        }
        $data->status = $request->status;
        if( $data->save()){
            //update html
            $html = new Html();
            $html->generator('site_setting');
            $html->generator('phone_number');
            $html->generator('footer_contact');
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
            $sitesetting = SiteSetting::findorfail($id);
            return view('backend.admin.sitesetting.edit', compact('sitesetting'));
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
        
        $validatedData = $request->validate([

            'meta_des' => 'min:50 | max:160',

        ]);
        
        //foysal
        $validatedData = $request->validate([
            'name' => 'string',
            'address' => 'string|required',
             'phone' => 'string',
            'email' => 'string',
          
            

            'meta_title' => 'string',
            'meta_des' => 'string',
            'meta_keywords' => 'string',
            'meta_title' => 'string',
        ]);
        
        $data = SiteSetting::find($id);
        $data->name = $request->name;
        
        
        $data->meta_title = $request->meta_title;
        $data->meta_des = $request->meta_des;
        $data->meta_keywords = $request->meta_keywords;        
        $data->canonical = $request->canonical;        
        $data->meta_robots = $request->meta_robots;        
        
        $data->address = $request->address;
        $data->phone = $request->phone;
        $data->email = $request->email;
        $data->facebook = $request->facebook;
        $data->twitter = $request->twitter;
        $data->linkedin = $request->linkedin;
        $data->youtube = $request->youtube;
        $data->intagram = $request->intagram;
        $data->robots_txt = $request->robots_txt;
        $data->category_summary = $request->category_summary;
        $data->templete = $request->templete;
        
        $image = $request->file('image');
        if($image)
        {
            $image_name= $image->getClientOriginalName();
            $image_full_name = $image_name;
            $upload_path = 'images/site_image/';
            $image_url = $upload_path.$image_full_name;
            $success = $image->move($upload_path, $image_full_name);
            if($success)
            {
                $old_image = $request->old_image;
                if (file_exists($old_image)) {
                    unlink($request->old_image);
                }
                $data->image = $image_url;
            }
        }
        
        $meta_image = $request->file('meta_image');
        if($meta_image)
        {
            $image_name= $meta_image->getClientOriginalName();
            $image_full_name = $image_name;
            $upload_path = 'images/site_image/';
            $image_url_meta = $upload_path.$image_full_name;
            $success = $meta_image->move($upload_path, $image_full_name);
            $img = Image::make($image_url_meta)->resize(900, 350)->save();
            if($success)
            {
                $old_meta_image = $request->old_meta_image;
                if (file_exists($old_meta_image)) {
                    unlink($request->old_meta_image);
                }
                $data->meta_image = $image_url_meta;
            }
        }        
        
        $data->status = $request->status;
        if( $data->save()){
            //update html
            $html = new Html();
            $html->generator('site_setting');
            $html->generator('phone_number');
            $html->generator('footer_contact');
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
        $imagePath = SiteSetting::select('image')->where('id', $id)->first();
        $filePath = $imagePath->image;
        if (file_exists($filePath)) {
            unlink($filePath);
            SiteSetting::where('id', $id)->delete();
           if($siteSetting){
            //update html
            $html = new Html();
            $html->generator('site_setting');
            $html->generator('phone_number');
        }
        }else{
            SiteSetting::where('id', $id)->delete();
            if($siteSetting){
            //update html
            $html = new Html();
            $html->generator('site_setting');
            $html->generator('phone_number');
        }
        }
        
        $notification=array(
            'message' => 'SiteSetting Deleted Successfully !!',
            'alert-type' => 'error'
        );
        
        return redirect()->back()->with($notification);    }
}
