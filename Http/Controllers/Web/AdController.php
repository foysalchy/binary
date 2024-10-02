<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ad;
use Str;
use Intervention\Image\Facades\Image;
use Auth;

class AdController extends Controller
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
            $ads = Ad::get();
            return view('backend.admin.ad.index', compact('ads'));
        }

        return redirect('login');
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
            return view('backend.admin.ad.create');
        }  
        
        return redirect('login');
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
            'image' => 'required',
            'page' => 'nullable|max:500',
            'cat' => 'nullable|max:500',
            'status' => 'required',
        ]);


        $data = new Ad();
        $image = $request->file('image');
        if($image)
        {
            $image_name= $image->getClientOriginalName();
            $image_full_name = mt_rand(100000, 999999).'_'.$image_name;
            $upload_path = 'images/f_s_ads_img/';
            $image_url = $upload_path.$image_full_name;
            $success = $image->move($upload_path, $image_full_name);
            // $img = Image::make($image_url)->resize(840, 360)->save();
            if($success)
            {
                $data->image = $image_url;
            }
        }
        $data->page = $request->page;
        $data->cta = $request->cta;
        $data->status = $request->status;
        $data->save();
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
            $ad = Ad::findorfail($id);
            return view('backend.admin.ad.edit', compact('ad'));
        }
        
        return redirect('login');
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
        $data = Ad::findOrFail($id);
        $data->page = $request->page;
        $data->cta = $request->cta;

        $image = $request->file('image');
        if($image)
        {
            $image_name= $image->getClientOriginalName();
            $image_full_name = mt_rand(100000, 999999).'_'.$image_name;
            $upload_path = 'images/f_s_ads_img/';
            $image_url = $upload_path.$image_full_name;
            $success = $image->move($upload_path, $image_full_name);
            // $img = Image::make($image_url)->resize(840, 360)->save();
            if($success)
            {
                $old_image = $data->image;
                if (file_exists($old_image)) {
                    try {
                        unlink($old_image);
                    } catch (\Throwable $th) {
                        //throw $th;
                    }
                    
                }
                $data->image = $image_url;
            }
        }
        $data->status = $request->status;
        $data->save();
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
        $ad = Ad::findOrFail($id);
        $filePath = $ad->image;
        if (file_exists($filePath)) {
            try {
                unlink($filePath);
            } catch (\Throwable $th) {

            }
            
        }
        $ad->delete();
        
        $notification=array(
            'message' => 'Banner Deleted Successfully !!',
            'alert-type' => 'error'
        );
        
        return redirect()->back()->with($notification);
    }
}
