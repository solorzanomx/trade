<?php

namespace App\Livewire;

use App\Services\IBKRImportService;
use Livewire\Component;
use Livewire\WithFileUploads;

class IBKRImporter extends Component
{
    use WithFileUploads;

    public $xmlFile     = null;
    public $status      = 'idle';   // idle | uploading | importing | done | error
    public $progress    = 0;
    public $message     = '';

    // Resultados
    public int    $imported   = 0;
    public int    $closed     = 0;
    public int    $duplicates = 0;
    public int    $skipped    = 0;
    public array  $errors     = [];
    public array  $trades     = [];

    public function updatedXmlFile(): void
    {
        $this->validate(['xmlFile' => 'required|file|max:51200']);
        $this->status   = 'uploading';
        $this->message  = 'Archivo listo para importar...';
        $this->progress = 20;
    }

    public function runImport(): void
    {
        $this->validate(['xmlFile' => 'required|file|max:51200']);

        $this->status   = 'importing';
        $this->message  = 'Leyendo XML de IBKR...';
        $this->progress = 40;

        try {
            // Guardar archivo
            $dir      = storage_path('app/ibkr_temp');
            if (!is_dir($dir)) mkdir($dir, 0777, true);

            $filename = 'ibkr_' . now()->format('YmdHis') . '_' . auth()->id() . '.xml';
            $fullPath = $dir . '/' . $filename;
            $this->xmlFile->storeAs('ibkr_temp', $filename, 'local');

            // Buscar el archivo (Livewire lo guarda en tmp primero)
            if (!file_exists($fullPath)) {
                // Livewire almacena en storage/app/livewire-tmp
                $tmpPath = storage_path('app/livewire-tmp/' . $this->xmlFile->getFilename());
                $contents = file_get_contents($tmpPath ?: $this->xmlFile->getRealPath());
                file_put_contents($fullPath, $contents);
            }

            $this->message  = 'Procesando trades...';
            $this->progress = 65;

            $service = new IBKRImportService(auth()->user());
            $results = $service->import($fullPath);

            // Limpiar
            if (file_exists($fullPath)) unlink($fullPath);

            $this->progress  = 100;
            $this->status    = 'done';
            $this->message   = 'Importación completada';
            $this->imported  = $results['imported'];
            $this->closed    = $results['closed'];
            $this->duplicates= $results['duplicates'];
            $this->skipped   = $results['skipped'];
            $this->errors    = $results['errors'];
            $this->trades    = array_slice($results['trades'], 0, 200); // max 200 en UI

        } catch (\Throwable $e) {
            $this->status  = 'error';
            $this->message = 'Error: ' . $e->getMessage();
            $this->errors  = [$e->getMessage()];
        }

        $this->xmlFile = null;
    }

    public function reset(): void
    {
        $this->status     = 'idle';
        $this->progress   = 0;
        $this->message    = '';
        $this->imported   = 0;
        $this->closed     = 0;
        $this->duplicates = 0;
        $this->skipped    = 0;
        $this->errors     = [];
        $this->trades     = [];
        $this->xmlFile    = null;
    }

    public function render()
    {
        return view('livewire.ibkr-importer');
    }
}
