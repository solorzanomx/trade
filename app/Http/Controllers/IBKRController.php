<?php

namespace App\Http\Controllers;

use App\Services\IBKRImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class IBKRController extends Controller
{
    public function index()
    {
        return view('ibkr.index');
    }

    public function import(Request $request)
    {
        $request->validate([
            'xml_file' => 'required|file|max:51200',
        ]);

        // Guardar con nombre fijo y extensión .xml
        $dir      = storage_path('app/ibkr_temp');
        $filename = Str::random(32) . '.xml';
        $fullPath = $dir . '/' . $filename;

        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        // Mover el archivo uploadado directamente
        $request->file('xml_file')->move($dir, $filename);

        if (!file_exists($fullPath)) {
            return back()->withErrors(['xml_file' => 'No se pudo guardar el archivo en el servidor.']);
        }

        $service = new IBKRImportService($request->user());
        $results = $service->import($fullPath);

        // Limpiar el archivo temporal
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }

        return redirect()->route('ibkr.index')->with('ibkr_results', $results);
    }
}
