<?php

use Brick\Math\RoundingMode;

return [
    'defaults' => [
        'buyer' => [
            'birthdate' => now()->subYears(30)->toDateString(),
            'gross_monthly_income' => 15000,
            'regional' => false,
            'interest_rate' => env('DEFAULT_INTEREST_RATE'),
            'down_payment_term' => env('DEFAULT_DOWN_PAYMENT_TERM'),
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
    'rounding_mode' => env('MONEY_ROUNDING_MODE', RoundingMode::CEILING),
    'default_currency' => env('DEFAULT_CURRENCY', 'PHP'),
    'property' => [

        'market' => [

            'segment' => [
                'open' => env('MARKET_SEGMENT_OPEN', 'Open Market'),
                'economic' => env('MARKET_SEGMENT_ECONOMIC', 'Economic'),
                'socialized' => env('MARKET_SEGMENT_SOCIALIZED', 'Socialized'),
            ],

            'ceiling' => [

                // Ceilings for horizontal development (typically BP 957)
                'horizontal' => [
                    'socialized' => env('HORIZONTAL_SOCIALIZED_MARKET_CEILING', 850000),
                    'economic' => env('HORIZONTAL_ECONOMIC_MARKET_CEILING', 2500000),
                    'open' => env('HORIZONTAL_OPEN_MARKET_CEILING', 10000000),
                ],

                // Ceilings for vertical development (typically BP 220)
                'vertical' => [
                    'socialized' => env('VERTICAL_SOCIALIZED_MARKET_CEILING', 1800000),
                    'economic' => env('VERTICAL_ECONOMIC_MARKET_CEILING', 2500000),
                    'open' => env('VERTICAL_OPEN_MARKET_CEILING', 10000000),
                ],
            ],

            'disposable_income_multiplier' => [
                'socialized' => env('SOCIALIZED_MARKET_DISPOSABLE_MULTIPLIER', 0.35),
                'economic' => env('ECONOMIC_MARKET_DISPOSABLE_MULTIPLIER', 0.35),
                'open' => env('OPEN_MARKET_DISPOSABLE_MULTIPLIER', 0.30),
            ],

            'loanable_value_multiplier' => [
                'socialized' => env('SOCIALIZED_MARKET_LOANABLE_MULTIPLIER', 1.00),
                'economic' => env('ECONOMIC_MARKET_LOANABLE_MULTIPLIER', 0.95),
                'open' => env('OPEN_MARKET_LOANABLE_MULTIPLIER', 0.90),
            ],
        ],

        'default' => [
            'processing_fee' => env('PROPERTY_DEFAULT_PROCESSING_FEE', 10000),
            'percent_dp' => env('PROPERTY_DEFAULT_PERCENT_DP', 10 / 100), // 10%
            'dp_term' => env('PROPERTY_DEFAULT_DP_TERM', 12), // in months
            'percent_mf' => env('PROPERTY_DEFAULT_PERCENT_MISC_FEES', 8.5 / 100), // 8.5%
        ],
    ],
];
