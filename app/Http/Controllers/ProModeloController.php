<?php

namespace App\Http\Controllers;

use App\Models\ProModelo;
use Illuminate\Http\Request;

class ProModeloController extends Controller
{
 
    public function index()
    {
        return view('modelos.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(ProModelo $proModelo)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ProModelo $proModelo)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProModelo $proModelo)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProModelo $proModelo)
    {
        //
    }
}
