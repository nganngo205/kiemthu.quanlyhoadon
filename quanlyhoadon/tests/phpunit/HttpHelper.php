<?php
class HttpHelper {
    private $base;
    private $cookieFile;
    public function __construct($base = 'http://localhost/quanlyhoadon') {
        $this->base = rtrim($base, '/');
        $this->cookieFile = sys_get_temp_dir() . '/quanlyhoadon_test_cookie.txt';
        @unlink($this->cookieFile);
    }
    public function post($path, $data = []) {
        $url = $this->base . '/' . ltrim($path, '/');
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookieFile);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $res = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        return [$info, $res];
    }
    public function get($path) {
        $url = $this->base . '/' . ltrim($path, '/');
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookieFile);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $res = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);
        return [$info, $res];
    }
}
