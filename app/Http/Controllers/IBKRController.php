<?php

namespace App\Http\Controllers;

use App\Services\IBKRImportService;
use Illuminate\Http\Request;

class IBKRController extends Controller
{
    public function index()
    {
        return view('ibkr.index');
    }

    public function import(Request $request)
    {
        $request->validate([
            'xml_file' => 'required|file|mimes:xml,txt|max:20480',
        ]);

        $path = $request->file('xml_file')->store('ibkr_temp');
        $fullPath = storage_path('app/' . $path);

        $service = new IBKRImportService($request->user());
        $results = $service->import($fullPath);

        // Limpiar el archivo temporal
        unlink($fullPath);

        return redirect()->route('ibkr.index')->with('ibkr_results', $results);
    }
}
