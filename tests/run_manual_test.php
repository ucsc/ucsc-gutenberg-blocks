<?php
// Simple manual test to validate CampusDirectoryTemplate escaping without phpunit
// Provide minimal WP function stubs when running outside WP
if (!function_exists('esc_html')) {
    function esc_html($s) { return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
}
if (!function_exists('esc_attr')) {
    function esc_attr($s) { return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
}
if (!function_exists('esc_url')) {
    function esc_url($s) { return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
}
if (!function_exists('sanitize_email')) {
    function sanitize_email($s) { return filter_var($s, FILTER_SANITIZE_EMAIL); }
}
if (!function_exists('wp_kses_post')) {
    function wp_kses_post($s) { return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
}

$items = [
    'informationToDisplay' => [[ 'cn' => 'Name', 'mail' => 'Campus Email' ]],
    'nodeContent' => [ 'linkOutToCampusDirectory' => false ],
    'dirLayout' => 'list',
    'linkToProfile' => false,
    'items' => [
        [
            'uid' => ['baduid'],
            'cn' => ['<script>alert(1)</script>'],
            'mail' => ['bad@example.com'],
            'ucscpersonpubwebsite' => ['http://example.com label'],
            'jpegphoto' => [],
            'count' => 1,
        ]
    ]
];

ob_start();
include __DIR__ . '/../templates/CampusDirectoryTemplate.php';
$output = ob_get_clean();

$ok = true;
if (strpos($output, '<script>') !== false) {
    echo "FAIL: raw <script> found in output\n";
    $ok = false;
}
if (strpos($output, '&lt;script&gt;alert(1)&lt;/script&gt;') === false) {
    echo "FAIL: escaped script tag not found in output\n";
    $ok = false;
}
if ($ok) {
    echo "PASS: CampusDirectoryTemplate escaping looks good\n";
    exit(0);
} else {
    echo "OUTPUT:\n" . $output . "\n";
    exit(1);
}
