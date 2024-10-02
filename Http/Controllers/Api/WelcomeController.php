<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Post;

class WelcomeController extends Controller
{
    public function post_details($slug)
    {
        $post = Post::where('slug', $slug)->first();
        return response()->json([
            'success' => true,
            'message' => '',
            'data' => $post,
        ]);
    }
}
