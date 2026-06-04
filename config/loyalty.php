<?php

return [
    /*
    | Points required to reach each tier (inclusive minimum).
    | Ordered from lowest to highest threshold.
    */
    'tiers' => [
        ['key' => 'silver', 'name' => 'Silver', 'min_points' => 0],
        ['key' => 'gold', 'name' => 'Gold', 'min_points' => 1000],
        ['key' => 'platinum', 'name' => 'Platinum', 'min_points' => 10000],
        ['key' => 'diamond', 'name' => 'Diamond', 'min_points' => 20000],
    ],

    /*
    | Monetary value of rewards per point (e.g. 0.01 = 100 pts → £1.00).
    */
    'point_value' => 0.01,

    'currency' => 'GBP',

    'benefits_by_tier' => [
        'silver' => [
            ['title' => 'Earn Points', 'description' => 'Earn 1 point for every £1 spent'],
            ['title' => 'Member Deals', 'description' => 'Access to seasonal promotions'],
        ],
        'gold' => [
            ['title' => '2x Points', 'description' => 'Earn 2x points for every purchase'],
            ['title' => 'Birthday Bonus', 'description' => '500 bonus points on your birthday'],
            ['title' => 'Exclusive Deals', 'description' => 'Access to member-only promotions'],
            ['title' => 'Priority Support', 'description' => 'Faster customer service response'],
        ],
        'platinum' => [
            ['title' => '3x Points', 'description' => 'Earn 3x points on selected categories'],
            ['title' => 'Free Delivery', 'description' => 'Free delivery on orders over £30'],
            ['title' => 'Early Access', 'description' => 'Shop new products before general release'],
            ['title' => 'Dedicated Support', 'description' => 'Priority queue for customer service'],
        ],
        'diamond' => [
            ['title' => '5x Points', 'description' => 'Maximum points multiplier on all orders'],
            ['title' => 'Concierge Support', 'description' => '24/7 dedicated support line'],
            ['title' => 'VIP Events', 'description' => 'Invitations to exclusive member events'],
            ['title' => 'Premium Gifts', 'description' => 'Quarterly surprise rewards'],
        ],
    ],
];
