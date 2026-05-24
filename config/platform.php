<?php

// config/platform.php
return [
    'commission_rate' => env('SELLER_COMMISSION_RATE', 10),
    'large_order_threshold' => env('LARGE_ORDER_THRESHOLD', 50000),
];
