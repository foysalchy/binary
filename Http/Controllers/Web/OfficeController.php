<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Office;
use Auth;
use App\Models\Html;

class OfficeController extends Controller
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
            $offices = Office::get();
            return view('backend.admin.office.index', compact('offices'));
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
            return view('backend.admin.office.create');
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
            'branch_name' => 'required|unique:offices',
            'address' => 'required',
            'phone' => 'required',
            'email' => 'required',
            'status' => 'required',
        ]);

        $data = new Office();
        $data->branch_name = $request->branch_name;
        $data->address = $request->address;
        $data->sales_support = $request->sales_support;
        $data->technicale_support = $request->technicale_support;
        $data->warranty_support = $request->warranty_support;
        $data->phone = $request->phone;
        $data->email = $request->email;
        $data->note = $request->note;
        $data->status = $request->status;
        $data->map = $request->map;
        $data->iframe = $request->iframe;

        $image = $request->file('image');
        if($image)
        {
            $image_name= $image->getClientOriginalName();
            $image_full_name = $image_name;
            $upload_path = 'images/office_image/';
            $image_url = $upload_path.$image_full_name;
            $success = $image->move($upload_path, $image_full_name);
            if($success)
            {
                $data->image = $image_url;
            }
        }


        if( $data->save()){
            //update html
            $html = new Html();
            $html->generator('office');
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
            $office = Office::findorfail($id);
            return view('backend.admin.office.edit', compact('office'));
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
        $data = Office::find($id);
        $data->branch_name = $request->branch_name;
        $data->address = $request->address;
        $data->sales_support = $request->sales_support;
        $data->technicale_support = $request->technicale_support;
        $data->warranty_support = $request->warranty_support;
        $data->phone = $request->phone;
        $data->email = $request->email;
        $data->note = $request->note;
        $data->status = $request->status;

        $data->map = $request->map;
        $data->iframe = $request->iframe;

        $image = $request->file('image');
        if($image)
        {
            $image_name= $image->getClientOriginalName();
            $image_full_name = $image_name;
            $upload_path = 'images/office_image/';
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

        if( $data->save()){
            //update html
            $html = new Html();
            $html->generator('office');
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
      $office =  Office::where('id', $id)->delete();
      if($office){
        //update html
        $html = new Html();
        $html->generator('office');
    }
        $notification=array(
            'message' => 'Successfully Done',
            'alert-type' => 'error'
        );
        return redirect()->back()->with($notification);

    }
}
