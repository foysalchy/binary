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
       
       	$website = 'https://www.binarylogic.com.bd';
       	$pc_builder = 'http://www.binarylogic.com.bd/binarylogic/tools/pc_builder';
       	$cutomerLogin = 'https://www.binarylogic.com.bd/customerLogin';
       	$customerRegister = 'https://www.binarylogic.com.bd/customerRegister';
       	$cart = 'https://www.binarylogic.com.bd/cart';


     	$categories = Category::orderBy('id','desc')->where('status',1)->get();
     	$subcategories = Subcategory::orderBy('id','desc')->where('status',1)->get();
     	$pro_sub_cats = Prosubcategory::orderBy('id','desc')->where('status',1)->get();
     	$pro_pro_sub_cats = Proprocategory::orderBy('id','desc')->where('status',1)->get();
     	$brands = Brand::orderBy('id','desc')->where('status',1)->get();
     	$banners = Banner::orderBy('id','desc')->where('status',1)->get();

     	$posts = Post::orderBy('id','desc')->where('status',1)->get();
     	$components = Component::orderBy('id','desc')->where('status',1)->get();
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

	public function imagesitemap(Request $r)
    {
       
       	$website = 'https://www.binarylogic.com.bd';
       	$pc_builder = 'http://www.binarylogic.com.bd/binarylogic/tools/pc_builder';
       	$cutomerLogin = 'https://www.binarylogic.com.bd/customerLogin';
       	$customerRegister = 'https://www.binarylogic.com.bd/customerRegister';
       	$cart = 'https://www.binarylogic.com.bd/cart';

     	$categories = Category::orderBy('id','desc')->where('status',1)->get();
     	$subcategories = Subcategory::orderBy('id','desc')->where('status',1)->get();
     	$pro_sub_cats = Prosubcategory::orderBy('id','desc')->where('status',1)->get();
     	$pro_pro_sub_cats = Proprocategory::orderBy('id','desc')->where('status',1)->get();
     	$product_image = Product::orderBy('id','desc')->where('status',1)->select(['id','name','slug','image','image_alt','image_des'])->get();
     	$brands = Brand::orderBy('id','desc')->where('status',1)->get();
     	$banners = Banner::orderBy('id','desc')->where('status',1)->get();
     	$posts = Post::orderBy('id','desc')->get();
     	$components = Component::orderBy('id','desc')->get();

      	return response()->view('imageSitemap', [
          	'categories' => $categories,
          	'subcategories' => $subcategories,
          	'pro_sub_cats' => $pro_sub_cats,
          	'pro_pro_sub_cats' => $pro_pro_sub_cats,
          	'brands' => $brands,
          	'banners' => $banners,
          	'posts' => $posts,
          	'components' => $components,
          	'product_image' => $product_image
      	])->header('Content-Type', 'text/xml');
    }
	
	public function imagesitemapproductone(Request $r)
    {
       
       	$website = 'https://www.binarylogic.com.bd';
       	$pc_builder = 'http://www.binarylogic.com.bd/binarylogic/tools/pc_builder';
       	$cutomerLogin = 'https://www.binarylogic.com.bd/customerLogin';
       	$customerRegister = 'https://www.binarylogic.com.bd/customerRegister';
       	$cart = 'https://www.binarylogic.com.bd/cart';

     	$products = Product::orderBy('products.id','desc')
			->select('products.id','products.name','products.slug','products.image','products.image_alt','products.image_des')
			->where('status', 1)
			->get();

      	return response()->view('imageSitemapProductOne', [
        
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
