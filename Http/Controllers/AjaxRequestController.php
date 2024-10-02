<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductHighlight;
use App\Models\Subcategory;
use App\Models\Prosubcategory;
use App\Models\Proprocategory;
use Cache;

class AjaxRequestController extends Controller
{
    public function getSubcategory(Request $request){
        
        $subcategories = Subcategory::where('category_id', $request->category_id)->get();
        return view('backend.admin.ajaxrequest.get_subcategory',compact('subcategories'));
    }
    public function getProSubCategory(Request $request){
        
        $prosubcategories = Prosubcategory::where('subcategory_id', $request->sub_category_id)->get();
        return view('backend.admin.ajaxrequest.getProSubCategory',compact('prosubcategories'));
    }
    public function getProProCategory(Request $request){
        
        $proprocategories = Proprocategory::where('pro_sub_category_id', $request->pro_sub_category_id)->get();
        return view('backend.admin.ajaxrequest.getProProCategory',compact('proprocategories'));
    }

    public function filterProduct(Request $request){ 
        // $filterOptions = $request->post()->data['stockAvailability'];
        $input = $request->all();
        $input_json = json_encode($input);
        $matchThese = [];

        $tableName = $input['tableName'];
        $tableValue = $input['tableValue'];

        $product_stock = "";
        $product_stock_count = "";
        $products = [];

        // stock filter
        if(!empty($input['stock'])){
            $product_stock = $input['stock'];
            $products = Product::where($tableName,$tableValue)
                ->where('status',1)
                ->where(function ($query) use ($product_stock) {
                    $query->where('stock_status', "=", $product_stock[0]);
                    if(sizeof($product_stock)>1){
                        for($i=0;$i<sizeof($product_stock);$i++){
                            $query->orWhere('stock_status', "=", $product_stock[$i]);
                        }
                    }
                })
                ->select('products.id','name','slug','regular_price','special_price','offer_price','call_for_price','stock_status','image','status','products.created_at')
                ->orderBy('created_at','DESC')
                ->get(); 
        }else{
            $products = Product::where($tableName,$tableValue)
                ->where('status',1)
                ->select('products.id','name','slug','regular_price','special_price','offer_price','call_for_price','stock_status','image','products.status','products.created_at')
                ->orderBy('stock_status','asc')
                ->get();
        }
        
        
        // for($i=0;$i<$product_stock_count;$i++){
        //     $stock_condition = ['stock_status'=>$product_stock[$i]];
        //     $matchThese = array_merge($matchThese,$stock_condition);
        // }
        // $matchThese = ['stock_status' => 'value', 'another_field' => 'another_value'];
        // $products = Product::where($tableName,$tableValue)
        //     ->where('status',1)
        //     ->where($matchThese)
        //     ->get();
        
        

        return view('frontend.ajaxFilter.filterProducts',compact('input_json','products','product_stock_count','product_stock'));
        // $stockAvailability = $input['stockAvailability'];
        // $categoryId = $input['categoryId'];
        
        //dd($filterOptions);
        // if ($stockAvailability == "in_stock") {
        //     $products = Product::where('category_id', $categoryId)->where('stock_status', 'in_stock')->where('status', 1)->orderBy('special_price','asc')->paginate(12);
        //     // dd($products);
        // }
        // elseif ($stockAvailability == "coming") {
        //     $products = Product::where('category_id', $categoryId)->where('stock_status', 'coming')->where('status', 1)->paginate(12);
        //     // dd($products);
        // }
        // elseif ($stockAvailability == "new_arrived") {
        //     $products = Product::where('category_id', $categoryId)->where('stock_status', 'new_arrived')->where('status', 1)->paginate(12);
        //     // dd($products);
        // }
        // elseif ($stockAvailability == "preorder") {
        //     $products = Product::where('category_id', $categoryId)->where('stock_status', 'preorder')->where('status', 1)->paginate(12);
        //     // dd($products);
        // }

        // return view('frontend.ajaxFilter.filterProducts',compact('input_json'));
    }

}
