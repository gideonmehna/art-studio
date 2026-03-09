<?php
/**
 * PHPUnit bootstrap: define minimal WordPress function stubs so that
 * inc/field-map-processor.php can be loaded and tested without WordPress.
 */

define('ART_STUDIO_TESTING', true);

// -----------------------------------------------------------------------
// Minimal stubs for the three WordPress sanitization helpers used by the
// pure processor functions. These closely match WP's actual behaviour.
// -----------------------------------------------------------------------

if (!function_exists('sanitize_text_field')) {
    function sanitize_text_field(string $str): string
    {
        return trim(strip_tags($str));
    }
}

if (!function_exists('sanitize_textarea_field')) {
    function sanitize_textarea_field(string $str): string
    {
        // Preserve newlines (WP keeps them in textarea fields).
        return trim(strip_tags($str));
    }
}

if (!function_exists('absint')) {
    function absint($maybeint): int
    {
        return abs((int) $maybeint);
    }
}

// Load the functions under test — no WordPress needed.
require_once __DIR__ . '/../inc/field-map-processor.php';
