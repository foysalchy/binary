<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Result extends Model
{
    use HasFactory;
    protected $fillable = ['name','phone','email','platform','user_id','quiz_id','totalmark','obtainedmark','negetivepq','timetaken','start_at','end_at','questiontotal','correctanswer','wronganswer','noanswer','answersheet','identification'];

    // Connecting users to Chirps
    // public function user()
    // {
    //     return $this->belongsTo(User::class);
    // }
}
