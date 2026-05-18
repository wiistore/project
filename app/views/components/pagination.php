<?php
$pagination = $pagination ?? null;

if (!function_exists('component_e')) {
    function component_e(mixed $value): string
    {
        if (function_exists('app_e')) {
            return app_e($value);
        }

        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('component_page_url')) {
    function component_page_url(int $page, array $extraQuery = []): string
    {
        $requestUri = $_SERVER['REQUEST_URI'] ?? '';
        $parts = parse_url($requestUri);

        $path = $parts['path'] ?? '';
        $query = [];

        if (!empty($parts['query'])) {
            parse_str($parts['query'], $query);
        }

        $query = array_merge($query, $extraQuery);
        $query['page'] = max(1, $page);

        return $path . '?' . http_build_query($query);
    }
}

if (!is_array($pagination)) {
    return;
}

$currentPage = max(1, (int) ($pagination['current_page'] ?? $pagination['page'] ?? 1));
$perPage = max(1, (int) ($pagination['per_page'] ?? 10));
$total = max(0, (int) ($pagination['total'] ?? 0));
$totalPages = max(1, (int) ($pagination['total_pages'] ?? ceil($total / $perPage)));

if ($total <= 0) {
    return;
}

$from = (($currentPage - 1) * $perPage) + 1;
$to = min($total, $currentPage * $perPage);

$start = max(1, $currentPage - 2);
$end = min($totalPages, $currentPage + 2);

if ($currentPage <= 3) {
    $end = min($totalPages, 5);
}

if ($currentPage >= $totalPages - 2) {
    $start = max(1, $totalPages - 4);
}
?>

<div class="app-pagination">
    <div class="app-pagination-info">
        Menampilkan
        <strong><?= component_e($from) ?></strong>
        -
        <strong><?= component_e($to) ?></strong>
        dari
        <strong><?= component_e($total) ?></strong>
        data
    </div>

    <nav class="app-pagination-nav" aria-label="Pagination">
        <a
            href="<?= component_e(component_page_url(max(1, $currentPage - 1))) ?>"
            class="app-pagination-btn <?= $currentPage <= 1 ? 'is-disabled' : '' ?>"
            aria-label="Halaman sebelumnya"
        >
            <i class="ti ti-chevron-left"></i>
        </a>

        <?php if ($start > 1): ?>
            <a href="<?= component_e(component_page_url(1)) ?>" class="app-pagination-btn">1</a>

            <?php if ($start > 2): ?>
                <span class="app-pagination-dots">...</span>
            <?php endif; ?>
        <?php endif; ?>

        <?php for ($page = $start; $page <= $end; $page++): ?>
            <a
                href="<?= component_e(component_page_url($page)) ?>"
                class="app-pagination-btn <?= $page === $currentPage ? 'is-active' : '' ?>"
            >
                <?= component_e($page) ?>
            </a>
        <?php endfor; ?>

        <?php if ($end < $totalPages): ?>
            <?php if ($end < $totalPages - 1): ?>
                <span class="app-pagination-dots">...</span>
            <?php endif; ?>

            <a href="<?= component_e(component_page_url($totalPages)) ?>" class="app-pagination-btn">
                <?= component_e($totalPages) ?>
            </a>
        <?php endif; ?>

        <a
            href="<?= component_e(component_page_url(min($totalPages, $currentPage + 1))) ?>"
            class="app-pagination-btn <?= $currentPage >= $totalPages ? 'is-disabled' : '' ?>"
            aria-label="Halaman berikutnya"
        >
            <i class="ti ti-chevron-right"></i>
        </a>
    </nav>
</div>