<?php
$flashMessages = [];

$flashData = $flash ?? [];

if (is_array($flashData)) {
    foreach (['success', 'error', 'warning', 'info'] as $type) {
        if (!empty($flashData[$type])) {
            $value = $flashData[$type];

            if (is_array($value)) {
                foreach ($value as $message) {
                    $flashMessages[] = [
                        'type' => $type,
                        'message' => (string) $message,
                    ];
                }
            } else {
                $flashMessages[] = [
                    'type' => $type,
                    'message' => (string) $value,
                ];
            }
        }
    }
}
?>

<?php if (!empty($flashMessages)): ?>
    <script type="application/json" id="appFlashMessages">
        <?= json_encode($flashMessages, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>
    </script>
<?php endif; ?>