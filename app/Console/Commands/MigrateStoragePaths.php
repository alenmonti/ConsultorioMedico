<?php

namespace App\Console\Commands;

use App\Models\HistoriaClinica;
use App\Models\Paciente;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class MigrateStoragePaths extends Command
{
    protected $signature = 'storage:migrate-paths
                            {--dry-run : Mostrar qué haría sin hacer nada}
                            {--move-files : También mover los archivos físicos}
                            {--limit=0 : Procesar solo N archivos en total (0 = todos)}';

    protected $description = 'Migra paths de imagenes/ y documents/ al nuevo formato usuarios/{medico_id}/...';

    public function handle(): int
    {
        $dryRun    = $this->option('dry-run');
        $moveFiles = $this->option('move-files');
        $limit     = (int) $this->option('limit');

        if ($dryRun) {
            $this->warn('DRY RUN — no se realizará ningún cambio.');
        }
        if ($limit > 0) {
            $this->warn("LIMIT {$limit} — se procesarán solo {$limit} archivo(s).");
        }

        $this->migrateImagenes($dryRun, $moveFiles, $limit);
        $this->migrateDocumentos($dryRun, $moveFiles, $limit);

        $this->info('');
        $this->info('Listo.');
        return self::SUCCESS;
    }

    private function migrateImagenes(bool $dryRun, bool $moveFiles, int $limit): void
    {
        $this->info('');
        $this->info('=== Imágenes de historia clínica ===');

        $procesados = 0;

        HistoriaClinica::withoutGlobalScopes()
            ->whereNotNull('imagenes')
            ->where('imagenes', '!=', '[]')
            ->with('paciente:id,medico_id')
            ->get()
            ->each(function (HistoriaClinica $hc) use ($dryRun, $moveFiles, $limit, &$procesados) {
                if ($limit > 0 && $procesados >= $limit) {
                    return false;
                }
                $medicoId = $hc->paciente?->medico_id;

                if (!$medicoId) {
                    $this->error("  Historia #{$hc->id}: no se encontró medico_id para paciente {$hc->paciente_id}");
                    return;
                }

                $nuevasImagenes = collect($hc->imagenes)->map(function (string $path) use ($hc, $medicoId, $dryRun, $moveFiles, $limit, &$procesados) {
                    if ($limit > 0 && $procesados >= $limit) {
                        return $path;
                    }

                    if (!str_starts_with($path, 'imagenes/')) {
                        return $path;
                    }

                    $nuevoPath = "usuarios/{$medicoId}/imagenes/" . basename($path);
                    $this->line("  Historia #{$hc->id}: {$path} → {$nuevoPath}");
                    $procesados++;

                    if ($moveFiles && !$dryRun) {
                        if (Storage::disk('public')->exists($path)) {
                            Storage::disk('public')->makeDirectory("usuarios/{$medicoId}/imagenes");
                            Storage::disk('public')->move($path, $nuevoPath);
                        } else {
                            $this->warn("    Archivo no encontrado en disco: {$path}");
                        }
                    }

                    return $nuevoPath;
                })->all();

                if (!$dryRun) {
                    $hc->imagenes = $nuevasImagenes;
                    $hc->save();
                }
            });
    }

    private function migrateDocumentos(bool $dryRun, bool $moveFiles, int $limit): void
    {
        $this->info('');
        $this->info('=== Documentos de pacientes ===');

        $procesados = 0;

        Paciente::withoutGlobalScopes()
            ->whereNotNull('documento')
            ->get()
            ->each(function (Paciente $paciente) use ($dryRun, $moveFiles, $limit, &$procesados) {
                if ($limit > 0 && $procesados >= $limit) {
                    return false;
                }

                $path = $paciente->documento;

                if (!str_starts_with($path, 'documents/')) {
                    return;
                }

                $nuevoPath = "usuarios/{$paciente->medico_id}/documents/" . basename($path);
                $this->line("  Paciente #{$paciente->id}: {$path} → {$nuevoPath}");
                $procesados++;

                if ($moveFiles && !$dryRun) {
                    if (Storage::disk('public')->exists($path)) {
                        Storage::disk('public')->makeDirectory("usuarios/{$paciente->medico_id}/documents");
                        Storage::disk('public')->move($path, $nuevoPath);
                    } else {
                        $this->warn("    Archivo no encontrado en disco: {$path}");
                    }
                }

                if (!$dryRun) {
                    $paciente->documento = $nuevoPath;
                    $paciente->save();
                }
            });
    }
}
