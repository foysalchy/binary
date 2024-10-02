<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
	use HasFactory;
	// Automatically apply input sanitization when creating or updating the model
 public static function boot()
 {
	parent::boot();
 
	// Hook into the 'creating' and 'updating' events to sanitize data
	static::creating(function ($model) {
	    $model->sanitizeAttributes();
	});
 
	static::updating(function ($model) {
	    $model->sanitizeAttributes();
	});
 }
 
 // Sanitize all attributes, excluding non-string attributes like files
 public function sanitizeAttributes()
 {
	foreach ($this->attributes as $key => $value) {
	    // Check if the attribute is a string
	    if (is_string($value)) {
		   $this->attributes[$key] = sanitizeInput($value);
	    }
	}
 }



    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    
    
	public function user(){
		return $this->belongsTo('App\Models\User', 'user_id');
	}  

	public function subcategory(){
		return $this->belongsTo('App\Models\Subcategory', 'sub_category_id');
	}
	public function prosubcategory(){
		return $this->belongsTo('App\Models\Prosubcategory', 'pro_sub_category_id');
	}
	public function proprocategory(){
		return $this->belongsTo('App\Models\Proprocategory', 'pro_pro_category_id');
	}
	public function component(){
		return $this->belongsTo('App\Models\Component', 'component_id');
	}
	public function ProductSocial()
	{
	return $this->hasOne('App\ProductSocial');
	}

	public function compatibleIdsToArray() {
		if (empty($this->compatible_product_ids)) return [];
		return json_decode($this->compatible_product_ids);
	}

	public static function getSearchedProducts($name = null){
		return Product::whereRaw(
			"MATCH(name) AGAINST(?)", 
			array($name)
			)->get();
		/*return Product::where('name', 'LIKE', '%' . $search_name . '%')
			->where('status', 1)
			->select(['products.id','name','subtitle','slug','buying_price','discount_price','regular_price',
			'special_price','offer_price','price_highlight','call_for_price','product_image_small',
			'image','image_des','image_alt','status','sku','discount'])
			->orderBy('id', 'DESC');*/
	}

}
