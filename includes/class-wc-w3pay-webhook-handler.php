<?php
/**
 * W3PAY - Web3 Crypto Payments
 * Website: https://w3pay.dev
 * GitHub Website: https://w3pay.github.io/
 * GitHub: https://github.com/w3pay
 * GitHub plugin: https://github.com/w3pay-word-press
 * Copyright (c)
 */

if (!defined('ABSPATH')) {
	exit;
}

/**
 * WC_w3pay_Webhook_Handler class.
 *
 */
class WC_w3pay_Webhook_Handler {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'woocommerce_api_wc_w3pay', [ $this, 'handle_webhook' ] );
	}

	/**
	 * Check incoming requests for w3pay Webhook data and process them.
	 */
	public function handle_webhook() {
	    if(empty($_GET['page'])){ echo 'page not found'; exit; }
        $page=$_GET['page'];

        if($page=='load'){
            status_header( 200 );
            WC_W3pay_wp::instance()->pageLoad();
        }

	    if($page=='payment'){
            status_header( 200 );
            if(empty($_GET['order'])){ echo 'order is empty'; exit; }
            WC_W3pay_wp::instance()->pagePayment($_GET['order']);
        }

        if($page=='checkpayment'){
            status_header( 200 );
            WC_W3pay_wp::instance()->pageCheckPayment();
        }
        status_header( 404 );
        exit;
	}
}

new WC_w3pay_Webhook_Handler();
