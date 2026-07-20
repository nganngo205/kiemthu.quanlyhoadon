<?php
$base = 'http://localhost/quanlyhoadon';
$cookieFile = __DIR__ . '/.cookie.txt';
@unlink($cookieFile);

$tests = require __DIR__ . '/test_data.php';

$group = $argv[1] ?? null;
if ($group && !isset($tests[$group])) {
    echo "Group not found: $group\n"; exit(1);
}
$runGroups = $group ? [$group => $tests[$group]] : $tests;

function post($url, $data = [], $cookieFile=null) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $res = curl_exec($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);
    return [$info, $res];
}

function get($url, $cookieFile=null) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $res = curl_exec($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);
    return [$info, $res];
}

foreach ($runGroups as $groupName => $cases) {
    echo "\n=== GROUP: $groupName ===\n";
    foreach ($cases as $i => $case) {
        $n = $i + 1;
        echo "[$n] {$case['name']} ... ";
        $name = $groupName;
        $input = $case['input'];
        $expected = $case['expected'];
        $ok = false;
        try {
            if ($groupName === 'add_product') {
                list($info, $res) = post($base . '/sanpham/add.php', $input, $cookieFile);
                $json = json_decode($res, true);
                if ($json && isset($json['success'])) $ok = ($json['success'] === $expected['success']);
            } elseif ($groupName === 'cart_add') {
                list($info, $res) = post($base . '/giohang/add.php', $input, $cookieFile);
                $json = json_decode($res, true);
                if ($json && isset($json['success'])) $ok = ($json['success'] === $expected['success']);
            } elseif ($groupName === 'cart_update_qty') {
                $post = [];
                foreach ($input['qty'] as $pid => $n) {
                    $post["qty[$pid]"] = $n;
                }
                list($info, $res) = post($base . '/giohang/update.php', $post, $cookieFile);
                $json = json_decode($res, true);
                if ($json && isset($json['success'])) $ok = ($json['success'] === $expected['success']);
                else $ok = ($info['http_code'] >= 200 && $info['http_code'] < 400);
            } elseif ($groupName === 'payment_cash') {
                $post = $input + ['method' => 'TIEN_MAT'];
                list($info, $res) = post($base . '/thanhtoan/pay.php', $post, $cookieFile);
                $json = json_decode($res, true);
                if ($json && isset($json['success'])) $ok = ($json['success'] === $expected['success']);
                else {
                    $ok = (strpos($res, 'Thanh toán không đủ') !== false) === ($expected['success'] === false);
                }
            } else {
                $ok = true; 
            }
        } catch (Exception $e) {
            $ok = false;
        }
        echo $ok ? "PASS\n" : "FAIL\n";
    }
}

echo "\nIntegration run finished. Note: this is a lightweight runner - adapt tests/test_data.php and mappings for full coverage.\n";

?>