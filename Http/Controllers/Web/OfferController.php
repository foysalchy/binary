<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Auth;
use Illuminate\Support\Carbon;

class OfferController extends Controller
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
            $offers = DB::table('offer_page')->orderBy('id','desc')->get(['id','name','offer_type','image','status']);
            return view('backend.admin.offer.index', compact('offers'));
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
            $offertype = DB::table('offer_type')->orderBy('id','desc')->get();
            return view('backend.admin.offer.create',compact('offertype'));
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
            'status' => 'required',
        ]);

        $image = $request->file('image');
        if($image)
        {
            $image_name= $image->getClientOriginalName();
            $image_full_name = $image_name;
            $upload_path = 'images/offer_image/';
            $image_url = $upload_path.$image_full_name;
            $success = $image->move($upload_path, $image_full_name);
            // $img = Image::make($image_url)->resize(155, 55)->save();
           
        }
        DB::table('offer_page')->insert(
            array(
                   'name'  =>  $request->name,
                   'offer_type'  =>  $request->offer_type,
                   'slug'  =>  $request->offer_type,
                   'meta_title'   =>  $request->meta_title,
                   'description'   => $request->description, 
                   'image'  => $image_url ?? '',
                   'status' =>   $request->status,
                   "created_at" =>  \Carbon\Carbon::now(), # new \Datetime()
                   "updated_at" => \Carbon\Carbon::now(),  # new \Datetime()
            )
       );
       $notification=array(
        'message' => 'Successfully Offer Created',
        'alert-type' => 'success'
    );
       return redirect('admin/offer')->with($notification);
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
            $offertype = DB::table('offer_type')->orderBy('id','desc')->get();
            $offer = DB::table('offer_page')->find($id);
            return view('backend.admin.offer.edit', compact('offer','offertype'));
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
        $image = $request->file('image');
      //dd($image);
        if($image)
        {
            $image_name= $image->getClientOriginalName();
            $image_full_name = $image_name;
            $upload_path = 'images/offer_image/';
            $image_url = $upload_path.$image_full_name;
            $success = $image->move($upload_path, $image_full_name);
            $result = DB::table('offer_page')
            ->where('id', $id)
            ->update(['image' => $image_url]);
        }

        $result = DB::table('offer_page')
        ->where('id', $id)
        ->update([
            'name'   =>   $request->name,
            'offer_type'  =>  $request->offer_type,
            'meta_title'   =>   $request->meta_title,
            'description'   =>   $request->description,
            'status'   =>   $request->status,
            "created_at" =>  \Carbon\Carbon::now(), # new \Datetime()
            "updated_at" => \Carbon\Carbon::now(),  # new \Datetime()
        ]);

        $notification=array(
            'message' => 'Successfully updated Offer',
            'alert-type' => 'success'
        );
        return redirect('admin/offer')->with($notification);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $offers =  DB::table('offer_page')->where('id', $id)->delete();
        
      
      
      $notification=array(
          'message' => 'Offer Deleted Successfully !!',
          'alert-type' => 'error'
      );
      
      return redirect()->back()->with($notification);
    }
    
}
