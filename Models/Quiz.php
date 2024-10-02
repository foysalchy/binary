<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
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
    protected $fillable = ['quiztype','name','title','slug','description','image','status','start_at','end_at'];

    // Connecting users to Chirps
    public function user()
    {
        return $this->belongsTo(User::class);
    }
      
    public function question(){
        return $this->belongsToMany(Question::class, 'quiz_question');
    }
}
