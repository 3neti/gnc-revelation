<?php

return [
    'defaults' => [
        'buyer' => [
            'birthdate' => now()->subYears(30)->toDateString(),
            'gross_monthly_income' => 15000,
            'regional' => false,
        ],
    ],
    'limits' => [
        'min_borrowing_age' => 21,
        'max_borrowing_age' => 65,
    ],
    'default_regional' => env('DEFAULT_REGIONAL_BORROWER', false),
    'lending_institutions' => [
        'hdmf' => [
            'name' => 'Home Development Mutual Fund',
            'alias' => 'Pag-IBIG',
            'type' => 'government financial institution',
            'borrowing_age' => [
                'minimum' => 18,
                'maximum' => 60,
                'offset' => 0,
            ],
            'maximum_term' => 30,
            'maximum_paying_age' => 70,
            'buffer_margin' => 0.1,
        ],
        'rcbc' => [
            'name' => 'Rizal Commercial Banking Corporation',
            'alias' => 'RCBC',
            'type' => 'universal bank',
            'borrowing_age' => [
                'minimum' => 18,
                'maximum' => 60,
                'offset' => -1,
            ],
            'maximum_term' => 20,
            'maximum_paying_age' => 65,
            'buffer_margin' => 0.15,
        ],
        'cbc' => [
            'name' => 'China Banking Corporation',
            'alias' => 'CBC',
            'type' => 'universal bank',
            'borrowing_age' => [
                'minimum' => 18,
                'maximum' => 60,
                'offset' => -1,
            ],
            'maximum_term' => 20,
            'maximum_paying_age' => 65,
            'buffer_margin' => 0.15,
        ],
    ],
    'default_lending_institution' => env('DEFAULT_LENDING_INSTITUTION', 'hdmf'),
    'default_seller_code' => env('DEFAULT_SELLER_CODE', 'AA537'),
    'default_disposable_income_multiplier' => env('DEFAULT_DISPOSABLE_INCOME_MULTIPLIER', 0.35),
    'default_buffer_margin' => env('DEFAULT_BUFFER_MARGIN', 0.1),
];
