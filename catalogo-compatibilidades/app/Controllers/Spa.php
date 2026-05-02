<?php

declare(strict_types=1);

namespace App\Controllers;

use CodeIgniter\Controller;

class Spa extends Controller
{
    public function index()
    {
        $manifestPath = FCPATH . 'spa/.vite/manifest.json';
        $assets = [
            'entryJs' => null,
            'entryCss' => [],
        ];

        if (is_file($manifestPath)) {
            $raw = file_get_contents($manifestPath);
            $manifest = is_string($raw) ? json_decode($raw, true) : null;
            if (is_array($manifest) && isset($manifest['index.html'])) {
                $entry = $manifest['index.html'];
                $assets['entryJs'] = isset($entry['file']) ? 'spa/' . $entry['file'] : null;
                $assets['entryCss'] = isset($entry['css']) && is_array($entry['css'])
                    ? array_map(static fn(string $css): string => 'spa/' . $css, $entry['css'])
                    : [];
            }
        }

        return view('spa/index', [
            'title' => 'App Interna',
            'assets' => $assets,
        ]);
    }
}
