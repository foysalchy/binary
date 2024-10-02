<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use DB;

class HomepageOfferController extends Controller
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
            $homeOffers = DB::table('homeoffer')->orderBy('id','ASC')->get();
            return view('backend.admin.homeoffer.index', compact('homeOffers'));
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
        //
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
            $homeoffer = DB::table('homeoffer')->find($id);
            //dd($homeoffer);
            return view('backend.admin.homeoffer.edit', compact('homeoffer'));
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
          if($image)
          {
              $image_name= $image->getClientOriginalName();
              $image_full_name = $image_name;
              $upload_path = 'images/home_offer_image/';
              $image_url = $upload_path.$image_full_name;
              $success = $image->move($upload_path, $image_full_name);
              $result = DB::table('homeoffer')
              ->where('id', $id)
              ->update(['image' => $image_url]);
          }

        $result = DB::table('homeoffer')
        ->where('id', $id)
        ->update([
            'title' =>  $request->title,
            'url'   =>  $request->url,
            'status'   =>  $request->status,
        ]);
        $notification=array(
            'message' => 'Successfully updated Home Offer',
            'alert-type' => 'success'
        );
        return redirect('admin/homeoffer')->with($notification);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
