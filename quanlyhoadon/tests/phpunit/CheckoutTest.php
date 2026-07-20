<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/HttpHelper.php';

class CheckoutTest extends TestCase {
    private $http;
    protected function setUp(): void {
        $this->http = new HttpHelper();
    }

    public function testCashPayment_insufficient() {
        $this->http->post('/giohang/add.php', ['product_id'=>'SP003','quantity'=>1]);
        list($info, $res) = $this->http->post('/thanhtoan/pay.php', ['method'=>'TIEN_MAT','paid'=>0]);
        $json = json_decode($res, true);
        if (is_array($json)) {
            $this->assertFalse($json['success']);
        } else {
            $this->assertStringContainsString('Thanh toán không đủ', $res);
        }
    }
}
