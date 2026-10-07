<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{$pageTitle|default:'Блог'|escape}</title>
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>
    <header class="site-header">
        <div class="container">
            <a class="site-logo" href="/">PHP Blog</a>
        </div>
    </header>

    <main class="container">
        {block name="content"}{/block}
    </main>
</body>
</html>

