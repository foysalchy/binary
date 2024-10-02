<?php

namespace App\Http\Controllers\Web;
use \Cache;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Popup;
use Str;
use Intervention\Image\Facades\Image;
use Auth;

class PopupController extends Controller
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
            $popups = Popup::orderBy('id', 'desc')->get();
            return view('backend.admin.popup.index', compact('popups'));
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
            return view('backend.admin.popup.create');
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
            'image_d' => 'required',
            'image_m' => 'required',
            'title' => 'required',
        ]);


        $data = new Popup();
        $data->title = $request->title;
        $data->link = $request->link;
        $imaged = $request->file('image_d');
        $imagem = $request->file('image_m');  
        $data->status = $request->status;

        if($imaged)
        {
            $image_full_name = $imaged->getClientOriginalName();
            $upload_path = 'images/popup/';
            $image_url = $upload_path.$image_full_name;
            $success = $imaged->move($upload_path, $image_full_name);
            $imaged = Image::make($upload_path. $image_full_name);
            $imaged->save($upload_path .'/desktop-popup-'. $image_full_name);
            $data->image = $image_full_name;
            
        }
        
        if($imagem)
        {
            $image_full_name = $imagem->getClientOriginalName();
            $upload_path = 'images/popup/';
            $image_url = $upload_path.$image_full_name;
            $success = $imagem->move($upload_path, $image_full_name);
            $imagem = Image::make($upload_path. $image_full_name);
            $imagem->save($upload_path .'/mobile-popup-'. $image_full_name);

            $data->image_mobile = $image_full_name;
            //dd($data->image_mobile);
        }
        // dd( $image_full_name );
        $data->id = $data->id;
        $data->save();

        $notification = array(
            'message' => 'Successfully Done',
            'alert-type' => 'success'
        );
        return redirect('/admin/popup')->with($notification);
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
            $popup = Popup::findorfail($id);
            return view('backend.admin.popup.edit', compact('popup'));
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
        $data = Popup::find($id);
        $data->title = $request->title;
        $data->link = $request->link;

        $imaged = $request->file('image_d');
        $imagem = $request->file('image_m');

        if($imaged)
        {
            $image_full_name = $imaged->getClientOriginalName();
            $upload_path = 'images/popup/';
            $image_url = $upload_path.$image_full_name;
            $success = $imaged->move($upload_path, $image_full_name);
            $imaged = Image::make($upload_path. $image_full_name);
            $imaged->save($upload_path .'/desktop-popup-'. $image_full_name);
            $data->image = $image_full_name;
        }
        if($imagem)
        {
            $image_full_name = $imagem->getClientOriginalName();
            $upload_path = 'images/popup/';
            $image_url = $upload_path.$image_full_name;
            $success = $imagem->move($upload_path, $image_full_name);
            $imagem = Image::make($upload_path. $image_full_name);
            $imagem->save($upload_path .'/mobile-popup-'. $image_full_name);
            $data->image_mobile = $image_full_name;
        }
        
        $data->status = $request->status;
        $data->save();
        $notification=array(
            'message' => 'Successfully Done',
            'alert-type' => 'success'
        );
        // $banners = Banner::where('status',1)->orderBy('id', 'DESC')->take(5)->get();
        // Cache::put('banners', json_encode($banners), $seconds = 10000000000);
        return redirect('/admin/popup')->with($notification);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $imagePath = Popup::select('image')->where('id', $id)->delete();

        $notification=array(
            'message' => 'Popup Deleted Successfully !!',
            'alert-type' => 'error'
        );
        
        return redirect()->back()->with($notification);
    }
}
