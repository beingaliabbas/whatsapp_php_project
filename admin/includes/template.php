<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? $title : 'Your Website' ?></title>
    <!-- Tailwind CSS -->
    <link href="https://cdn.tailwindcss.com" rel="stylesheet">
</head>
<body class="bg-gray-50 text-gray-800">

    <!-- Main Content Here -->
    <?= $content ?>

    <!-- Optional Footer -->
    <footer class="bg-gray-800 text-white p-4 text-center">
        <p>&copy; <?= date('Y') ?> Your Website</p>
    </footer>

</body>
</html>
