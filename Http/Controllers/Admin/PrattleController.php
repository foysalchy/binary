<?php

namespace App\Http\Controllers;

use App\Models\Prattle;
use Illuminate\Http\Request;
// use Inertia\Inertia;

class PrattleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // return 'Hello Prattle, Welcome World!';
        // return Inertia::render('Prattles/Index', [
        //     //
        // ]);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:255',
            'chirp_id' => 'required|exists:chirps,id',
        ]);
 
        $request->user()->prattles()->create($validated);
 
        return back()->with('success','The Prattle saved successfully!');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Prattle  $prattle
     * @return \Illuminate\Http\Response
     */
    public function show(Prattle $prattle)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Prattle  $prattle
     * @return \Illuminate\Http\Response
     */
    public function edit(Prattle $prattle)
    {
        $this->authorize('update', $prattle);
 
        return view('prattles.edit', [
            'prattle' => $prattle,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Prattle  $prattle
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Prattle $prattle)
    {
        $this->authorize('update', $prattle);
 
        $validated = $request->validate([
            'message' => 'required|string|max:255',
        ]);
 
        $prattle->update($validated);
 
        return back()->with('success','The Prattle updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Prattle  $prattle
     * @return \Illuminate\Http\Response
     */
    public function destroy(Prattle $prattle)
    {
        $this->authorize('delete', $prattle);
 
        $prattle->delete();
 
        // return redirect(route('chirps.show',$prattle->chirp_id));
        return back()->with('success','The Prattle deleted successfully!');
    }
}
