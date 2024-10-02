<?php

namespace App\Http\Controllers\Web;
use \Cache;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Html;
use App\Models\Category;
use App\Models\CatFaq;
use App\Models\Subcategory;
use App\Models\Prosubcategory;
use App\Models\Proprocategory;
use Str;
use Auth;

class Proprocategorycontroller extends Controller
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
            $proprocategories = Proprocategory::get();
            return view('backend.admin.proprocategory.index', compact('proprocategories'));
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
            $categories = Category::where('status', 1)->get();
            $subcategories = Subcategory::where('status', 1)->get();
            $prosubcategories = Prosubcategory::where('status', 1)->get();
            return view('backend.admin.proprocategory.create', compact('categories', 'subcategories', 'prosubcategories'));
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
         // validate to foysal
        $validatedData = $request->validate([
            'name' => 'string|required',
            'slug' => 'string|required|unique:proprocategories',
            'category_id' => 'required',
            'subcategory_id' => 'required',
            'pro_sub_category_id' => 'required',
            'status' => 'required',
            'meta_title' => 'string',
            'meta_des' => 'string',
            'description' => 'string',
            'meta_keywords' => 'string', 
        ]);

        $data = new Proprocategory();
        $data->category_id = $request->category_id;
        $data->subcategory_id = $request->subcategory_id;
        $data->pro_sub_category_id = $request->pro_sub_category_id;
        $data->name = $request->name;

        $data->slug = Str::slug($request->slug);
        $data->meta_title = $request->meta_title;
        $data->meta_des = $request->meta_des;
        $data->meta_keywords = $request->meta_keywords;
        $data->description = $request->description;
        
        $image = $request->file('image');
        if($image)
        {
            $image_name= $image->getClientOriginalName();
            $image_full_name = $image_name;
            $upload_path = 'images/subcat_image/';
            $image_url = $upload_path.$image_full_name;
            $success = $image->move($upload_path, $image_full_name);
            // $img = Image::make($image_url)->resize(155, 55)->save();
            if($success)
            {
                $data->image = $image_url;
            }
        }

        $data->status = $request->status;
        if($data->save()){
            //update html
            $html = new Html();
            $html->generator('menu');
            $html->generator('mobile_menu');
        }
        $notification=array(
            'message' => 'Successfully Done',
            'alert-type' => 'success'
        );

        $faqs = $request->faq;
        if ($faqs) {
            foreach ($faqs as $faq){
                if(!empty($faq['question'])){
                    $product_faq = new CatFaq();
                    $product_faq->proprocategory_id = $data->id;
                    $product_faq->question = $faq['question'];
                    $product_faq->answer = $faq['answer'];
                    $product_faq->point = $faq['point'];
                    $product_faq->save();
                }
            }   
        }

        Cache::put('proprosubcategory_'.$data->id, json_encode($data), $seconds = 10000000000);
        $proprosubcategories = Proprocategory::select(['id','name','slug','pro_sub_category_id'])->where('status',1)->get();
        Cache::put('proprosubcategories', json_encode($proprosubcategories), $seconds = 10000000000);

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
            $categories = Category::where('status', 1)->get();
            $subcategories = Subcategory::where('status', 1)->get();
            $prosubcategories = Prosubcategory::where('status', 1)->get();
            $proprocategory = Proprocategory::find($id);
            $catFaqs = CatFaq::where('proprocategory_id', $id)->get();
            return view('backend.admin.proprocategory.edit', compact('subcategories', 'categories', 'prosubcategories', 'proprocategory','catFaqs'));
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
         // validate to foysal
         $validatedData = $request->validate([
            'name' => 'string|required',
            'slug' => 'string',
           
            'status' => 'required',
            'meta_title' => 'string',
            'meta_des' => 'string',
            'description' => 'string',
            'meta_keywords' => 'string', 
        ]);

        $data = Proprocategory::find($id);
        $data->category_id = $request->category_id;
        $data->subcategory_id = $request->subcategory_id;
        $data->pro_sub_category_id = $request->pro_sub_category_id;
        $data->name = $request->name;

        $data->slug = Str::slug($request->slug);
        $data->meta_title = $request->meta_title;
        $data->meta_des = $request->meta_des;
        $data->meta_keywords = $request->meta_keywords;
        $data->description = $request->description;
        $data->category_summary = $request->category_summary;
        $data->section_1 = $request->section_1;
        $data->section_2 = $request->section_2;
        
        $image = $request->file('image');
        if($image)
        {
            $image_name= $image->getClientOriginalName();
            $image_full_name = $image_name;
            $upload_path = 'images/subcat_image/';
            $image_url = $upload_path.$image_full_name;
            $success = $image->move($upload_path, $image_full_name);
            // $img = Image::make($image_url)->resize(155, 55)->save();
            if($success)
            {   
                $old_image = $request->old_image;
                if (file_exists($old_image)) {
                    unlink($request->old_image);
                }
                $data->image = $image_url;
            }
        }         
        
        $data->status = $request->status;
        if($data->save()){
            //update html
            $html = new Html();
            $html->generator('menu');
            $html->generator('mobile_menu');
        }
        $notification=array(
            'message' => 'Successfully Done',
            'alert-type' => 'success'
        );
        $catFaq = CatFaq::where('proprocategory_id', $id)->delete();
        $faqs = $request->faq;
        if ($faqs) {
            // dd($faqs);
            foreach ($faqs as $faq){
                // dd($faq);
                if(!empty($faq['question'])){
                    $product_faq = new CatFaq();
                    $product_faq->proprocategory_id = $id;
                    $product_faq->question = $faq['question'];
                    $product_faq->answer = $faq['answer'];
                    $product_faq->point = $faq['point'];
                    $product_faq->save();
                }
            }   
        }
        Cache::put('proprosubcategory_'.$data->id, json_encode($data), $seconds = 10000000000);
        $proprosubcategories = Proprocategory::select(['id','name','slug','pro_sub_category_id'])->where('status',1)->get();
        Cache::put('proprosubcategories', json_encode($proprosubcategories), $seconds = 10000000000);

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
        $data = Proprocategory::find($id);
        $delete = $data->delete();
        if($delete){
            //update html
            $html = new Html();
            $html->generator('menu');
            $html->generator('mobile_menu');
        }
        $notification=array(
            'message' => 'Successfully Done',
            'alert-type' => 'error'
        );
        return redirect()->back()->with($notification);
    }
}
