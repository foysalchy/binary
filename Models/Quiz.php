<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    use HasFactory;
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
