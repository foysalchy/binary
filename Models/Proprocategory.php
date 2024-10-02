<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proprocategory extends Model
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
    public function subcategory()
    {
        return $this->belongsTo(Subcategory::class);
    }
    public function prosubcategory()
    {
        return $this->belongsTo('App\Models\Prosubcategory', 'pro_sub_category_id');
    }


}
