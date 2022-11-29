<?php

namespace Lyntouch;

class Vite
{

    private array $paths;
    private string $outputDir;
    public array $output = [];
    public bool $hot = false;
    public string $pluginUrl;
    public string $manifestDirectory;

    public function __construct(array $paths, string $manifestDirectory = "", string $outputDir = "")
    {
        $this->paths = $this->normalizePaths($paths);
        $this->manifestDirectory = $manifestDirectory ? $manifestDirectory : VITGUT_BASE_PATH . 'static';
        $this->outputDir = $outputDir === ""
            ? $this->getDefaultOutputDirectory()
            : $outputDir;
    }

    private function getDefaultOutputDirectory(): string
    {
        return str_contains($this->manifestDirectory, 'themes')
            ? parse_url(get_stylesheet_directory_uri(), PHP_URL_PATH) . 'build'
            : parse_url(VITGUT_BASE_URL, PHP_URL_PATH) . 'static';
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

    public function loadStatic($assets)
    {
        $assets = $this->normalizePaths($assets);
        foreach ($assets as $asset) {
            $file = new \SplFileInfo($asset);
            $this->output[$file->getFilename()] = VITGUT_BASE_URL .  $asset;
        }
        return $this;
    }

    public function build(): Vite
    {
        $devHost = $this->getDevHost();

        if ($devHost) {
            $this->output['client'] = $devHost . "/@vite/client";
            foreach ($this->paths as $path) {
                $this->output[$path] = $devHost . "/$path";
            }
            return $this;
        }

        $manifestAssets = $this->parseManifest();

        foreach ($manifestAssets as $key => $asset) {
            $host = env("APP_URL");
            $asset = $manifestAssets[$key]['file'];

            if (str_contains ($key, '_index')) {
                $this->output[$key] = $host . $this->outputDir . '/' . $asset;
            }

            if (in_array($key, $this->paths)) {
                $this->output[$key] = $host . $this->outputDir . '/' . $asset;
            }
        }

        return $this;
    }

    public function load()
    {
        foreach ($this->output as $key => $asset) {
            $pathInfo = pathinfo($asset);
            $ext = $pathInfo['extension'] ?? '';

            match ($ext) {
                'css' => wp_enqueue_style($key, $asset, [], null),
                default => wp_enqueue_script($key, $asset . "#module", [], null)
            };
        }
    }

    public function get($key)
    {
        return $this->output[$key];
    }
}
