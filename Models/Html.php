<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Auth;
use App\Models\Page;
use Illuminate\Support\Str;
use DB;

class Html extends Model
{
    use HasFactory;
    

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
        // <li><a title="Binary logic offers" href="https://www.binarylogic.com.bd/offer">Offer</a></li>
        if($page == 'menu'){
            $txt = '<nav><ul><li><a title="Binary logic brands" href="https://www.binarylogic.com.bd/brands">Our Brands</a></li>';
            $categories = Category::where('status', 1)->orderBy('position_id','ASC')->select('id','name','slug')->get(); 
            foreach($categories as $category){
                $subCategories = Subcategory::where('category_id', $category['id'])->where('status', 1)->select('id','name','slug')->get();
                if(!empty($subCategories) && sizeof($subCategories)>0){$txt_angle = '<svg width="18px" height="18px" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <g>
                        <path fill="none" d="M0 0h24v24H0z"/>
                        <path d="M12 13.172l4.95-4.95 1.414 1.414L12 16 5.636 9.636 7.05 8.222z"/>
                    </g>
                </svg>';}else{$txt_angle = '';}
                $txt .= '<li><a title="'.$category['name'].'" class= "main_menu_new" href="'.'https://www.binarylogic.com.bd/'.$category['slug'].'">'.$category['name'].$txt_angle.'</a>';
                if(!empty($subCategories) && sizeof($subCategories)>0){
                    $txt .= '<ul class="sub_menu">';
                    foreach($subCategories as $category2){
                        $proSubCategories = Prosubcategory::where('subcategory_id', $category2['id'])->where('status', 1)->select('id','name','slug')->get();
                        if(!empty($proSubCategories) && sizeof($proSubCategories)>0){$txt_angle = '<svg width="20px" height="20px" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <g>
                                <path fill="none" d="M0 0h24v24H0z"/>
                                <path d="M13.172 12l-4.95-4.95 1.414-1.414L16 12l-6.364 6.364-1.414-1.414z"/>
                            </g>
                        </svg>';}else{$txt_angle = '';}
                        $txt .= '<li><a href="'.'https://www.binarylogic.com.bd/'.$category2['slug'].'">'.$category2['name'].$txt_angle.'</a>';
                        if(!empty($proSubCategories) && sizeof($proSubCategories)>0){
                            $txt .= '<ul>';
                            foreach($proSubCategories as $category3){
                                $proproCategories = Proprocategory::where('pro_sub_category_id', $category3['id'])->where('status', 1)->select('id','name','slug')->get();
                                if(!empty($proproCategories) && sizeof($proproCategories)>0){$txt_angle = '<svg width="20px" height="20px" viewBox="0 0 24 24" id="_24x24_On_Light_Arrow-Left" data-name="24x24/On Light/Arrow-Left" xmlns="http://www.w3.org/2000/svg">
                                    <rect id="view-box" width="24" height="24" fill="none"/>
                                    <path id="Shape" d="M.22,10.22A.75.75,0,0,0,1.28,11.28l5-5a.75.75,0,0,0,0-1.061l-5-5A.75.75,0,0,0,.22,1.28l4.47,4.47Z" transform="translate(14.75 17.75) rotate(180)" fill="#141124"/>
                                  </svg>';}else{$txt_angle = '';}
                                $txt .= '<li><a href="'.'https://www.binarylogic.com.bd/'.$category3['slug'].'">'.$category3['name'].$txt_angle.'</a>';
                                if(!empty($proproCategories) && sizeof($proproCategories)>0){
                                    $txt .= '<ul>';
                                    foreach($proproCategories as $category4){
                                        $txt .= '<li><a href="'.'https://www.binarylogic.com.bd/'.$category4['slug'].'">'.$category4['name'].'</a></li>';
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
        }
  
        
        
        elseif($page == 'category'){
            $shops = ShopType::where('status',1)->get();
            $txt = '
            <section class="container"><h2 class="text-center mb-4">FEATURED CATEGORY</h2><ul class="row">';
            foreach($shops as $shop){
                $img = explode('/',$shop->image);
                $img[2] = 'thumb-'.$img[2];
                $img_arr_s = $img[0].'/'.$img[1].'/'.$img[2];

                $txt .= '
                    <li class="col-md-3 col-6 mb-2 text-center">
                        <a class="single-cate with-title" href="'.$shop->slug.'">
                        <strong class="font-weight-bold d-inline-block fbitx">'.$shop->title.'</strong>
                        <picture class="float-end" style="max-width:60px">
                        <img class="card-img-top px-1 p-sm-2" src="'.asset($shop->image).'" alt="binary '.$shop->title.'">
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
        }
        
        elseif($page == 'feature'){
           $products = FeatureProduct::select('products.id','name','subtitle','slug','buying_price','discount_price','regular_price',
            'special_price','offer_price','price_highlight','call_for_price','product_image_small',
            'image','image_des','image_alt','products.status','sku','discount','stock_status')
            ->join('products', 'feature_products.product_id', '=', 'products.id')
            ->orderBy('seq', 'ASC')
            ->where('products.stock_status', '=', 'in_stock')
            ->take(12)
            ->get();
            if($products->count() > 0){
            $txt = '<div id="feature-product">
            <section class="featured_product_area">
            <div class="container">
            <div class="row">
                <div class="col-6 col-md-6">
                    <div class="section_title">
                        <a href="offer">
                            <h2>Latest Offer</h2>
                        </a>
                    </div>
                </div>
                <div class="col-6 col-md-6">
                    <div style="text-align:right;"> <a class="btn btn-sm text-white" style="background:#0063d1;" href="offer" title="offer">View All Offer</a></div>
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
                $thumb_image = DB::table('product_images')->where('product_id',$product->id)->first();
               //dd($thumb_image);
                $new_images = $thumb_image->product_image_thumb ?? $product->image;
                $txt .= '<div class="col-6 col-md-4">
                <article class="single_product">
                    <figure>
                        <div class="product_thumb cls_image_l">
                            <a class="primary_img" href="'.$product['slug'].'" title="'.$product['name'].'">
                                <img loading="lazy" src="'.asset($new_images).'" alt="'.$product['name'].'" title="'.$product['name'].'">
                            </a>
                        </div>
                        <figcaption class="product_content">
                            <div class="price_box box_1">
                                <span class="old_price text-danger">৳ '.number_format($price_old).'</span>
                                <span class="current_price">৳ '.number_format($price_current).'</span>
                            </div>';
                          $txt .=  '<h3 class="product_name"><a href="'.$product['slug'].'" title="'.$product['name'].'">'.Str::limit($product->name, 45).' </a></h3>';
                          $txt .= '</figcaption>';
                          $txt .= '</figure>';
                          $txt .=' </article>';
                          $txt .= '</div>';
            }
            $txt .= '
            </div>
            </div>
            </section>
            </div>';
        
            }
         else{
             $txt = '';
         }
        }
        
        elseif($page =='upcoming'){
            $products = DB::table('upcoming')->select('products.id','name','subtitle','slug','buying_price','discount_price','regular_price',
            'special_price','offer_price','price_highlight','call_for_price','product_image_small',
            'image','product_image_thumb','image_des','image_alt','sku','discount','stock_status')
            ->join('products', 'upcoming.product_id', '=', 'products.id')
            ->orderBy('seq', 'ASC')
            ->get();
             if($products->count() > 0){
                $txt = '<div class="container upcoming_section">';
                $txt.= '<div class="section_title">
                <h2><span>Upcoming Products</span></h2>
                </div>';
                $txt.= '<div class="p-items-wrap-up">';
                  foreach($products as $product){    
                    $txt.= '<div class="p-item-up">
                    <div class="p-item-inner">
                            <div class="p-item-img"> <a href="'.$product->slug.'"><img loading="lazy" class="cls_image_up" src="'.asset($product->product_image_small).'" alt="'.$product->name.'" width="228" height="228"></a></div>
                            <div class="p-item-details">
                                <h3 class="p-item-name">
                                    <a href="'.$product->slug.'">'.$product->name.'</a></h4>';                     
                    $txt.=  '</div>';
                    $txt.=  '</div>';
                    $txt.=  '</div>';
                 }
             $txt.='</div>';
             $txt.= '</div>';
         }
         else{
               $txt = '';
         }
        }
        
        elseif($page == 'latest'){
            $txt = '<div id="new-arrival" style="background: #f2f4f8;">
            <section class="product_area">
            <div class="container">
            <div class="row">
            <div class="col-6 col-md-6 mb-4">
                <div class="section_title">
                    <a href="latestproducts">
                        <h2>New Arrivals</h2>
                    </a>
                </div>
            </div>
    
            <div class="col-6 col-md-6 mb-4">
                <div style="text-align:right;"> <a class="btn btn-sm text-white" style="background:#0063d1;" href="latestproducts" title="new arrival">View All</a></div>
            </div>
            <div class="small_product_area">
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
                //dd($product->id);
                $thumb_image = DB::table('product_images')->where('product_id',$product->id)->first();
             //   dd($thumb_image);
               $new_images = $thumb_image->product_image_thumb ?? $product->image;
                $txt .= '
                    
                <div class="col-md-3">
                    <div>
                        <article class="arrival_product">
                            <figure>
                                <div class="arrival_thumb">
                                <a class="primary_img" href="'.$product['slug'].'" tabindex="0"><img loading="lazy"  src="'.asset($new_images).'" alt="'.$product['name'].'" title="'.$product['name'].'"></a>
                                </div>
                                
                                <figcaption class="arrival_content">
                                <div class="price_box">
                                <span style="color: #dc3545"><del> ৳ '.number_format($price_old).' </del></span>
                                <span class="current_price">৳ '.number_format($price_current).'</span>
                                </div>
                                <h3 class="product_name"><a href="'.$product['slug'].'" tabindex="0" title="'.$product['name'].'">
                                
                                '.Str::limit($product->name, 45).'
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
            </section>
            
            </div>';
        }
        elseif($page == 'mobile_menu'){
            $txt = '<div class="container">';
            $txt .='<div id="menu" class="text-left ">'; 
            $txt .='<ul class="offcanvas_main_menu">'; 
            $txt .='<li class="menu-item-has-children active">
                      <a href="https://www.binarylogic.com.bd">Home</a>
                     </li>'; 
            
                               
            $categories = Category::where('status', 1)->orderBy('position_id','ASC')->select('id','name','slug')->get(); 
            foreach($categories as $category){
                $subCategories = Subcategory::where('category_id', $category['id'])->where('status', 1)->select('id','name','slug')->get();
                $txt .= '<li class="menu-item-has-children">';
                $txt .= '<a href="'.$category['slug'].'">'.$category['name'].'</a>';
                $txt .= '<ul class="sub-menu">';
                    foreach($subCategories as $sub_category){
                        $proSubCategories = Prosubcategory::where('subcategory_id', $sub_category->id)->where('status', 1)->get();
                        $txt .= '<li class="menu-item-has-children">';
                        $txt .=  '<a href="'.$sub_category['slug'].'"> '.$sub_category['name'].'</a>';
                        $txt .=  '<ul class="sub-menu">';
                            foreach($proSubCategories as $proSubCategory){
                                $txt .= '<li><a href=" '.$proSubCategory->slug.'">'.$proSubCategory->name.'</a></li>';
                            }
                            $txt .= ' </ul>';
                            $txt .= ' </li>';
                    }
                    $txt .= ' </ul>';
                    $txt .= ' </li>';

            }
            $txt .='</ul>'; 
            $txt .='</div>';
            $txt .='</div>';

        }
         elseif($page == 'footer'){
            $page_categories = PageCategory::where('status', 1)->take(4)->get();
            $siteInfo = SiteSetting::first();   
            $pages = Page::where('status', 1)->get();
            $offices = Office::where('status', 1)->get();
            $txt = '<footer class="footer_widgets">';
            $txt .= '<div class="footer_top">';
            $txt .= '<div class="container">';
            $txt .= '<div class="row">';
            $txt .= '<div class="col-lg-4 col-md-6">';
            $txt .= '<div class="widgets_container contact_us">';
            $txt .= '<div class="footer_logo">
                        <a href="https://www.binarylogic.com.bd">
                            
                            <span class="homepage_two__log">
                                <svg version="1.0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 500.000000 373.000000" preserveAspectRatio="xMidYMid meet">
                                    <g transform="translate(0.000000,373.000000) scale(0.100000,-0.100000)" fill="#0101c1" stroke="none">
                                    <path d="M0 1870 l0 -1860 1638 1 c1628 1 1636 1 1521 19 -550 88 -995 373 -1296 832 -471 717 -378 1680 221 2297 302 310 661 493 1090 556 78 11 -142  13 -1541 14 l-1633 1 0 -1860z"></path>
                                    <path d="M3081 3409 c-260 -55 -535 -201 -737 -392 -257 -243 -416 -542 -476  -891 -26 -158 -22 -427 11 -577 187 -863 1025 -1406 1881 -1219 549 119 998  530 1165 1065 228 729 -91 1508 -765 1868 -134 71 -333 139 -477 162 l-43 7 0 -586 0 -586 -217 2 -218 3 -3 583 c-2 456 -5 582 -15 581 -6 0 -54 -9 -106 -20z"></path>
                                    </g>
                                    </svg>
                                    <spam class="logo-text">Binary Logic
                                    </spam>
                            </span> 
                        </a>
                    </div>';
            
                $txt .= '<div class="footer_contact">
                <p>'.$siteInfo->name.'</p>

                <div class="d-inline-flex flex-column">
                    <div class="mb-2 w-100">
                        <button id="address-button" class="button-theme">
                            <span class="button-theme__icon"><i class="fa fa-map"></i></span>
                            <span class="button-theme__text">contact & support</span>
                        </button>
                    </div>

                    <div class="mb-2 w-100">
                        <a href="https://www.binarylogic.com.bd/direction">
                            <button class="button-theme">
                                <span class="button-theme__icon"><i class="fa fa-location-arrow"></i></span>
                                <span class="button-theme__text">
                                    Address & Directions
                                </span>
                            </button>
                        </a>
                    </div>
                </div>
                </p>
            </div>';
            $txt .= '</div>';
            $txt .= '</div>';
            $txt .= ' <div class="col-lg-2 col-md-6 col-sm-6">';
            $txt .=  '<div class="widgets_container widget_menu">';
            $txt .=   '<h3>Information</h3>';
            $txt .=   '<div class="footer_menu">';
            $txt .=          ' <ul>';
                                    foreach($pages as $page){
                                        $txt .=  '<li><a href="'.$page->slug.'">{{$page->title}}</a></li>';
                                    }
                                    $txt .=  '<li><a href="">Contact us</a></li>';
                                    $txt .=   '<li><a href="https://supporttickets.intel.com/warrantyinfo"> Check Intel CPU Warranty </a></li>';
                             $txt .=   '</ul>';
                        $txt .=    '</div>';
                      $txt .=    '</div>';
                 $txt .=    '</div>';

                 $txt .=  '<div class="col-lg-2 col-md-6 col-sm-6">';
                 $txt .=   '<div class="widgets_container widget_menu">';
                 $txt .=    '<h3>My Account</h3>';
                 $txt .=      '<div class="footer_menu">';
                 $txt .=            ' <ul>';
                 $txt .=                      '<li><a href="https://www.binarylogic.com.bd/customerLogin">Login</a></li>';
                 $txt .=                   ' <li><a href="https://www.binarylogic.com.bd/customerLogin">Register</a></li>';

                        $txt .=  '</ul>';
                        $txt .=  '</div>';
                        $txt .=  '</div>';
                        $txt .=  '</div>';





                        $txt .=  '<div class="col-lg-4 col-md-6">
                        <div class="widgets_container newsletter">
                            <h3>Follow Us</h3>
                            <div class="footer_social_link">
                                <ul>
            <li><a  class="facebook" target="_blank" href="https://www.facebook.com/binarylogic.com.bd"><i class="fa fa-facebook"></i></a></li>
            <li><a target="_blank" class="twitter" href="https://twitter.com/binarylogic_bd"><i class="fa fa-twitter"></i></a></li>
            <li><a target="_blank" class="pinterest" href="https://www.pinterest.com/binarylogicdigital"><i class="fa fa-pinterest-p"></i></a></li>
            <li><a target="_blank" class="linkedin" href="https://www.linkedin.com/company/binarylogicbd"><i class="fa fa-linkedin"></i></a></li>
                                </ul>
                            </div>
                            <div class="subscribe_form" style="display:none">
                                <h3>Join Our Newsletter Now</h3>
                                <form id="mc-form" class="mc-form footer-newsletter">
                                    <input id="mc-email" type="email" autocomplete="off"
                                        placeholder="Your email address..." />
                                    <button id="mc-submit">Subscribe!</button>
                                </form>
                                <!-- mailchimp-alerts Start -->
                                <div class="mailchimp-alerts text-centre">
                                    <div class="mailchimp-submitting"></div><!-- mailchimp-submitting end -->
                                    <div class="mailchimp-success"></div><!-- mailchimp-success end -->
                                    <div class="mailchimp-error"></div><!-- mailchimp-error end -->
                                </div><!-- mailchimp-alerts end -->
                            </div>
                        </div>
                    </div>';



                    
            $txt .= '</div>';
            $txt .= '</div>';
            $txt .= '</div>';


            $txt .= '<div class="footer_bottom">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-12 col-md-12">
                        <div class="copyright_area text-center">
                            <p class="copyright-text">&copy; 2022 Design & Developing By <a href="https://goo.gl/maps/Aa7rZ5RhCFXzqhuy7" target="_blank"><span style="color:#0063d1;">Crenotive Digital</span> </a></p>
                        </div>
                    </div>
                    
                </div>
            </div>
            <div class="container">
                <div class="footer-fixed-mobile-menu-wrapper">
                    <ul>
                   
                             <a href="https://www.binarylogic.com.bd/offer" class="animated-button1 ">
                                
                                <span></span>
                                <span></span>
                                <span></span>
                                <span></span>
                                 <b>Hot Deal</b>
                            </a>
                      
                        <li>
                            <a href="https://www.binarylogic.com.bd/tools/pc_builder">
                                <i class="fa fa-desktop" aria-hidden="true"></i>
                                <span>PC Builder</span>
                            </a>
                        </li>
                      
                        <li>
                            <a href="https://www.binarylogic.com.bd/direction">
                                <i class="fa fa-phone-square" aria-hidden="true"></i>
                                <span>Contact</span>
                            </a>
                        </li>
                        <li>   
                            <a href="https://www.binarylogic.com.bd/customerLogin">
                                <i class="fa fa-user" aria-hidden="true"></i>
                                <span>Account</span>
                            </a>

                        </li>
                    </ul>
                </div>
            </div>
        </div>';

            $txt .= ' <div id="footer-address-body" class="footer-address-model">
            <i id="footer-model-close" class="fa fa-times footer-model-close-icon" aria-hidden="true"></i>
            <div class="footer-address-tabs">
                <div class="footer_address_tab_with_content">
                    <div class="nav flex-column nav-pills me-3 footer-button-wrapper" id="v-pills-tab" role="tablist" aria-orientation="vertical">';
                            $i=1; 
                        foreach($offices as $office){
                           
                                $active = '';
                                if($i == 1){
                                    $active = 'active';
                                }
                        

                                $txt .=  '<button class="footer_tab_item '.$active.'" id="v-pills-'.$office->id.'-tab" data-bs-toggle="pill" data-bs-target="#v-pills-'.$office->id.'" type="button" role="tab" aria-controls="v-pills-'.$office->id.'" aria-selected="true"><i class="fa fa-thumb-tack" aria-hidden="true"></i>'.$office->branch_name.'</button>';

                              $i++; 
                            }
                    
                            $txt .=  '</div>';
                            $txt .=  '<div class="tab-content" id="v-pills-tabContent">';
                            $i=1; 
                        foreach($offices as $office){
      
                            $show = '';
                            $active = '';
                            if($i == 1){
                                $show = 'show';
                                $active = 'active';
                            }
                            
                            $txt .=  '<div class="tab-pane fade '.$show.' '.$active.'" id="v-pills-'.$office->id.'" role="tabpanel" aria-labelledby="v-pills-'.$office->id.'-tab">';
                            $txt .=   '<div class="footer_tab_wrapper">';
                            $txt .=   '<div class="footer_tab_content">';
                            $txt .=    '<h3>'.$office->branch_name. '</h3>';
                            $txt .=    ' <p>Technical Support: '.$office->technicale_support.' </p>';
                            $txt .=     '<p>Warranty Support: '.$office->warranty_support.' </p>';
                            $txt .=      '<p>Sales Support: '.$office->phone.' </p>';
                            $txt .=    '</div>';
                            $txt .=    '<div class="footer_tab_map">';
                            $txt .=     '<iframe src=" '.$office->iframe.' " title="binary office map" width="400" height="300" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>';
                            $txt .=   ' </div>';
                            $txt .=  '</div>';
                            $txt .=  '</div>';
                         $i++;
                        }
                        $txt .=  '</div>';
                        $txt .=  '</div>';
                        $txt .=  '</div>';
                        $txt .=  '</div>';
            $txt .= '</footer>';
        } 
        elseif($page == 'office') {
            $offices = Office::where('status', 1)->get();
            $txt = ' <div id="footer-address-body" class="footer-address-model">
           
            <svg id="footer-model-close" class=" footer-model-close-icon" aria-hidden="true" height="28px" width="28px" version="1.1" id="图层_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
            	 viewBox="0 0 40 40" enable-background="new 0 0 40 40" xml:space="preserve">
            <g>
            	<g>
            		<g>
            			<path fill="#231815" d="M25,25.5c-0.1,0-0.3,0-0.4-0.1l-10-10c-0.2-0.2-0.2-0.5,0-0.7s0.5-0.2,0.7,0l10,10c0.2,0.2,0.2,0.5,0,0.7
            				C25.3,25.5,25.1,25.5,25,25.5z"/>
            		</g>
            		<g>
            			<path fill="#231815" d="M15,25.5c-0.1,0-0.3,0-0.4-0.1c-0.2-0.2-0.2-0.5,0-0.7l10-10c0.2-0.2,0.5-0.2,0.7,0s0.2,0.5,0,0.7l-10,10
            				C15.3,25.5,15.1,25.5,15,25.5z"/>
            		</g>
            	</g>
            </g>
        </svg>
            <div class="footer-address-tabs">
                <div class="footer_address_tab_with_content">
                    <div class="nav flex-column nav-pills me-3 footer-button-wrapper" id="v-pills-tab" role="tablist" aria-orientation="vertical">';
                            $i=1; 
                        foreach($offices as $office){
                           
                                $active = '';
                                if($i == 1){
                                    $active = 'active';
                                }
                        

                                $txt .=  '<button class="footer_tab_item '.$active.'" id="v-pills-'.$office->id.'-tab" data-bs-toggle="pill" data-bs-target="#v-pills-'.$office->id.'" type="button" role="tab" aria-controls="v-pills-'.$office->id.'" aria-selected="true"><svg height="22px" width="28px" version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 463 463" xmlns:xlink="http://www.w3.org/1999/xlink" enable-background="new 0 0 463 463">
  <path d="M303.5,208h-1.151L287.582,45.568C296.571,42.265,303,33.62,303,23.5C303,10.542,292.458,0,279.5,0h-96  C170.542,0,160,10.542,160,23.5c0,10.12,6.429,18.765,15.418,22.068L160.651,208H159.5c-12.958,0-23.5,10.542-23.5,23.5  s10.542,23.5,23.5,23.5H224v200.5c0,4.143,3.357,7.5,7.5,7.5s7.5-3.357,7.5-7.5V255h64.5c12.958,0,23.5-10.542,23.5-23.5  S316.458,208,303.5,208z M183.5,15h96c4.687,0,8.5,3.813,8.5,8.5s-3.813,8.5-8.5,8.5h-96c-4.687,0-8.5-3.813-8.5-8.5  S178.813,15,183.5,15z M190.349,47h82.303l14.636,161H175.712L190.349,47z M303.5,240h-144c-4.687,0-8.5-3.813-8.5-8.5  s3.813-8.5,8.5-8.5h144c4.687,0,8.5,3.813,8.5,8.5S308.187,240,303.5,240z"/>
</svg>'.$office->branch_name.'</button>';

                              $i++; 
                            }
                    
                            $txt .=  '</div>';
                            $txt .=  '<div class="tab-content" id="v-pills-tabContent">';
                            $i=1; 
                        foreach($offices as $office){
      
                            $show = '';
                            $active = '';
                            if($i == 1){
                                $show = 'show';
                                $active = 'active';
                            }
                            
                            $txt .=  '<div class="tab-pane fade '.$show.' '.$active.'" id="v-pills-'.$office->id.'" role="tabpanel" aria-labelledby="v-pills-'.$office->id.'-tab">';
                            $txt .=   '<div class="footer_tab_wrapper">';
                            $txt .=   '<div class="footer_tab_content">';
                            $txt .=    '<h3>'.$office->branch_name. '</h3>';
                            $txt .=    ' <p>Technical Support: '.$office->technicale_support.' </p>';
                            $txt .=     '<p>Warranty Support: '.$office->warranty_support.' </p>';
                            $txt .=      '<p>Sales Support: '.$office->phone.' </p>';
                            $txt .=    '</div>';
                            $txt .=    '<div class="footer_tab_map">';
                            $txt .=     '<iframe src=" '.$office->iframe.' " title="binary office map" width="400" height="300" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>';
                            $txt .=   ' </div>';
                            $txt .=  '</div>';
                            $txt .=  '</div>';
                         $i++;
                        }
                        $txt .=  '</div>';
                        $txt .=  '</div>';
                        $txt .=  '</div>';
                        $txt .=  '</div>';
        }
        
         elseif($page == 'dynamic_page'){
            $pages = Page::where('status', 1)->get();
            $txt = ' <div class="col-lg-2 col-md-6 col-sm-6">';
            $txt .=  '<div class="widgets_container widget_menu">';
            $txt .=   '<h3>Information</h3>';
            $txt .=   '<div class="footer_menu">';
            $txt .=          ' <ul>';
                    foreach($pages as $item){
                        $txt .=  '<li><a href="https://www.binarylogic.com.bd/page/'.$item->slug.'">'.$item->title.'</a></li>';
                    }
                    $txt .=  '<li><a href="https://www.binarylogic.com.bd/contact-us">Contact us</a></li>';
                    $txt .=   '<li><a href="https://supporttickets.intel.com/warrantyinfo"> Check Intel CPU Warranty </a></li>';
                             $txt .=   '</ul>';
                        $txt .=    '</div>';
                      $txt .=    '</div>';
                 $txt .=    '</div>';
        }
        
        elseif($page == 'site_setting'){
            $SiteSetting = SiteSetting::where('status', 1)->first();
            $txt=' <title>'.$SiteSetting->meta_title ?? 'Binary Logic'.'';
            $txt .= '</title>';
            $txt .=  '<meta name="description" content="'.$SiteSetting->meta_des ?? ''.'';
            $txt .=  '"/>';
            $txt .= '<meta property="type" content="Website"/>';
            $txt .=  '<link rel="canonical" href="'.$SiteSetting->canonical ?? ''.'';
            $txt .=  '"/>';
            $txt .= '<meta name="robots" content="index,allow"/>';
            $txt .= '<meta name="author" content="Binary Logic"/>';
            $txt .= '<meta name="publisher" content="Crenotive"/>';
            $txt .= '<meta property="site_name" content="'.$SiteSetting->meta_title ?? ''.'';
            $txt .= '"/>';
            $txt .= '<meta property="og:url" content="https://www.binarylogic.com.bd/"/>';
            $txt .= '<meta property="og:type" content="website" />';
            $txt .= '<meta property="og:title" content="'.$SiteSetting->meta_title ?? ''.'';
            $txt .= '"/>';
            $txt .=  '<meta property="og:description" content="'.$SiteSetting->meta_des ?? ''.'';
            $txt .=  '"/>';
            $txt .= '<meta property="og:keywords" content="'.$SiteSetting->meta_keywords ?? ''.'';
            $txt .= '"/>';
            $txt .= '<meta property="og:image" content="https://binarylogic.com.bd/images/site_image/binary-logic.jpeg"';
            $txt .= '/>';           
            $txt .= '<meta name="twitter:card" content="summary" />';
            $txt .= '<meta name="twitter:site" content="@BinaryLogic" />';
            $txt .= '<meta name="twitter:creator" content="@BinaryLogic" />';
            $txt .=  '<meta property="twitter:url" content="https://www.binarylogic.com.bd/"/>';
            $txt .= '<meta property="twitter:title" content="'.$SiteSetting->meta_title ?? ''.'';
            $txt .= '"/>';
            $txt .= '<meta property="twitter:description" content="'.$SiteSetting->meta_des ?? ''.'';
            $txt .= '"/>';
            $txt .= '<meta property="twitter:keywords"   content="'.$SiteSetting->meta_keywords ?? ''.'';
            $txt .= '"/>';
            $txt .= '<meta property="twitter:image" content="https://binarylogic.com.bd/images/site_image/binary-logic.jpeg"';
            $txt .= '/>';
        }
        
        elseif( $page == 'phone_number'){
            $SiteSetting = SiteSetting::where('status', 1)->first();
            if(!empty($SiteSetting->phone)){
              $phone = explode(' ',$SiteSetting->phone); 
              $txt = '';                
                for($i=0; $i<sizeof($phone); $i++){
                    $txt .= '<a class="tphone" href="tel:'.$phone[$i].'"><span>'.$phone[$i].'</span></a>';
                }           
            }
        } 
        
        elseif($page == 'footer_contact') {
            $siteInfo = SiteSetting::first();
            $txt =  '<p>'.$siteInfo->name.'</p>';
        } 
        
        
        elseif($page == 'brand'){
            
        $brands = Brand::where('status',1)->select('id','name','slug','image')->get();
            $txt ='<div style="background: #f8f8f8;padding-bottom: 20px;" class="brand_area mb-70">';
            $txt .='<div class="container">     
                        <div class="brand_title">
                            <a href="https://www.binarylogic.com.bd/brands"><h3>Our Brand</h3></a>
                        </div>
                     </div>';
            $txt .=' <div class="container">';
            $txt .='  <div class="row">';
            $txt .='  <div class="col-12">';
            $txt .='  <div class="brand_container owl-carousel">';
            foreach($brands as $brand){
                $txt .= '<div class="brand_items">';
                $txt .= '<div class="single_brand">';
                $txt .= '<a href="'.$brand->slug.'"><img loading="lazy" src="'.asset($brand->image).'" alt="'.$brand->name.'"></a>';
                $txt .= '</div>';
                $txt .= '</div>';
            }
            $txt .='</div>';
            $txt .='</div>';
            $txt .='</div>';
            $txt .='</div>';
            $txt .='</div>';
            $txt .='</div>';

        }
        
        else{
            dd('Go back! something going wrong!!');
        }

        $myfile = fopen("resources/views/html/".$page.".blade.php", "w") or die("Unable to open file!");
        $txt .= '</ul></nav>';
        fwrite($myfile, $txt);
        fclose($myfile);
        print_r($page.' generated!');
        return('Go back! this is end of generator!!');
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

}
