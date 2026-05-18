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
?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= app_e(app_asset('assets/js/app.js')) ?>"></script>
<script src="<?= app_e(app_asset('assets/js/components.js')) ?>"></script>

<?php if (!empty($useChart)): ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<?php endif; ?>

<?php if (($pageScript ?? '') === 'dashboard'): ?>
    <script src="<?= app_e(app_asset('assets/js/dashboard.js')) ?>"></script>
<?php endif; ?>
</body>
</html>