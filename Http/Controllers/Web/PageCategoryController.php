<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PageCategory;
use Auth;
use DB;

class PageCategoryController extends Controller
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
            $categories = PageCategory::get();
            return view('backend.admin.pagecategory.index', compact('categories'));
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
            return view('backend.admin.pagecategory.create');
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
            'name' => 'string|required',
            'status' => 'required',
        ]);

        $category = new PageCategory();
        $category->name = $request->name;
        $category->status = $request->status;
        $category->save();
        $notification=array(
            'message' => 'PageCategory Saved Successfully !!',
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
            $category = PageCategory::findorfail($id);
            return view('backend.admin.pagecategory.edit', compact('category'));
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
        //foysal
        $validatedData = $request->validate([
            'name' => 'string',
        ]);

        $category = PageCategory::findorfail($id);
        $category->name = $request->name;
        $category->status = $request->status;
        $category->save();
        $notification=array(
            'message' => 'PageCategory Updated Successfully !!',
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
        $page = PageCategory::findorfail($id);
        $page->delete();
        $notification=array(
        'message' => 'PageCategory Deleted Successfully !!',
            'alert-type' => 'error'
        );

        return redirect()->back()->with($notification);
    }
    public function complain_list(){
        $user_id = Auth::user();
        if($user_id){
            $complains = DB::table('complain')->orderBy('id', 'desc')->paginate(50);
            return view('backend.admin.complain.complain-list', compact('complains'));
        }else{
            return redirect('login');
        } 
    }
}
