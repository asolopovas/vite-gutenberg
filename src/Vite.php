<?php

namespace Lyntouch;

class Vite
{

    private array $paths;
    private string $manifestDirectory;
    private string $outputDir;
    private array $output = [];
    public bool $hot = false;

    public function __construct(array $paths, string $manifestDirectory = "", string $outputDir = "")
    {
        $this->paths = $this->normalizePaths($paths);
        $this->manifestDirectory = $this->resolveManifest($manifestDirectory);
        $this->outputDir =  $this->resolveOutputDirectory($outputDir);
    }

    private function resolveManifest(string $manifestDirectory): string
    {
        return $manifestDirectory === "" ? plugin_dir_path(__DIR__) . "static" : $manifestDirectory;
    }

    private function resolveOutputDirectory(): string
    {
        return str_contains($this->manifestDirectory, 'themes')
            ? parse_url(get_stylesheet_directory_uri(), PHP_URL_PATH) . 'static'
            : parse_url(plugin_dir_url(realpath(__DIR__)), PHP_URL_PATH) . 'static';
    }

    private function normalizePaths(array $paths): array
    {
        return array_map(fn ($path) => str_starts_with($path, '/')
            ? ltrim($path, '/')
            : $path, $paths);
    }

    private function getDevHost(): string|bool
    {
        if (is_file($this->manifestDirectory . '/hot')) {
            return rtrim(file_get_contents($this->manifestDirectory . '/hot'));
        }
        return false;
    }

    private function parseManifest()
    {
        $json = file_get_contents($this->manifestDirectory . '/manifest.json');

        return json_decode($json, true);
    }

    public function build(): array
    {
        $devHost = $this->getDevHost();

        if ($devHost) {
            $this->output['client'] = $devHost . "/@vite/client";
            foreach ($this->paths as $path) {
                $this->output[$path] = $devHost . "/$path";
            }

            return $this->output;
        }

        $manifestAssets = $this->parseManifest();

        foreach ($manifestAssets as $key => $asset) {
            $host = getenv("APP_URL");
            $asset = $manifestAssets[$key]['file'];
            $this->output[$key] = $host . $this->outputDir . '/' . $asset;
        }

        return $this->output;
    }

    public function get($key)
    {
        return $this->output[$key];
    }
}
