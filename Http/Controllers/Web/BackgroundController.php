<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Str;
use Auth;
use Session;
use App\Models\Background;




class BackgroundController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    private $excel;

    public function index()
    {
        $user_id = Auth::user();

        if($user_id){
            $backgrounds = Background::first();
            return view('backend.admin.background.edit', compact('backgrounds'));
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

        return view('backend.admin.background.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {


        $v = $request->validate([
            'latest_products' => 'nullable|max:255',
            'upcoming_products' => 'nullable|max:255',
            'top_products' => 'nullable|max:255',
            'brand' => 'nullable|max:255',
            'pd_body_bg' => 'nullable|max:255',
            'pd_container_bg' => 'nullable|max:255',
            'pd_text_color' => 'nullable|max:255',
            'pd_nav_bg' => 'nullable|max:255',
        ]);

        $b = new Background();
        $b->latest_products = $v['latest_products'];
        $b->upcoming_products = $v['upcoming_products'];
        $b->top_products = $v['top_products'];
        $b->brand = $v['brand'];
        $b->pd_body_bg = $v['pd_body_bg'];
        $b->pd_container_bg = $v['pd_container_bg'];
        $b->pd_text_color = $v['pd_text_color'];
        $b->pd_nav_bg = $v['pd_nav_bg'];
        $b->save();

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
        return view('backend.admin.background.edit');
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

        $v = $request->validate([
            'latest_products' => 'nullable|max:255',
            'upcoming_products' => 'nullable|max:255',
            'top_products' => 'nullable|max:255',
            'brand' => 'nullable|max:255',
            'pd_body_bg' => 'nullable|max:255',
            'pd_container_bg' => 'nullable|max:255',
            'pd_text_color' => 'nullable|max:255',
            'pd_nav_bg' => 'nullable|max:255',
            'product_details_tab' => 'nullable|max:255',
        ]);

        $b = Background::first();
        $b->latest_products = $v['latest_products'];
        $b->upcoming_products = $v['upcoming_products'];
        $b->top_products = $v['top_products'];
        $b->brand = $v['brand'];
        $b->pd_body_bg = $v['pd_body_bg'];
        $b->pd_container_bg = $v['pd_container_bg'];
        $b->pd_text_color = $v['pd_text_color'];
        $b->pd_nav_bg = $v['pd_nav_bg'];
        $b->product_details_tab = $v['product_details_tab'];
        $b->update();

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

        $notification=array(
            'message' => 'Product Deleted Successfully !!',
            'alert-type' => 'error'
        );
        return redirect()->back()->with($notification);
    }

}
