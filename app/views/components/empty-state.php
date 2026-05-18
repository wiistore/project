<?php
$emptyIcon = $emptyIcon ?? 'ti ti-database-off';
$emptyTitle = $emptyTitle ?? 'Data belum tersedia';
$emptyMessage = $emptyMessage ?? 'Belum ada data yang bisa ditampilkan saat ini.';
$emptyActionUrl = $emptyActionUrl ?? null;
$emptyActionLabel = $emptyActionLabel ?? null;
$emptyActionIcon = $emptyActionIcon ?? 'ti ti-plus';
?>

<div class="app-empty-state">
    <div class="app-empty-icon">
        <i class="<?= app_e($emptyIcon) ?>"></i>
    </div>

    <h3><?= app_e($emptyTitle) ?></h3>
    <p><?= app_e($emptyMessage) ?></p>

    <?php if (!empty($emptyActionUrl) && !empty($emptyActionLabel)): ?>
        <a href="<?= app_e(app_url($emptyActionUrl)) ?>" class="btn btn-success app-empty-action">
            <i class="<?= app_e($emptyActionIcon) ?>"></i>
            <?= app_e($emptyActionLabel) ?>
        </a>
    <?php endif; ?>
</div>

<?php
unset(
    $emptyIcon,
    $emptyTitle,
    $emptyMessage,
    $emptyActionUrl,
    $emptyActionLabel,
    $emptyActionIcon
);
?>