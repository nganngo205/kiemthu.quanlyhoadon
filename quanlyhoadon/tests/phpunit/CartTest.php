<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/HttpHelper.php';

class CartTest extends TestCase {
    private $http;
    protected function setUp(): void {
        $this->http = new HttpHelper();
    }

    public function testAddToCart_and_update_qty() {
        list($info, $res) = $this->http->post('/giohang/add.php', ['product_id'=>'SP002','quantity'=>1]);
        $json = json_decode($res, true);
        $this->assertIsArray($json);
        $this->assertTrue($json['success']);

        $post = ['qty[SP002]'=>2];
        list($info2, $res2) = $this->http->post('/giohang/update.php', $post);
        $json2 = json_decode($res2, true);
        $this->assertIsArray($json2);
        $this->assertTrue($json2['success']);
    }

    public function testAddToCart_insufficient_stock() {
        list($info, $res) = $this->http->post('/giohang/add.php', ['product_id'=>'SP001','quantity'=>999999]);
        $json = json_decode($res, true);
        $this->assertIsArray($json);
        $this->assertFalse($json['success']);
    }
}
