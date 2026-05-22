    </main>
</div>

<?php
$componentFiles = [
    APP_PATH . '/views/components/flash.php',
    APP_PATH . '/views/components/toast-container.php',
    APP_PATH . '/views/components/confirm-modal.php',
];

foreach ($componentFiles as $componentFile) {
    if (file_exists($componentFile)) {
        require $componentFile;
    }
}

$pageScripts = $pageScripts ?? [];
if (is_string($pageScripts)) {
    $pageScripts = [$pageScripts];
}
?>

<script src="<?= app_e(app_asset_versioned('assets/vendor/bootstrap.bundle.min.js')) ?>"></script>
<script src="<?= app_e(app_asset_versioned('assets/vendor/aos.js')) ?>"></script>

<script src="<?= app_e(app_asset_versioned('assets/js/app.js')) ?>"></script>
<script src="<?= app_e(app_asset_versioned('assets/js/components.js')) ?>"></script>
<script src="<?= app_e(app_asset_versioned('assets/js/animations.js')) ?>"></script>

<?php if (!empty($useChart)): ?>
    <script src="<?= app_e(app_asset_versioned('assets/vendor/chart.umd.min.js')) ?>"></script>
<?php endif; ?>

<?php if (!empty($useBarcode)): ?>
    <script src="<?= app_e(app_asset_versioned('assets/vendor/JsBarcode.all.min.js')) ?>"></script>
<?php endif; ?>

<?php if (($pageScript ?? '') === 'dashboard'): ?>
    <script src="<?= app_e(app_asset_versioned('assets/js/dashboard.js')) ?>"></script>
<?php endif; ?>

<?php foreach ($pageScripts as $scriptFile): ?>
    <script src="<?= app_e(app_asset_versioned((string) $scriptFile)) ?>"></script>
<?php endforeach; ?>
</body>
</html>
