<?php

function viteAssetsLoader($assets): void
{
    foreach ($assets as $key => $asset) {
        $pathInfo = pathinfo($asset);
        $ext = $pathInfo['extension'] ?? '';

        match ($ext) {
            'css' => wp_enqueue_style($key, $asset, [], null),
            default => wp_enqueue_script(
                $key,
                $asset . "#module",
                ['wp-block-editor', 'wp-blocks', 'wp-components', 'wp-data', 'wp-element', 'wp-i18n', 'wp-primitives'],
                null,
                true
            )
        };
    }
}
