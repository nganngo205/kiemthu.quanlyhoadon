<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/HttpHelper.php';

class ProductsTest extends TestCase {
    private $http;
    public static function setUpBeforeClass(): void {
    }
    protected function setUp(): void {
        $this->http = new HttpHelper();
    }

    public function testAddProduct_valid() {
        $id = 'TST_' . uniqid();
        $input = ['id'=>$id,'barcode'=>'9'.rand(100000000000,999999999999),'name'=>'PT Test','price'=>15000,'quantity'=>5];
        list($info, $res) = $this->http->post('/sanpham/add.php', $input);
        $json = json_decode($res, true);
        $this->assertIsArray($json);
        $this->assertArrayHasKey('success', $json);
        $this->assertTrue($json['success']);
    }

    public function testAddProduct_duplicate() {
        $input = ['id'=>'SP001','barcode'=>'8938505970011','name'=>'Dup','price'=>10000,'quantity'=>1];
        list($info, $res) = $this->http->post('/sanpham/add.php', $input);
        $json = json_decode($res, true);
        $this->assertIsArray($json);
        $this->assertFalse($json['success']);
    }
}
