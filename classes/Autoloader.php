<?php

class Autoloader
{
    public static function register(string $projectRoot): void
    {
        spl_autoload_register(function (string $className) use ($projectRoot): void {
            $possibleFiles = [
                $projectRoot . '/' . $className . '.php',
                $projectRoot . '/classes/' . $className . '.php',
            ];

            foreach ($possibleFiles as $filePath) {
                if (file_exists($filePath)) {
                    require_once $filePath;
                    return;
                }
            }
        });
    }
}
