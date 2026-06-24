<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use Throwable;

class PdfCoverGenerator
{
    public function generate(?string $pdfFile, ?string $name = null): ?string
    {
        $sourcePath = $this->localPdfPath($pdfFile);

        if ($sourcePath === null) {
            return null;
        }

        $tmpDir = storage_path('app/tmp/pdf-covers/'.Str::uuid());

        File::ensureDirectoryExists($tmpDir);

        try {
            $renderedPath = $this->renderFirstPage($sourcePath, $tmpDir);

            if ($renderedPath === null) {
                return null;
            }

            return $this->storeCover(
                $renderedPath,
                $this->publicDiskPath($pdfFile) ?? $sourcePath,
                $sourcePath,
                $name,
            );
        } finally {
            File::deleteDirectory($tmpDir);
        }
    }

    private function localPdfPath(?string $pdfFile): ?string
    {
        $relativePath = $this->publicDiskPath($pdfFile);

        if ($relativePath !== null && Storage::disk('public')->exists($relativePath)) {
            return Storage::disk('public')->path($relativePath);
        }

        if (filled($pdfFile) && Str::startsWith((string) $pdfFile, '/') && File::exists((string) $pdfFile)) {
            return (string) $pdfFile;
        }

        return null;
    }

    private function publicDiskPath(?string $pdfFile): ?string
    {
        if (blank($pdfFile)) {
            return null;
        }

        $path = trim((string) $pdfFile);

        if (Str::startsWith($path, ['http://', 'https://', '/'])) {
            return null;
        }

        $path = ltrim($path, '/');

        if (Str::startsWith($path, 'storage/')) {
            $path = Str::after($path, 'storage/');
        }

        if (Str::startsWith($path, 'public/')) {
            $path = Str::after($path, 'public/');
        }

        return $path;
    }

    private function renderFirstPage(string $sourcePath, string $tmpDir): ?string
    {
        foreach ([
            fn () => $this->renderWithImagick($sourcePath, $tmpDir),
            fn () => $this->renderWithPdftoppm($sourcePath, $tmpDir),
            fn () => $this->renderWithMutool($sourcePath, $tmpDir),
            fn () => $this->renderWithSips($sourcePath, $tmpDir),
            fn () => $this->renderWithMagick($sourcePath, $tmpDir),
            fn () => $this->renderWithQuickLook($sourcePath, $tmpDir),
        ] as $renderer) {
            try {
                $renderedPath = $renderer();

                if ($this->isUsableImage($renderedPath)) {
                    return $renderedPath;
                }
            } catch (Throwable) {
                continue;
            }
        }

        return null;
    }

    private function renderWithImagick(string $sourcePath, string $tmpDir): ?string
    {
        if (! class_exists(\Imagick::class) || ! $this->hasRunnableBinary('gs')) {
            return null;
        }

        $outputPath = $tmpDir.'/imagick-cover.jpg';
        $image = new \Imagick;
        $flattened = null;

        try {
            $image->setResolution(160, 160);
            $image->readImage($sourcePath.'[0]');
            $image->setImageBackgroundColor('white');
            $flattened = $image->mergeImageLayers(\Imagick::LAYERMETHOD_FLATTEN);
            $flattened->setImageFormat('jpeg');
            $flattened->setImageCompressionQuality(90);
            $flattened->writeImage($outputPath);
        } finally {
            $flattened?->clear();
            $flattened?->destroy();
            $image->clear();
            $image->destroy();
        }

        return $outputPath;
    }

    private function renderWithPdftoppm(string $sourcePath, string $tmpDir): ?string
    {
        foreach ($this->candidateBinaries('pdftoppm') as $binary) {
            $outputPrefix = $tmpDir.'/pdftoppm-cover';
            $outputPath = $outputPrefix.'.jpg';

            File::delete($outputPath);

            if ($this->run([$binary, '-f', '1', '-singlefile', '-jpeg', '-r', '160', $sourcePath, $outputPrefix])) {
                return $outputPath;
            }
        }

        return null;
    }

    private function renderWithMutool(string $sourcePath, string $tmpDir): ?string
    {
        foreach ($this->candidateBinaries('mutool') as $binary) {
            $outputPath = $tmpDir.'/mutool-cover.png';

            File::delete($outputPath);

            if ($this->run([$binary, 'draw', '-o', $outputPath, '-r', '160', $sourcePath, '1'])) {
                return $outputPath;
            }
        }

        return null;
    }

    private function renderWithSips(string $sourcePath, string $tmpDir): ?string
    {
        foreach ($this->candidateBinaries('sips') as $binary) {
            $outputPath = $tmpDir.'/sips-cover.jpg';

            File::delete($outputPath);

            if ($this->run([$binary, '-s', 'format', 'jpeg', $sourcePath, '--out', $outputPath])) {
                return $outputPath;
            }
        }

        return null;
    }

    private function renderWithMagick(string $sourcePath, string $tmpDir): ?string
    {
        if (! $this->hasRunnableBinary('gs')) {
            return null;
        }

        foreach ($this->candidateBinaries('magick') as $binary) {
            $outputPath = $tmpDir.'/magick-cover.jpg';

            File::delete($outputPath);

            if ($this->run([
                $binary,
                '-density',
                '160',
                $sourcePath.'[0]',
                '-background',
                'white',
                '-alpha',
                'remove',
                '-alpha',
                'off',
                '-quality',
                '90',
                $outputPath,
            ], 60)) {
                return $outputPath;
            }
        }

        return null;
    }

    private function renderWithQuickLook(string $sourcePath, string $tmpDir): ?string
    {
        foreach ($this->candidateBinaries('qlmanage') as $binary) {
            $outputPath = $tmpDir.'/'.basename($sourcePath).'.png';

            File::delete($outputPath);

            if ($this->run([$binary, '-t', '-s', '1200', '-o', $tmpDir, $sourcePath])) {
                return $outputPath;
            }
        }

        return null;
    }

    private function storeCover(string $renderedPath, string $pdfPath, string $sourcePath, ?string $name): ?string
    {
        if ($this->canWriteJpeg() && $this->storeJpegCover($renderedPath, $pdfPath, $sourcePath, $name)) {
            return $this->coverPath($pdfPath, $sourcePath, $name, 'jpg');
        }

        $extension = strtolower(pathinfo($renderedPath, PATHINFO_EXTENSION) ?: 'png');
        $targetPath = $this->coverPath($pdfPath, $sourcePath, $name, $extension);
        $absoluteTargetPath = Storage::disk('public')->path($targetPath);

        File::ensureDirectoryExists(dirname($absoluteTargetPath));

        return File::copy($renderedPath, $absoluteTargetPath) ? $targetPath : null;
    }

    private function storeJpegCover(string $renderedPath, string $pdfPath, string $sourcePath, ?string $name): bool
    {
        $targetPath = $this->coverPath($pdfPath, $sourcePath, $name, 'jpg');
        $absoluteTargetPath = Storage::disk('public')->path($targetPath);

        File::ensureDirectoryExists(dirname($absoluteTargetPath));

        $source = @imagecreatefromstring(File::get($renderedPath));

        if ($source === false) {
            return false;
        }

        $width = imagesx($source);
        $height = imagesy($source);
        $canvas = imagecreatetruecolor($width, $height);

        if ($canvas === false) {
            imagedestroy($source);

            return false;
        }

        $white = imagecolorallocate($canvas, 255, 255, 255);
        imagefilledrectangle($canvas, 0, 0, $width, $height, $white);
        imagecopy($canvas, $source, 0, 0, 0, 0, $width, $height);

        $written = imagejpeg($canvas, $absoluteTargetPath, 90);

        imagedestroy($source);
        imagedestroy($canvas);

        return $written;
    }

    private function coverPath(string $pdfPath, string $sourcePath, ?string $name, string $extension): string
    {
        $baseName = Str::slug($name ?: pathinfo($pdfPath, PATHINFO_FILENAME));
        $baseName = Str::limit($baseName ?: 'publication', 80, '');
        $fingerprint = substr(sha1($pdfPath.'|'.(@filemtime($sourcePath) ?: '').'|'.(@filesize($sourcePath) ?: '')), 0, 10);

        return "publications/covers/{$baseName}-{$fingerprint}.{$extension}";
    }

    private function candidateBinaries(string $command): array
    {
        return array_values(array_unique([
            $command,
            "/opt/homebrew/bin/{$command}",
            "/usr/local/bin/{$command}",
            "/usr/bin/{$command}",
            "/bin/{$command}",
        ]));
    }

    private function hasRunnableBinary(string $command): bool
    {
        foreach ($this->candidateBinaries($command) as $binary) {
            if ($this->run([$binary, '--version'], 5)) {
                return true;
            }
        }

        return false;
    }

    private function run(array $command, int $timeout = 30): bool
    {
        try {
            $process = new Process($command);
            $process->setTimeout($timeout);
            $process->run();

            return $process->isSuccessful();
        } catch (Throwable) {
            return false;
        }
    }

    private function isUsableImage(?string $path): bool
    {
        return filled($path)
            && File::exists((string) $path)
            && File::size((string) $path) > 0
            && @getimagesize((string) $path) !== false;
    }

    private function canWriteJpeg(): bool
    {
        return function_exists('imagecreatefromstring')
            && function_exists('imagecreatetruecolor')
            && function_exists('imagejpeg');
    }
}
