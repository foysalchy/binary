<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;
    protected $fillable = ['questiontext','optionfirst','optionsecond','optionthird','optionfourth','bestoption','image','status','questionlabel','expiring'];

    // Connecting users to Chirps
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
