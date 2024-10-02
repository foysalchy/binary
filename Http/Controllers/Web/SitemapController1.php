<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Post;
use App\Models\Category;
use App\Models\Proprocategory;
use App\Models\Subcategory;
use App\Models\Prosubcategory;
use App\Models\Brand;
use App\Models\Banner;
use App\Models\Component;

class SitemapController extends Controller
{
    public function index(Request $r)
    {
       
       	$website = 'www.binarylogic.com.bd';
       	$pc_builder = 'http://binarylogic.com.bd/binarylogic/tools/pc_builder';
       	$cutomerLogin = 'https://binarylogic.com.bd/customerLogin';
       	$customerRegister = 'https://binarylogic.com.bd/customerRegister';
       	$cart = 'https://binarylogic.com.bd/cart';


     	$categories = Category::orderBy('id','desc')->get();
     	$subcategories = Subcategory::orderBy('id','desc')->get();
     	$pro_sub_cats = Prosubcategory::orderBy('id','desc')->get();
     	$pro_pro_sub_cats = Proprocategory::orderBy('id','desc')->get();
     	$brands = Brand::orderBy('id','desc')->get();
     	$banners = Banner::orderBy('id','desc')->get();

     	$posts = Post::orderBy('id','desc')->get();
     	$components = Component::orderBy('id','desc')->get();

     	$products = Product::orderBy('id','desc')->where('status', 1)->get();


      	return response()->view('sitemap', [
          	'categories' => $categories,
          	'subcategories' => $subcategories,
          	'pro_sub_cats' => $pro_sub_cats,
          	'pro_pro_sub_cats' => $pro_pro_sub_cats,
          	'brands' => $brands,
          	'banners' => $banners,
          	'posts' => $posts,
          	'components' => $components,
          	'products' => $products,

      	])->header('Content-Type', 'text/xml');
      

    }

    public function feed()
    {


       	$website = 'www.binarylogic.com.bd';
       	$pc_builder = 'http://binarylogic.com.bd/binarylogic/tools/pc_builder';
       	$cutomerLogin = 'https://binarylogic.com.bd/customerLogin';
       	$customerRegister = 'https://binarylogic.com.bd/customerRegister';
       	$cart = 'https://binarylogic.com.bd/cart';


     	$categories = Category::orderBy('id','desc')->get();
     	$subcategories = Subcategory::orderBy('id','desc')->get();
     	$pro_sub_cats = Prosubcategory::orderBy('id','desc')->get();
     	$pro_pro_sub_cats = Proprocategory::orderBy('id','desc')->get();
     	$brands = Brand::orderBy('id','desc')->get();
     	$banners = Banner::orderBy('id','desc')->get();

     	$posts = Post::orderBy('id','desc')->get();
     	$components = Component::orderBy('id','desc')->get();

     	$products = Product::orderBy('id','desc')->where('status', 1)->get();


      	return response()->view('rss', [
          	'categories' => $categories,
          	'subcategories' => $subcategories,
          	'pro_sub_cats' => $pro_sub_cats,
          	'pro_pro_sub_cats' => $pro_pro_sub_cats,
          	'brands' => $brands,
          	'banners' => $banners,
          	'posts' => $posts,
          	'components' => $components,
          	'products' => $products,

      	])->header('Content-Type', 'application/xml');

    }
    
    public function robots()
    {
        return response(view('robots'))->header('Content-Type', 'text/plain');
    }    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
}
