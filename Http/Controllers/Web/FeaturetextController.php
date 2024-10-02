<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Auth;

class FeaturetextController extends Controller
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
            $texts = DB::table('feature_text')->get();
            return view('backend.admin.text.index', compact('texts'));
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
            return view('backend.admin.text.create');
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
        // dd($request);
        $validated = $request->validate([
            'title' => 'string|required|max:30',
            'image' => ['image',
                        'mimes:jpg,webp,png,jpeg,gif,svg',
                        'max:1024'],
            'subtitle'=>'string',
        ]);
        $image = $request->file('image');
      //  dd($image);
        if($image)
        {
            $image_name= $image->getClientOriginalName();
            $image_full_name = $image_name;
            $upload_path = 'images/text_image/';
            $image_url = $upload_path.$image_full_name;
            $success = $image->move($upload_path, $image_full_name);
        } 
        DB::table('feature_text')->insert(
            [
            'title' => $request->title,        
            'subtitle' => $request->subtitle,        
            'image' => $image_url ?? '',
            'status' => $request->status,               
            ]
        );
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
        $data = DB::table('feature_text')->find($id);
        return view('backend.admin.text.edit', compact('data'));
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
        //foysal
        $validated = $request->validate([
            'title' => 'string',
            'subtitle'=>'string',
        ]);

        $image = $request->file('image');
        //  dd($image);
          if($image)
          {
              $image_name= $image->getClientOriginalName();
              $image_full_name = $image_name;
              $upload_path = 'images/text_image/';
              $image_url = $upload_path.$image_full_name;
              $success = $image->move($upload_path, $image_full_name);
          } 
          else{
            $data = DB::table('feature_text')->find($id);
            $previous_image =  $data->image;
          }

         DB::table('feature_text')
        ->where('id', $id)
        ->update([
            'title' => $request->title,        
            'subtitle' => $request->subtitle,        
            'image' => $image_url ?? $previous_image,
            'status' => $request->status, 
        ]);
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
        DB::table('feature_text')
        ->where('id', $id)->delete();
        $notification=array(
            'message' => 'Successfully Done',
            'alert-type' => 'success'
        );
        return redirect()->back()->with($notification);
    }
}
