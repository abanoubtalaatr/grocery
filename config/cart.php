<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Maximum quantity per product per user
    |--------------------------------------------------------------------------
    |
    | Maximum number of units of a single product that a user can add to cart
    | or purchase in one order. Prevents a single user from buying entire stock.
    |
    */
    'max_quantity_per_product' => (int) env('CART_MAX_QUANTITY_PER_PRODUCT', 10),

];
