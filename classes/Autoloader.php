<?php

class Autoloader
{
    public static function register(string $projectRoot): void
    {
        spl_autoload_register(function (string $className) use ($projectRoot): void {
            $classFile = $className . '.php';
            $classesPath = $projectRoot . '/classes';

            $possibleFiles = [
                $projectRoot . '/' . $classFile,
                $classesPath . '/' . $classFile,
            ];

            foreach ($possibleFiles as $filePath) {
                if (file_exists($filePath)) {
                    require_once $filePath;
                    return;
                }
            }

            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($classesPath, RecursiveDirectoryIterator::SKIP_DOTS)
            );

            foreach ($iterator as $file) {
                if ($file->isFile() && $file->getFilename() === $classFile) {
                    require_once $file->getPathname();
                    return;
                }
            }
        });
    }
}
