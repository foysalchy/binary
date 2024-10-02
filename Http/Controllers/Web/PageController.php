<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Page;
use App\Models\PageCategory;
use Str;
use Auth;
use DB;
use App\Models\Html;

class PageController extends Controller
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
            $pages = Page::get();
            return view('backend.admin.page.index', compact('pages'));
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
            $categories = PageCategory::where('status', 1)->get();
            return view('backend.admin.page.create', compact('categories'));
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
        // foysal
        $validatedData = $request->validate([
            'title' => 'string|required|unique:pages',
            'content' => 'string|required',
            'status' => 'required',
            'meta_title' => 'string',
            'meta_des' => 'string',
            'meta_keywords' => 'string',
        ]);

        $category = new Page();
        $category->page_category_id = $request->page_category_id;
        $category->title = $request->title;
        $category->slug = str::slug($request->title);
        
        $category->meta_title = $request->meta_title;
        $category->meta_des = $request->meta_des;
        $category->meta_keywords = $request->meta_keywords;
        
        $image = $request->file('meta_image');
        if($image)
        {
            $image_name= $image->getClientOriginalName();
            $image_full_name = $image_name;
            $upload_path = 'images/page_image/';
            $image_url = $upload_path.$image_full_name;
            $success = $image->move($upload_path, $image_full_name);
            // $img = Image::make($image_url)->resize(155, 55)->save();
            if($success)
            {
                $category->meta_image = $image_url;
            }
        }
        
        $category->content = $request->content;
        $category->status = $request->status;
        if( $category->save()){
            //update html
            $html = new Html();
            $html->generator('dynamic_page');
        }
        $notification=array(
            'message' => 'Page Saved Successfully !!',
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
            $page = Page::findorfail($id);
            $categories = PageCategory::where('status', 1)->get();
            return view('backend.admin.page.edit', compact('page', 'categories'));
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
        // foysal
          $request->validate([
            'title' => 'string',
            'content' => 'string', 
            'meta_title' => 'string',
            'meta_des' => 'string',
            'meta_keywords' => 'string',
        ]);

        $category = Page::findorfail($id);
        $category->page_category_id = $request->page_category_id;
        $category->title = $request->title;
        $category->slug = str::slug($request->title);
        
        
        $category->meta_title = $request->meta_title;
        $category->meta_des = $request->meta_des;
        $category->meta_keywords = $request->meta_keywords;
        
        $image = $request->file('meta_image');

        if($image)
        {
            $image_name= $image->getClientOriginalName();
            $image_full_name = $image_name;
            $upload_path = 'images/page_image/';
            $image_url = $upload_path.$image_full_name;
            $success = $image->move($upload_path, $image_full_name);
            // $img = Image::make($image_url)->resize(155, 55)->save();
            if($success)
            {
                
                $old_meta_image = $request->old_meta_image;
                if (file_exists($old_meta_image)) {
                    unlink($request->old_meta_image);
                }                
                
                $category->meta_image = $image_url;
            }
        }        
        
        $category->content = $request->content;
        $category->status = $request->status;
        if( $category->save()){
            //update html
            $html = new Html();
            $html->generator('dynamic_page');
        }
        $notification=array(
            'message' => 'Page Updated Successfully !!',
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
        $page = Page::findorfail($id);
        $page->delete();
        if( $page->delete()){
            //update html
            $html = new Html();
            $html->generator('dynamic_page');
        }
        $notification=array(
        'message' => 'Page Deleted Successfully !!',
            'alert-type' => 'error'
        );

        return redirect()->back()->with($notification);
    }
    
     public function stock_report(){
        $categoryreport = DB::table('categories')->get();
        $Subcategoryreport = DB::table('subcategories')->get();
        $ProSubcategoryreport = DB::table('prosubcategories')->get();
        $ProProSubcategoryreport = DB::table('proprocategories')->get();
        return view('backend.admin.page.stock-report', compact('categoryreport','Subcategoryreport','ProSubcategoryreport','ProProSubcategoryreport'));
    }
    
    public function user_activity(){
        $activities= DB::table('activity_log')->orderBy('id','desc')->get();
        return view('backend.admin.page.activity',compact('activities'));
    }
    
       public function pc_create(){
        return view('backend.admin.pc_builder.create');
    }
    public function pc_store( Request $request){
        DB::table('pc_content')->insert(
            [
                'description_one' => $request->description,
                'description_two' => $request->description_two,
                'meta_title' => $request->meta_title,
                'meta_des' => $request->meta_des,
             ]
            );
            $notification=array(
                'message' => 'Content Inserted Successfully !!',
                'alert-type' => 'success'
            );
            return redirect('admin/pc-manage')->with($notification);
    }
    
    //pc content add

    public function pc_manage(){
        $contents = DB::table('pc_content')->get();
        return view('backend.admin.pc_builder.index', compact('contents'));
    }

    public function pc_edit( Request $request, $id ){
        $content = DB::table('pc_content')->find($id);
  
        return view('backend.admin.pc_builder.edit', compact('content'));
    }

    public function pc_update( Request $request, $id ){

       DB::table('pc_content')
            ->where('id', $id)
            ->update([
                'description_one' => $request->description,
                'description_two' => $request->description_two,
                'meta_title' => $request->meta_title,
                'meta_des' => $request->meta_des,
            ]);
            $notification=array(
                'message' => 'Content Updated Successfully !!',
                'alert-type' => 'success'
            );
            return redirect()->back()->with($notification);
        
    }
    
    public function pc_delete( $id ){

        DB::table('pc_content')->where('id', $id)->delete();
        $notification=array(
            'message' => 'Content Deleted Successfully !!',
            'alert-type' => 'success'
        );
        return redirect()->back()->with($notification);

    }
  
    
    
}
