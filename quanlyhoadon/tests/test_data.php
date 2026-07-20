<?php
return [
    'add_product' => [
        [
            'name'=>'Thêm sản phẩm hợp lệ',
            'input'=>['id'=>'TST001','barcode'=>'1234567890123','name'=>'Sản phẩm test','price'=>10000,'quantity'=>10],
            'expected'=>['success'=>true]
        ],
        [
            'name'=>'Thêm sản phẩm trùng ID',
            'input'=>['id'=>'SP001','barcode'=>'8938505970011','name'=>'Gạo','price'=>10000,'quantity'=>5],
            'expected'=>['success'=>false]
        ]
    ],
    'cart_add' => [
        [
            'name'=>'Thêm vào giỏ hợp lệ',
            'input'=>['product_id'=>'SP001','barcode'=>'','quantity'=>1],
            'expected'=>['success'=>true]
        ],
        [
            'name'=>'Thêm vượt tồn',
            'input'=>['product_id'=>'SP001','barcode'=>'','quantity'=>99999],
            'expected'=>['success'=>false]
        ]
    ],
    'payment_cash' => [
        [
            'name'=>'Thanh toán tiền mặt không đủ',
            'input'=>['paid'=>1],
            'expected'=>['success'=>false]
        ]
    ]
];
