<?php
// Common helpers for the application
if (!function_exists('is_ajax')) {
    function is_ajax() {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
            || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);
    }
}

if (!function_exists('json_response')) {
    function json_response($data) {
        if (!headers_sent()) header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}

if (!function_exists('redirect_with_msg')) {
    function redirect_with_msg($url, $msg, $status = 'info') {
        $location = $url . (strpos($url, '?') === false ? '?' : '&') . 'msg=' . urlencode($msg) . '&status=' . urlencode($status);
        header('Location: ' . $location);
        exit;
    }
}

if (!function_exists('json_success')) {
    function json_success($msg = '', $extra = []) {
        $payload = array_merge(['success' => true, 'msg' => $msg], $extra);
        json_response($payload);
    }
}

if (!function_exists('json_error')) {
    function json_error($msg = '', $extra = []) {
        $payload = array_merge(['success' => false, 'msg' => $msg], $extra);
        json_response($payload);
    }
}
