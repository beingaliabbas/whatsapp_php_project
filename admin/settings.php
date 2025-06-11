<?php
require 'auth.php';
include 'header.php';
require_once 'db.php';

$msg = "";
$errors = [];

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle favicon upload if present
    if (isset($_FILES['favicon']) && $_FILES['favicon']['error'] === UPLOAD_ERR_OK) {
    $ext = strtolower(pathinfo($_FILES['favicon']['name'], PATHINFO_EXTENSION));
    $allowed = ['ico', 'png', 'jpg', 'jpeg', 'svg'];
    $assetsDir = __DIR__ . '/../assets';
    if (!file_exists($assetsDir)) {
        mkdir($assetsDir, 0755, true);
    }
    $dest = $assetsDir . '/favicon.' . $ext;
    $relativeDest = 'assets/favicon.' . $ext;
    if (in_array($ext, $allowed)) {
        if (move_uploaded_file($_FILES['favicon']['tmp_name'], $dest)) {
            $_POST['settings']['favicon'] = $relativeDest;
        } else {
            $errors[] = "Failed to upload favicon file.";
        }
    } else {
        $errors[] = "Invalid favicon file type.";
    }
}

    // Loop through settings and update
    foreach ($_POST['settings'] as $key => $value) {
        $stmt = $db->prepare("UPDATE general_settings SET value=? WHERE `key`=?");
        $stmt->execute([$value, $key]);
    }
    if (!$errors) {
        $msg = "Settings updated!";
    }
}

// Fetch all settings
$stmt = $db->query("SELECT * FROM general_settings ORDER BY id ASC");
$settings = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $settings[$row['key']] = $row['value'];
}

// Prepare for display
$site_title = $settings['site_title'] ?? '';
$site_description = $settings['site_description'] ?? '';
$site_keywords = $settings['site_keywords'] ?? '';
$favicon = $settings['favicon'] ?? 'assets/favicon.ico';
$base_url = isset($settings['base_url']) ? rtrim($settings['base_url'], '/') . '/' : '/';
?>
<div class="mb-6">
    <h1 class="text-2xl font-bold mb-4">General Settings</h1>
    <?php if (!empty($msg)): ?>
        <div class="bg-green-100 text-green-700 p-2 mb-4 rounded"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>
    <?php if ($errors): ?>
        <div class="bg-red-100 text-red-700 p-2 mb-4 rounded">
            <?php foreach ($errors as $e) echo htmlspecialchars($e) . "<br>"; ?>
        </div>
    <?php endif; ?>
    <form method="post" enctype="multipart/form-data" class="bg-white border rounded p-4 w-full max-w-lg">
        <div class="mb-4">
            <label class="block font-semibold mb-1">Site Title</label>
            <input type="text" name="settings[site_title]" value="<?= htmlspecialchars($site_title) ?>" class="w-full border px-2 py-1 rounded" maxlength="255"/>
        </div>
        <div class="mb-4">
            <label class="block font-semibold mb-1">Site Description</label>
            <textarea name="settings[site_description]" class="w-full border px-2 py-1 rounded" maxlength="500"><?= htmlspecialchars($site_description) ?></textarea>
        </div>
        <div class="mb-4">
            <label class="block font-semibold mb-1">Site Keywords</label>
            <input type="text" name="settings[site_keywords]" value="<?= htmlspecialchars($site_keywords) ?>" class="w-full border px-2 py-1 rounded" maxlength="255"/>
            <div class="text-xs text-gray-500">Comma separated keywords for SEO</div>
        </div>
        <div class="mb-4">
            <label class="block font-semibold mb-1">Favicon Icon</label>
            <div class="flex items-center space-x-4">
                <img src="<?= htmlspecialchars($base_url . $favicon) ?>" alt="favicon" class="w-8 h-8 border" />
                <input type="file" name="favicon" accept=".ico,.png,.jpg,.jpeg,.svg" class="border px-2 py-1 rounded"/>
            </div>
            <div class="text-xs text-gray-500">Upload .ico, .png, .jpg, or .svg file. Recommended: 32x32px or 64x64px.</div>
        </div>
        <?php
        // Show all other settings except the ones we already handled
        foreach ($settings as $key => $value) {
            if (in_array($key, ['site_title', 'site_description', 'site_keywords', 'favicon'])) continue;
        ?>
            <div class="mb-4">
                <label class="block font-semibold mb-1"><?= htmlspecialchars($key) ?></label>
                <input type="text" name="settings[<?= htmlspecialchars($key) ?>]" value="<?= htmlspecialchars($value) ?>" class="w-full border px-2 py-1 rounded" />
            </div>
        <?php } ?>
        <button type="submit" class="bg-indigo-600 text-white px-4 py-1 rounded hover:bg-indigo-700">Save Settings</button>
    </form>
</div>
<?php include 'footer.php'; ?>