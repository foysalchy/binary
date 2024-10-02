<?php

namespace App\Http\Controllers\Web;
use \Cache;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Landing;
use App\Models\Product;

use App\Models\Banner;
use App\Models\Brand;
use App\Models\Category;
use App\Models\FeatureProduct;
use App\Models\Subcategory;
use App\Models\Prosubcategory;
use App\Models\Proprocategory;
use App\Models\ProductRundown;
use App\Models\Post;
use App\Models\ShopType;

use Str;
use Intervention\Image\Facades\Image;
use Auth;

class LandingController extends Controller
{
    // public function getLinkData(){
    //     $user_id = Auth::user();
    //     if($user_id){
    //         $products = Product::orderBy('id', 'desc')->get();
    //         return view('backend.admin.landing.index', compact('products'));
    //     }else{
    //         return redirect('login');
    //     }
    // }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public $array_tables = [
        1=>'Static',
        2=>'Product',
        3=>'Category',
        4=>'Subcategory',
        5=>'Prosubcategory',
        6=>'Proprocategory',
        7=>'Brand',
        8=>'Post',
        9=>'Banner',
        10=>'ShopType'
    ];
    public $array_static_pages = [1=>'Home',2=>'Brands'];
    
    public function generator($page){
        if(empty($page)){dd('Go back! Something going wrong!!');}
        // generator::menu-category-slider-feature
        if($page == 'menu'){
            $txt = '<nav><ul><li><a title="Binary logic brands" href="brands">Our Brands</a></li><li><a title="Binary logic offers" href="offer">Offer</a></li>';
            $categories = Category::where('status', 1)->orderBy('position_id','ASC')->select('id','name','slug')->get(); 
            foreach($categories as $category){
                $subCategories = Subcategory::where('category_id', $category['id'])->where('status', 1)->select('id','name','slug')->get();
                if(!empty($subCategories) && sizeof($subCategories)>0){$txt_angle = '<i class="fa fa-angle-down"></i>';}else{$txt_angle = '';}
                $txt .= '<li><a title="'.$category['name'].'" href="'.$category['slug'].'">'.$category['name'].$txt_angle.'</a>';
                if(!empty($subCategories) && sizeof($subCategories)>0){
                    $txt .= '<ul class="sub_menu">';
                    foreach($subCategories as $category2){
                        $proSubCategories = Prosubcategory::where('subcategory_id', $category2['id'])->where('status', 1)->select('id','name','slug')->get();
                        if(!empty($proSubCategories) && sizeof($proSubCategories)>0){$txt_angle = '<i class="fa fa-chevron-right float-end" style="font-size: 12px;"></i>';}else{$txt_angle = '';}
                        $txt .= '<li><a href="'.$category2['slug'].'">'.$category2['name'].$txt_angle.'</a>';
                        if(!empty($proSubCategories) && sizeof($proSubCategories)>0){
                            $txt .= '<ul>';
                            foreach($proSubCategories as $category3){
                                $proproCategories = Proprocategory::where('pro_sub_category_id', $category3['id'])->where('status', 1)->select('id','name','slug')->get();
                                if(!empty($proproCategories) && sizeof($proproCategories)>0){$txt_angle = '<i class="fa fa-chevron-left float-end" style="font-size: 12px;"></i>';}else{$txt_angle = '';}
                                $txt .= '<li><a href="'.$category3['slug'].'">'.$category3['name'].$txt_angle.'</a>';
                                if(!empty($proproCategories) && sizeof($proproCategories)>0){
                                    $txt .= '<ul>';
                                    foreach($proproCategories as $category4){
                                        $txt .= '<li><a href="'.$category4['slug'].'">'.$category4['name'].'</a></li>';
                                    }
                                    $txt .= '</ul>';
                                }
                                $txt .= '</li>';
                            }
                            $txt .= '</ul>';
                        }
                        $txt .= '</li>';
                    }
                    $txt .= '</ul>';
                }
                $txt .= '</li>';
            }
        }elseif($page == 'category'){
            $shops = ShopType::where('status',1)->get();
            $txt = '
            <section class="container my-4"><h2 class="text-center mb-4">FEATURED CATEGORY</h2><ul class="row">';
            foreach($shops as $shop){
                $img = explode('/',$shop->image);
                $img[2] = 'thumb-'.$img[2];
                $img_arr_s = $img[0].'/'.$img[1].'/'.$img[2];

                $txt .= '
                    <li class="col-md-3 col-6 mb-2 text-center">
                        <a class="single-cate with-title" href="'.$shop->slug.'">
                        <strong class="font-weight-bold d-inline-block fbitx">'.$shop->title.'</strong>
                        <picture class="float-end" style="max-width:60px">
                        <img class="card-img-top px-1 p-sm-2" src="'.asset($shop->image).'" alt="'.$shop->title.'">
                        </picture>
                        </a>
                    </li>';
            }
            $txt .= '</ul></section>';
            
        }elseif($page == 'slider'){
            $txt = '<section class="slider_section"><div class="row slider">';
            $banners = Banner::where('status', 1)->get();
            foreach($banners as $banner){
                $img_arr = explode('/',$banner->image);
                $img_arr_t = $img_arr[0].'/'.$img_arr[1].'/t/'.$img_arr[2];
                $img_arr_s = $img_arr[0].'/'.$img_arr[1].'/s/'.$img_arr[2];
                $img_arr_m = $img_arr[0].'/'.$img_arr[1].'/m/'.$img_arr[2];
                $img_arr_l = $img_arr[0].'/'.$img_arr[1].'/l/'.$img_arr[2];
                $txt .= '<div class="col-md-12">
                <a href="'.$banner['url'].'" class="w-100">
                    <picture>
                        <source srcset="'.asset($img_arr_s).'" media="(max-width: 768px)">
                        <source srcset="'.asset($img_arr_m).'" media="(max-width: 1200px)">
                        <img src="'.asset($img_arr_l).'" alt="'.asset($banner->title).'" class="w-100"/>
                    </picture>
                </a>
            </div>';
            }
            $txt .= '</div></section>';
        }elseif($page == 'feature'){
            $products = FeatureProduct::select('products.id','name','subtitle','slug','buying_price','discount_price','regular_price',
            'special_price','offer_price','price_highlight','call_for_price','product_image_small',
            'image','image_des','image_alt','products.status','sku','discount','stock_status')
            ->join('products', 'feature_products.product_id', '=', 'products.id')
            ->orderBy('seq', 'ASC')
            ->where('products.stock_status', '=', 'in_stock')
            ->take(12)
            ->get();
            $txt = '<div id="feature-product">
            <section class="featured_product_area">
            <div class="container">
            <div class="row">
            <div class="col-12">
            <div class="section_title">
            <h2>Latest Offer</h2>
            </div>
            </div>
            </div>
            <div class="row featured_container featured_column5 product_carousel">
            ';
            foreach($products as $product){
                $price_old = 0;
                $price_current = 0;
                if (!empty($product->offer_price && $product->special_price)){
                    $price_old = $product->special_price;
                    $price_current = $product->offer_price;
                }elseif(!empty($product->regular_price && $product->special_price)){
                    $price_old = $product->regular_price;
                    $price_current = $product->special_price;
                }elseif(!empty( $product->regular_price)){
                    $price_current = $product->regular_price;
                }
                $txt .= '<div class="col-6 col-md-4">
                <article class="single_product">
                    <figure>
                        <div class="product_thumb">
                            <a class="primary_img" href="'.$product['slug'].'" title="'.$product['name'].'">
                                <img src="'.asset($product['image']).'" alt="'.$product['name'].'" title="'.$product['name'].'">
                            </a>
                        </div>

                        <figcaption class="product_content">
                            <div class="price_box box_1">
                                <span class="old_price text-danger">৳ '.$price_old.'</span>
                                <span class="current_price">৳ '.$price_current.'</span>
                            </div>
                            <h3 class="product_name"><a href="'.$product['slug'].'" title="'.$product['name'].'">'.$product['name'].' </a></h3>
                        </figcaption>
                    </figure>
                </article>
            </div>';
            }

            $txt .= '
            </div>
            </div>
            </section>
            
            </div>';
        }elseif($page == 'latest'){
            $txt = '<div id="new-arrival" style="background: #f2f4f8;">
            <section class="product_area">
            <div class="container">
            <div class="row">
            
            <div class="small_product_area">
            <div class="section_title">
            <h2><span>My New Arrivals </span></h2>
            </div>
            <div class="row">';
            $latests = ProductRundown::select('products.id','name','subtitle','slug','buying_price','discount_price','regular_price',
            'special_price','offer_price','price_highlight','call_for_price','product_image_small',
            'image','image_des','image_alt','status','sku','discount','stock_status')
            ->join('products', 'product_rundowns.product_id', '=', 'products.id')
            ->orderBy('seq', 'ASC')
            ->take(12)
            ->get();
            foreach($latests as $product){
                $price_old = 0;
                $price_current = 0;
                if (!empty($product->offer_price && $product->special_price)){
                    $price_old = $product->special_price;
                    $price_current = $product->offer_price;
                }elseif(!empty($product->regular_price && $product->special_price)){
                    $price_old = $product->regular_price;
                    $price_current = $product->special_price;
                }elseif(!empty( $product->regular_price)){
                    $price_current = $product->regular_price;
                }
                $txt .= '
                    
                <div class="col-md-3">
                    <div>
                        <article class="single_product" style="width: 100%;display: inline-block;">
                            <figure>
                                <div class="product_thumb" style=" width: 27%;float: left;">
                                <a class="primary_img" href="'.$product['slug'].'" tabindex="0"><img src="'.asset($product['image']).'" alt="'.$product['name'].'" title="'.$product['name'].'"></a>
                                </div>
                                
                                <figcaption class="product_content" style=" width: 73%;">
                                <div class="price_box">
                                <span style="color: red"><del> ৳ '.$price_old.' </del></span>
                                <span class="current_price">৳ '.$price_current.'</span>
                                </div>
                                <h3 class="product_name"><a href="'.$product['slug'].'" tabindex="0" title="'.$product['name'].'">
                                
                                '.$product['name'].'
                                </a></h3>
                                </figcaption>
                            </figure>
                        </article>
                    </div>
                </div>
                    
                
                ';
            }
            $txt .= '</div>
            </div>
            </div>
            </div>
            <div style="text-align:center;"> <a class="btn btn-sm text-white" style="background:#0063d1;" href="latestproducts" title="new arrival">View All</a></div>
            </section>
            
            </div>';
        }elseif($page == 'brand'){

        }else{
            dd('Go back! something going wrong!!');
        }

        $myfile = fopen("resources/views/html/".$page.".blade.php", "w") or die("Unable to open file!");
        $txt .= '</ul></nav>';
        fwrite($myfile, $txt);
        fclose($myfile);
        print_r($page.' generated!');
        dd('Go back! this is end of generator!!');
    }

    public function index()
    {
        $user_id = Auth::user();
        if($user_id){
            $brands = Brand::select('id','name','slug','status')->orderBy('id', 'desc')->get();
            $category1 = Category::orderBy('id', 'desc')->get();
            $category2 = Subcategory::orderBy('id', 'desc')->get();
            $category3 = Prosubcategory::orderBy('id', 'desc')->get();
            $category4 = Proprocategory::orderBy('id', 'desc')->get();
            $products = Product::select('id','name','slug','status')->orderBy('id', 'desc')->get();
            $posts = Post::select('id','slug','status')->orderBy('id', 'desc')->get();
            
            foreach($brands as $link){
                $landing = Landing::where('pagelink', $link->slug)->first();
                if(!empty( $landing )){$pagelink = $link->slug.'-brand-'.$link->id;}else{$pagelink = $link->slug;}
                if($link->status == 1){$statuscode = 200;}else{$statuscode = 404;}
                $data = new Landing();
                $data->linktype=7;$data->pagelink=$pagelink;$data->statuscode=$statuscode;
                $data->save();
            }

            foreach($category1 as $link){
                $landing = Landing::where('pagelink', $link->slug)->first();
                if(!empty( $landing )){$pagelink = $link->slug.'-c1-'.$link->id;}else{$pagelink = $link->slug;}
                if($link->status == 1){$statuscode = 200;}else{$statuscode = 404;}
                $data = new Landing();
                $data->linktype=3;$data->pagelink=$pagelink;$data->statuscode=$statuscode;
                $data->save();
            }
            foreach($category2 as $link){
                $landing = Landing::where('pagelink', $link->slug)->first();
                if(!empty( $landing )){$pagelink = $link->slug.'-c2-'.$link->id;}else{$pagelink = $link->slug;}
                if($link->status == 1){$statuscode = 200;}else{$statuscode = 404;}
                $data = new Landing();
                $data->linktype=3;$data->pagelink=$pagelink;$data->statuscode=$statuscode;
                $data->save();
            }
            foreach($category3 as $link){
                $landing = Landing::where('pagelink', $link->slug)->first();
                if(!empty( $landing )){$pagelink = $link->slug.'-c3-'.$link->id;}else{$pagelink = $link->slug;}
                if($link->status == 1){$statuscode = 200;}else{$statuscode = 404;}
                $data = new Landing();
                $data->linktype=3;$data->pagelink=$pagelink;$data->statuscode=$statuscode;
                $data->save();
            }
            foreach($category4 as $link){
                $landing = Landing::where('pagelink', $link->slug)->first();
                if(!empty( $landing )){$pagelink = $link->slug.'-c4-'.$link->id;}else{$pagelink = $link->slug;}
                if($link->status == 1){$statuscode = 200;}else{$statuscode = 404;}
                $data = new Landing();
                $data->linktype=3;$data->pagelink=$pagelink;$data->statuscode=$statuscode;
                $data->save();
            }
            foreach($products as $link){
                $landing = Landing::where('pagelink', $link->slug)->first();
                if(!empty( $landing )){$pagelink = $link->slug.'-p-'.$link->id;}else{$pagelink = $link->slug;}
                if($link->status == 1){$statuscode = 200;}else{$statuscode = 404;}
                $data = new Landing();
                $data->linktype=2;$data->pagelink=$pagelink;$data->statuscode=$statuscode;
                $data->save();
            }
            foreach($posts as $link){
                $landing = Landing::where('pagelink', $link->slug)->first();
                if(!empty( $landing )){$pagelink = $link->slug.'-post-'.$link->id;}else{$pagelink = $link->slug;}
                if($link->status == 1){$statuscode = 200;}else{$statuscode = 404;}
                $data = new Landing();
                $data->linktype=7;$data->pagelink=$pagelink;$data->statuscode=$statuscode;
                $data->save();
            }
            // end machanism

            $landings = Landing::orderBy('id', 'desc')->get();
            return view('backend.admin.landing.index', compact('landings'));
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
            'link' => 'required',
            'title' => 'required',
            'status' => 'required',
        ]);


        $data = new Popup();
        $data->title = $request->title;
        $data->link = $request->link;
        $imaged = $request->file('image_d');
        $imagem = $request->file('image_m');
        
        $data->status = $request->status;
        $data->save();

        if($imaged)
        {
            $image_full_name = $data->id.'-'.$imaged->getClientOriginalName();
            $upload_path = 'images/popup/';
            $image_url = $upload_path.$image_full_name;
            $success = $imaged->move($upload_path, $image_full_name);
            $imaged = Image::make($upload_path. $image_full_name);
            $imaged->save($upload_path .'/desktop-popup-'. $image_full_name);
        }

        if($imagem)
        {
            //$image_full_name = $data->id.'.png';
            $upload_path = 'images/popup/';
            $image_url = $upload_path.$image_full_name;
            $success = $imagem->move($upload_path, $image_full_name);
            $imagem = Image::make($upload_path. $image_full_name);
            $imagem->save($upload_path .'/mobile-popup-'. $image_full_name);
        }

        $data->image = $image_full_name;
        $data->id = $data->id;
        $data->save();

        $notification = array(
            'message' => 'Successfully Done',
            'alert-type' => 'success'
        );

        //$banners = Banner::where('status',1)->orderBy('id', 'DESC')->take(5)->get();
        //Cache::put('banners', json_encode($banners), $seconds = 10000000000);

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
            $landing = Landing::findorfail($id);
            return view('backend.admin.landing.edit', compact('landing'));
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
        $data = Landing::find($id);
        $data->linktype = $request->linktype;
        $data->pagelink = $request->pagelink;
        $data->nextpagelink = $request->nextpagelink;
        $data->statuscode = $request->statuscode;
        $data->updated_at = date('Y-m-d H:i:s');

        $data->save();
        $notification=array(
            'message' => 'Successfully Done',
            'alert-type' => 'success'
        );
        // $banners = Banner::where('status',1)->orderBy('id', 'DESC')->take(5)->get();
        // Cache::put('banners', json_encode($banners), $seconds = 10000000000);
        return redirect('/admin/landing')->with($notification);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $imagePath = Banner::select('image')->where('id', $id)->first();
        $filePath = $imagePath->image;
        if (file_exists($filePath)) {
            unlink($filePath);
            Banner::where('id', $id)->delete();
        }else{
            Banner::where('id', $id)->delete();
        }
        
        $notification=array(
            'message' => 'Popup Deleted Successfully !!',
            'alert-type' => 'error'
        );
        
        return redirect()->back()->with($notification);
    }
}
