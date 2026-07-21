<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Process\Process;

class CandidateDocumentService
{
    private const IMAGE_TARGET_BYTES = 450 * 1024;

    public function store(UploadedFile $file, string $directory, string $label): string
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $baseName = Str::slug($label) . '-' . Str::lower(Str::random(12));

        if (in_array($extension, ['jpg', 'jpeg', 'png'], true)) {
            $optimized = $this->optimizeImage($file);
            if ($optimized !== null) {
                $path = "{$directory}/{$baseName}.jpg";
                Storage::disk('local')->put($path, $optimized);

                return $path;
            }
        }

        if ($extension === 'pdf') {
            $optimized = $this->optimizePdf($file);
            if ($optimized !== null) {
                $path = "{$directory}/{$baseName}.pdf";
                Storage::disk('local')->put($path, $optimized);

                return $path;
            }
        }

        return $this->storeLossless($file, $directory, $baseName, $extension);
    }

    public function download(string $path): Response
    {
        if (str_ends_with($path, '.gz')) {
            $compressed = Storage::disk('local')->get($path);
            $contents = gzdecode($compressed);
            abort_if($contents === false, 500, 'Dokumen tidak dapat dibuka.');

            $filename = basename(substr($path, 0, -3));

            return response($contents, 200, [
                'Content-Type' => 'application/octet-stream',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Content-Length' => (string) strlen($contents),
            ]);
        }

        return Storage::disk('local')->download($path);
    }

    private function optimizeImage(UploadedFile $file): ?string
    {
        $contents = @file_get_contents($file->getRealPath());
        $source = $contents !== false ? @imagecreatefromstring($contents) : false;
        if (!$source) {
            return null;
        }

        $originalWidth = imagesx($source);
        $originalHeight = imagesy($source);
        $scale = min(1, 1600 / max($originalWidth, $originalHeight));
        $quality = 76;
        $result = null;

        for ($attempt = 0; $attempt < 6; $attempt++) {
            $width = max(1, (int) round($originalWidth * $scale));
            $height = max(1, (int) round($originalHeight * $scale));
            $canvas = imagecreatetruecolor($width, $height);
            imagefill($canvas, 0, 0, imagecolorallocate($canvas, 255, 255, 255));
            imagecopyresampled($canvas, $source, 0, 0, 0, 0, $width, $height, $originalWidth, $originalHeight);

            ob_start();
            imagejpeg($canvas, null, $quality);
            $result = ob_get_clean();
            imagedestroy($canvas);

            if (is_string($result) && strlen($result) <= self::IMAGE_TARGET_BYTES) {
                break;
            }

            $scale *= 0.82;
            $quality = max(48, $quality - 6);
        }

        imagedestroy($source);

        return is_string($result) ? $result : null;
    }

    private function optimizePdf(UploadedFile $file): ?string
    {
        $binary = $this->ghostscriptBinary();
        if ($binary === null) {
            return null;
        }

        $outputPath = tempnam(sys_get_temp_dir(), 'candidate_pdf_');
        if ($outputPath === false) {
            return null;
        }

        $process = new Process([
            $binary,
            '-sDEVICE=pdfwrite',
            '-dCompatibilityLevel=1.4',
            '-dPDFSETTINGS=/screen',
            '-dNOPAUSE',
            '-dQUIET',
            '-dBATCH',
            '-dColorImageResolution=96',
            '-dGrayImageResolution=96',
            '-dMonoImageResolution=150',
            '-sOutputFile=' . $outputPath,
            $file->getRealPath(),
        ]);
        $process->setTimeout(60);
        $process->run();

        $contents = null;
        if ($process->isSuccessful() && is_file($outputPath) && filesize($outputPath) > 0) {
            $optimized = file_get_contents($outputPath);
            if ($optimized !== false && strlen($optimized) < (int) $file->getSize()) {
                $contents = $optimized;
            }
        }

        @unlink($outputPath);

        return $contents;
    }

    private function storeLossless(
        UploadedFile $file,
        string $directory,
        string $baseName,
        string $extension
    ): string {
        $contents = file_get_contents($file->getRealPath());
        $compressed = gzencode($contents, 9);

        if ($compressed !== false && strlen($compressed) < strlen($contents) * 0.95) {
            $path = "{$directory}/{$baseName}.{$extension}.gz";
            Storage::disk('local')->put($path, $compressed);

            return $path;
        }

        $path = "{$directory}/{$baseName}.{$extension}";
        Storage::disk('local')->put($path, $contents);

        return $path;
    }

    private function ghostscriptBinary(): ?string
    {
        $installedVersions = glob('C:\\Program Files\\gs\\gs*\\bin\\gswin64c.exe') ?: [];
        rsort($installedVersions, SORT_NATURAL);

        if ($installedVersions !== []) {
            return $installedVersions[0];
        }

        $finder = PHP_OS_FAMILY === 'Windows'
            ? ['where.exe', 'gswin64c.exe']
            : ['which', 'gs'];

        try {
            $process = new Process($finder);
            $process->setTimeout(5);
            $process->run();

            if ($process->isSuccessful()) {
                $path = trim(strtok($process->getOutput(), "\r\n"));

                return $path !== '' ? $path : null;
            }
        } catch (\Throwable) {
            return null;
        }

        return null;
    }
}
