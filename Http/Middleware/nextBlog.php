<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class nextBlog
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // return $next($request)
        // ->header->set('Access-Control-Allow-Origin', '*')
        // ->header->set('Access-Control-Allow-Methods', 'PUT, POST, DELETE, GET, OPTIONS')
        // ->header->set('Access-Control-Allow-Headers', 'Accpet, Authorization, Content-Type');


        $response = $next($request);

        $response->headers->set('Access-Control-Allow-Origin' , '*');
        $response->headers->set('Access-Control-Allow-Methods', 'POST, GET, OPTIONS, PUT, DELETE');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Accept, Authorization, X-Requested-With, Application');

        return $response;
        
    }
}
