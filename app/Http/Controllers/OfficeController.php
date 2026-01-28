<?php

namespace App\Http\Controllers;

use App\Models\Office;
use Illuminate\Http\Request;

class OfficeController extends Controller
{
    //// app/Http/Controllers/Api/OfficeController.php
public function store(Request $request) {
    $request->validate([
        'office_name' => 'required',
        'latitude' => 'required',
        'longitude' => 'required',
        'radius' => 'required|integer', // Dalam meter
    ]);
    return Office::create($request->all());
}
}
