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
 * WC_Gateway_w3pay class.
 *
 * @extends WC_Payment_Gateway
 */
class WC_Gateway_w3pay extends WC_Payment_Gateway {

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->id                 = 'w3pay';
		$this->icon               = '';
		$this->has_fields         = true;
		$this->method_title       = 'W3PAY Web3 Crypto Payments';
		$this->method_description = 'Redirects customers to w3pay checkout page to complete their payment.';
		$this->supports           = [
			'products',
			'subscriptions',
			'subscription_cancellation',
			'gateway_scheduled_payments',
		];

		// Load the form fields.
		$this->init_form_fields();
		// Load the settings.
		$this->init_settings();

		// Get setting values.
        //$this->title       = 'W3PAY Web3 Crypto Payments';
		$this->enabled     = $this->get_option( 'enabled' );
        $this->title       = $this->get_option( 'title' );
		$this->description = $this->get_option( 'description' );

		add_action( 'wp_enqueue_scripts', [ $this, 'payment_scripts' ] );
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, [ $this, 'process_admin_options' ] );
	}

	/**
	 * Initialise Gateway Settings Form Fields
	 */
	public function init_form_fields() {
		$this->form_fields = require WC_w3pay_PLUGIN_PATH . '/includes/admin/w3pay-settings.php';
	}

	/**
	 * Payment form on checkout page
	 */
	public function payment_fields() {
        $PluginPaths = WC_W3pay_wp::instance()->getPluginPaths();
	    echo '<img src="'.$PluginPaths['imgb_url'].'">';
	    echo '<div class="wp-w3pay-description">'.$this->description.'</div>';
		//$cart = WC()->cart->get_cart();
	}

	/**
	 * Outputs scripts and styles used for w3pay payment
	 */
	public function payment_scripts() {
		if ($this->enabled === 'no') {
			return;
		}
        wp_register_script( 'woocommerce_w3pay', plugins_url( 'public/js/wp-w3pay.js', WC_w3pay_MAIN_FILE), [], false, true );
        wp_enqueue_script( 'woocommerce_w3pay' );

        wp_register_style( 'w3pay_styles', plugins_url( 'public/css/wp-w3pay.css', WC_w3pay_MAIN_FILE), [], false);
        wp_enqueue_style( 'w3pay_styles' );
	}

	/**
	 * Process the payment
	 *
	 * @param int  $order_id Reference.
	 *
	 * @throws Exception If payment will not be accepted.
	 * @return array|void
	 */
	public function process_payment( $order_id ) {
		global $woocommerce;

        $cart = WC()->cart->get_cart();

        $products = array_filter( $cart, function( $e ) {
            $product = $e['data'];
            return $product->is_type( 'simple' );
        });

        $subscriptions = array_filter( $cart, function( $e ) {
            $product = $e['data'];
            return $product->is_type( 'subscription' );
        });

        if (count($subscriptions)) {
            wc_add_notice( __('Payment error: ', 'woothemes') . 'Subscription products are not supported.');
            return;
        }

        $PaymentData = WC_W3pay_wp::instance()->getPaymentData($order_id);
        if(!empty($PaymentData['error'])){
            wc_add_notice( __('Payment error: ', 'woothemes') . $PaymentData['data'] );
            return;
        }

        return [
            'result' => 'success',
            'redirect' => $PaymentData['PaymentData']['pay_url'],
        ];
	}

    /**
     * Show Admin settings W3PAY
     */
    public function admin_options() {
        $PluginPaths = WC_W3pay_wp::instance()->getPluginPaths();
        /*echo '<pre>';
        print_r($PluginPaths);
        echo '</pre>';*/

        echo '<a href="'.$PluginPaths['settings_url'].'">Settings</a> | <a href="'.$PluginPaths['settings_url'].'&settings=3">Transactions</a> | <a href="'.$PluginPaths['settings_url'].'&settings=2">Standard settings</a>';

        if(!empty($_GET['settings']) && $_GET['settings']==2){
            ?><table class="form-table"><?php $this->generate_settings_html(); ?></table><?php
        } elseif(!empty($_GET['settings']) && $_GET['settings']==3) {
            // Set the right paths
            if(!defined('_W3PAY_w3payFrontend_')){ define('_W3PAY_w3payFrontend_', $PluginPaths['w3payFrontend']); }
            if(!defined('_W3PAY_w3payBackend_')){ define('_W3PAY_w3payBackend_', $PluginPaths['w3payBackend']); }
            // Include php class widget wW3pay.php
            include_once(_W3PAY_w3payBackend_. '/widget/wW3pay.php');

            $Transactions = \wW3pay::instance()->showTransactions([
                'checkAuthRequired'=>false,
                'sendurl'=>$PluginPaths['settingsSend_url'],
                'checkPaymentPageUrl'=>$PluginPaths['checkPaymentPageUrl'],
            ]);
            if(!empty($Transactions['head'])){ echo $Transactions['head']; } // Show js, css files
            if(!empty($Transactions['html'])){ echo $Transactions['html']; } // Show html content

            ?><style>.woocommerce form .submit { display: none; }</style><?php
        } else {
            // Set the right paths
            if(!defined('_W3PAY_w3payFrontend_')){ define('_W3PAY_w3payFrontend_', $PluginPaths['w3payFrontend']); }
            if(!defined('_W3PAY_w3payBackend_')){ define('_W3PAY_w3payBackend_', $PluginPaths['w3payBackend']); }
            // Include php class widget wW3pay.php
            include_once(_W3PAY_w3payBackend_. '/widget/wW3pay.php');

            $FormSettings = \wW3pay::instance()->showFormSettings(['checkAuthRequired'=>false, 'cms'=> 'wp', 'sendurl'=>$PluginPaths['settingsSend_url']]);
            if(!empty($FormSettings['head'])){ echo $FormSettings['head']; } // Show js, css files
            if(!empty($FormSettings['html'])){ echo $FormSettings['html']; } // Show html content

            ?><style>.woocommerce form .submit { display: none; }</style><?php
        }
	}

	public function validate_title_field( $key, $value ) {
		if ( ! isset( $value ) || empty( $value ) ) {
			return 'w3pay';
		}
		return $value;
	}
}
