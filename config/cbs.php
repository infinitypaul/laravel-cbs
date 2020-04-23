<?php


return [
    /**
     * Client ID From CBS
     *
     */
    'clientId' => getenv('CBS_CLIENT_ID'),

    /**
     * Secret Key From CBS
     *
     */
    'secret' => getenv('CBS_SECRET'),

    /**
     * switch to live or test
     *
     */
    'mode' => getenv('CBS_MODE', 'test'),

    /**
     * CBS Test Payment URL
     *
     */
    'testUrl' => getenv('CBS_TEST_BASE_URL'),

    /**
     * CBS Live Payment URL
     *
     */
    'liveURL' => getenv('CBS_LIVE_BASE_URL'),


    /**
     * Revenue Head
     *
     */
    'revenueHead' => getenv('CBS_REVENUE_HEAD'),

    /**
     * Revenue Head
     *
     */
    'categoryId' => getenv('CBS_CATEGORY_ID'),
];
