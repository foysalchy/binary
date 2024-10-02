<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Tag;
use App\Models\Post;
use App\Models\Post_tag;
use Str;
use Auth;
use Intervention\Image\Facades\Image;

class PostController extends Controller
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
            $posts = Post::get();
            return view('backend.admin.post.index', compact('posts'));
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
            $tags = Tag::where('status', 1)->get();
    
            return view('backend.admin.post.create', compact('categories', 'tags'));
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
            'title' => 'string|required|unique:posts',
            'slug' => 'string|required|unique:posts',
            'status' => 'required',
            'meta_title' => 'string',
            'meta_description' => 'string',
            'description' => 'string',
            'key_words' => 'string', 
           

        ]);

        $user_id = Auth::user()->id;
      
        $data = new Post();
        $data->user_id = $user_id;
        $data->category_id = $request->category_id;
        $data->title = $request->title;
        $data->slug = str::slug($request->title);
        $data->meta_title = $request->meta_title;
        $data->meta_description = $request->meta_description;
        $data->key_words = $request->key_words;
        $data->description = $request->description;
        $data->video = $request->video;
        $data->date = $request->date;
        $data->time = $request->time;
        $data->status = $request->status;
        
        $image = $request->file('image');
        if($image)
        {
            $image_name= $image->getClientOriginalName();
            $image_full_name = $image_name;
            $upload_path = 'images/post_image/';
            $image_url = $upload_path.$image_full_name;
            $success = $image->move($upload_path, $image_full_name);
            $img = Image::make($image_url)->resize(800,450)->save();
            if($success)
            {
                $data->image = $image_url;
            }
        }
        $data->status = $request->status;
        $data->save();

        $tag_id= $request->tag_id;
        if ($tag_id) {
            foreach ($tag_id as $key => $value ){
                $post_category = new Post_tag();
                $post_category->post_id =$data->id;
                $post_category->tag_id=$value;
                $post_category->save();
            }   
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
        
            $post = Post::find($id);
            $categories = Category::where('status', 1)->get();
            $tags = Tag::where('status', 1)->get();
            $post_tags = Post_tag::where('post_id', $id)->get();
    
            return view('backend.admin.post.edit', compact('post', 'categories', 'tags', 'post_tags'));
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
            'title' => 'string|required',
            'slug' => 'string|required', 
            'meta_title' => 'string',
            'meta_description' => 'string',
            'description' => 'string',
            'key_words' => 'string', 
           

        ]);


        $user_id = Auth::user()->id;
      
        $data = Post::find($id);
        $data->user_id = $user_id;
        $data->category_id = $request->category_id;
        $data->title = $request->title;
        $data->slug = str::slug($request->title);
        $data->meta_title = $request->meta_title;
        $data->meta_description = $request->meta_description;
        $data->key_words = $request->key_words;
        $data->description = $request->description;
        $data->video = $request->video;
        $data->date = $request->date;
        $data->time = $request->time;
        $data->status = $request->status;
        
        $image = $request->file('image');
        if($image)
        {
            $image_name= $image->getClientOriginalName();
            $image_full_name = $image_name;
            $upload_path = 'images/post_image/';
            $image_url = $upload_path.$image_full_name;
            $success = $image->move($upload_path, $image_full_name);
            $img = Image::make($image_url)->resize(800,450)->save();
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
        $data->save();

        $PostTags = Post_tag::where('post_id', $id)->delete();
        $tag_id= $request->tag_id;
        if ($tag_id) {
            foreach ($tag_id as $key => $value ){
                $post_category = new Post_tag();
                $post_category->post_id =$data->id;
                $post_category->tag_id=$value;
                $post_category->save();
            }   
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
        $imagePath = Post::select('image')->where('id', $id)->first();
        $filePath = $imagePath->image;
        if (file_exists($filePath)) {
            unlink($filePath);
            Post::where('id', $id)->delete();
        }else{
            Post::where('id', $id)->delete();
        }
        
        Post_tag::where('post_id', $id)->delete();

        $notification=array(
            'message' => 'Deleted Successfully !!',
            'alert-type' => 'error'
        );
        
        return redirect()->back()->with($notification);
    }
}
