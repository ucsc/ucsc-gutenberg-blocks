<?php
// Minimal WP function stubs for running template tests outside full WP environment
if (!function_exists('esc_html')) {
    function esc_html($s) { return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
}
if (!function_exists('esc_attr')) {
    function esc_attr($s) { return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
}
if (!function_exists('esc_url')) {
    function esc_url($s) { return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
}
if (!function_exists('sanitize_email')) {
    function sanitize_email($s) { return filter_var((string)$s, FILTER_SANITIZE_EMAIL); }
}
if (!function_exists('wp_kses_post')) {
    function wp_kses_post($s) { return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
}
