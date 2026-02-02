<?php

namespace App\Http\Controllers;

use App\Models\Office;
use Illuminate\Http\Request;

class OfficeController extends Controller
{
    public function index()
    {
        $offices = Office::all();
        return view('admin.offices.index', compact('offices'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'office_name' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
            'radius' => 'required|integer',
        ]);

        Office::create($request->all());

        return redirect()->back()->with('success', 'Kantor berhasil ditambahkan');
    }

    public function destroy($id)
    {
        $office = Office::findOrFail($id);
        $office->delete();

        return redirect()->back()->with('success', 'Kantor berhasil dihapus');
    }
}