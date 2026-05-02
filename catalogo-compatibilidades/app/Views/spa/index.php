<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title ?? 'App') ?></title>
    <?php foreach (($assets['entryCss'] ?? []) as $cssPath): ?>
        <link rel="stylesheet" href="<?= base_url($cssPath) ?>">
    <?php endforeach; ?>
</head>
<body>
    <div id="app"></div>

    <?php if (!empty($assets['entryJs'])): ?>
        <script type="module" src="<?= base_url($assets['entryJs']) ?>"></script>
    <?php else: ?>
        <main style="font-family: Nunito, sans-serif; padding: 24px;">
            <h1>SPA no compilada</h1>
            <p>Ejecuta <code>npm run build</code> dentro de <code>frontend/</code> para generar <code>public/spa</code>.</p>
        </main>
    <?php endif; ?>
</body>
</html>

