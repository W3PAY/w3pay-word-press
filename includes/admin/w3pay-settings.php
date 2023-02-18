<?php
/**
 * W3PAY - Web3 Crypto Payments
 * Website: https://w3pay.dev
 * GitHub Website: https://w3pay.github.io/
 * GitHub: https://github.com/w3pay
 * GitHub plugin: https://github.com/w3pay-word-press
 * Copyright (c)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$wc_w3pay_settings = [
	'enabled' => [
		'title'       => 'Enable/Disable',
		'type'        => 'checkbox',
		'label'       => 'Enable w3pay',
		'default'     => 'no'
	],
    'title' => [
        'title'       => 'Title',
        'description' => 'This controls the title which the user sees during checkout.',
        'type'        => 'textarea',
        'default'     => 'W3PAY Web3 Crypto Payments',
        'desc_tip'    => true
    ],
	'description' => [
		'title'       => 'Description',
		'description' => 'This controls the description which the user sees during checkout.',
		'type'        => 'textarea',
		'default'     => 'Payment with crypto using a Trust Wallet or MetaMask wallets.',
		'desc_tip'    => true
	],
];

return apply_filters( 'wc_w3pay_settings', $wc_w3pay_settings );
