<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Result extends Model
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
    protected $fillable = ['name','phone','email','platform','user_id','quiz_id','totalmark','obtainedmark','negetivepq','timetaken','start_at','end_at','questiontotal','correctanswer','wronganswer','noanswer','answersheet','identification'];

    // Connecting users to Chirps
    // public function user()
    // {
    //     return $this->belongsTo(User::class);
    // }
}
