<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\SliderBoxImage;
use Illuminate\Http\Request;
use Auth;
use Intervention\Image\Facades\Image;

class SliderBoxImageController extends Controller
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
            $slider_box = SliderBoxImage::get();
            return view('backend.admin.sliderbox.index',compact('slider_box'));
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
            return view('backend.admin.sliderbox.create');
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
            'title' => 'required',
        ]);
            $data = new SliderBoxImage ();
            $data->title = $request->title;
            $data->url = $request->url;
            $data->status = $request->status;

            
            $image = $request->file('img');
            if($image)
            {
                //dd($upload_path);
                $image_name= $image->getClientOriginalName();
                $image_full_name = $image_name;
                $upload_path = 'images/slider_box_image/';
                $image_url = $upload_path.$image_full_name;
                $success = $image->move($upload_path, $image_full_name);
                // $img = Image::make($image_url)->resize(155, 55)->save();
                //dd($image);
                //resize images jwe
                $sizes = [160, 480, 800, 1600];
                $size_name = ['t', 's', 'm', 'l'];

                for($i = 0; $i < 4; $i++) {
                    $image = Image::make($upload_path. $image_full_name);
                    $image->widen($sizes[$i]);
                    $image->save($upload_path .$size_name[$i].'/'. $size_name[$i] . $image_full_name);
                }
                

                if($success)
                {
                    $data->img = $image_url;
                }
            }
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
            $data = SliderBoxImage::findorfail($id);
            return view('backend.admin.sliderbox.edit', compact('data'));
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
        $data = SliderBoxImage::find($id);
        $data->title = $request->title;
        $data->url = $request->url;
        $data->status = $request->status;
        $image = $request->file('img');
        if($image)
        {
            $image_name= $image->getClientOriginalName();
            $image_full_name = $image_name;
            $upload_path = 'images/slider_box_image/';
            $image_url = $upload_path.$image_full_name;
            $success = $image->move($upload_path, $image_full_name);
            // $img = Image::make($image_url)->resize(155, 55)->save();

            $sizes = [200, 480, 800, 1600];
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
                $data->img = $image_url;
            }
        }
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
        $imagePath = SliderBoxImage::select('img')->where('id', $id)->first();
        $filePath = $imagePath->image;
        if (file_exists($filePath)) {
            unlink($filePath);
            SliderBoxImage::where('id', $id)->delete();
        }else{
            SliderBoxImage::where('id', $id)->delete();
        }
        
        $notification=array(
            'message' => 'Deleted Successfully !!',
            'alert-type' => 'error'
        );
        
        return redirect()->back()->with($notification);
    }
}
