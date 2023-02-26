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

/**
 * WC_w3pay class.
 *
 */
class WC_W3pay_wp {

    protected static $instance;

    /**
     * @return WC_W3pay_wp
     */
    public static function instance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    /**
     *
     */
    public function pageLoad(){
        $PluginPaths = WC_W3pay_wp::instance()->getPluginPaths();
        // Set the right paths
        if(!defined('_W3PAY_w3payFrontend_')){ define('_W3PAY_w3payFrontend_', $PluginPaths['w3payFrontend']); }
        if(!defined('_W3PAY_w3payBackend_')){ define('_W3PAY_w3payBackend_', $PluginPaths['w3payBackend']); }
        // Include php class widget wW3pay.php
        include_once(_W3PAY_w3payBackend_. '/widget/wW3pay.php');

        \wW3pay::instance()->showLoad(['checkAuthRequired'=>false]);
        exit;
    }

    /**
     * @param $order_id
     */
    public function pagePayment($order_id){
        $PaymentData = WC_W3pay_wp::instance()->getPaymentData($order_id);
        if(!empty($PaymentData['error'])){
            echo $PaymentData['data'];
            exit;
        }
        $PluginPaths = WC_W3pay_wp::instance()->getPluginPaths();

        WC_W3pay_wp::instance()->addSessionStartPay($order_id);

        // Set the right paths
        if(!defined('_W3PAY_w3payFrontend_')){ define('_W3PAY_w3payFrontend_', $PluginPaths['w3payFrontend']); }
        if(!defined('_W3PAY_w3payBackend_')){ define('_W3PAY_w3payBackend_', $PluginPaths['w3payBackend']); }
        // Include php class widget wW3pay.php
        include_once(_W3PAY_w3payBackend_. '/widget/wW3pay.php');
        // Set prices to receive tokens
        $orderId = $PaymentData['PaymentData']['orderId']; // Please enter your order number
        $payAmountInReceiveToken = $PaymentData['PaymentData']['payAmountInReceiveToken']; // Please enter a price for the order

        $multicurrencyIsActive = \wW3pay::instance()->multicurrencyIsActive();
        if(empty($multicurrencyIsActive['error'])){
            //If multicurrency is enabled in the settings, then we can set the price in fiat currency
            $OrderData = [
                'orderId' => $orderId,
                'fiatData' => ['currency' => $PaymentData['PaymentData']['currency'], 'amount' => $payAmountInReceiveToken]
            ];
        } else {
            if($PaymentData['PaymentData']['currency']!='USD'){
                echo 'Only USD currency or enable multiplicity in w3pay settings.';
                exit;
            }
            // Set the price in tokens to receive
            $OrderData = [
                'orderId' => $orderId,
                'payAmounts' => [
                    ['chainId' => 97, 'payAmountInReceiveToken' => $payAmountInReceiveToken], // Binance Smart Chain Mainnet - Testnet (BEP20)
                    ['chainId' => 56, 'payAmountInReceiveToken' => $payAmountInReceiveToken], // Binance Smart Chain Mainnet (BEP20)
                    ['chainId' => 137, 'payAmountInReceiveToken' => $payAmountInReceiveToken], // Polygon (MATIC)
                    ['chainId' => 43114, 'payAmountInReceiveToken' => $payAmountInReceiveToken], // Avalanche C-Chain
                    ['chainId' => 250, 'payAmountInReceiveToken' => $payAmountInReceiveToken], // Fantom Opera
                ],
            ];
        }

        $showPayment = \wW3pay::instance()->showPayment([
            'checkPaymentPageUrl'=>$PaymentData['PaymentData']['checkPaymentPageUrl'],
            'OrderData' => $OrderData,
        ]);

        $content = '';
        if(!empty($showPayment['head'])){ $content .= $showPayment['head']; } // Show js, css files
        if(!empty($showPayment['html'])){ $content .= $showPayment['html']; } // Show html content

        $dataHtml = [
            'title' => 'W3PAY Web3 Crypto Payments',
            'description' => 'W3PAY Web3 Crypto Payments',
            'content' => $content,
        ];

        $PluginPaths = WC_W3pay_wp::instance()->getPluginPaths();
        echo $this->getTemplateHtml($PluginPaths['template'], $dataHtml);
        exit;
    }

    /**
     *
     */
    public function pageCheckPayment(){
        $CheckPaymentData = WC_W3pay_wp::instance()->getCheckPaymentData();
        if(!empty($CheckPaymentData['error'])){
            echo $CheckPaymentData['data'];
            exit;
        }
        $PluginPaths = WC_W3pay_wp::instance()->getPluginPaths();

        $htmlSuccess = ' ';

        // Set the right paths
        if(!defined('_W3PAY_w3payFrontend_')){ define('_W3PAY_w3payFrontend_', $PluginPaths['w3payFrontend']); }
        if(!defined('_W3PAY_w3payBackend_')){ define('_W3PAY_w3payBackend_', $PluginPaths['w3payBackend']); }
        // Include php class widget wW3pay.php
        include_once(_W3PAY_w3payBackend_. '/widget/wW3pay.php');

        $showCheckPayment = \wW3pay::instance()->showCheckPayment([
            'htmlSuccess' => ' ',
            'htmlError' => '<a class="checkPaymentBtn" href="/">Home</a>',
        ]);

        if(!empty($showCheckPayment['CheckPaymentData']['showSuccess'])){
            $orderId = $showCheckPayment['CheckPaymentData']['checkSign']['checkSign']['orderId'];
            // TODO The administrator can mark $orderId the successful payment in the database.
            WC_W3pay_wp::instance()->paySave($showCheckPayment, true);

            $order_received_url = $this->getOrderReceivedUrl($orderId);
            $htmlSuccess = '<div class="linkResultPay"><a class="checkPaymentBtn" href="'.$order_received_url.'">View order</a></div>';
        } else {
            if(!empty($showCheckPayment['CheckPaymentData']['checkSign']['typeError']) && $showCheckPayment['CheckPaymentData']['checkSign']['typeError']=='SignaturFalse'){
                //$orderId = $showCheckPayment['CheckPaymentData']['checkSign']['checkSign']['orderId'];
                // TODO The administrator can mark $orderId the failed payment in the database.
                WC_W3pay_wp::instance()->paySave($showCheckPayment, false);
            }
        }

        $content = '';
        if(!empty($showCheckPayment['head'])){ $content .= $showCheckPayment['head']; } // Show js, css files
        if(!empty($showCheckPayment['html'])){ $content .= $showCheckPayment['html']; } // Show html content

        $dataHtml = [
            'title' => 'W3PAY Backend check of payment',
            'description' => 'W3PAY Backend check of payment',
            'content' => $content.$htmlSuccess,
        ];

        $PluginPaths = WC_W3pay_wp::instance()->getPluginPaths();
        echo $this->getTemplateHtml($PluginPaths['template'], $dataHtml);
        exit;
    }

    /**
     * @param $template
     * @param array $data
     * @return false|string
     */
    public function getTemplateHtml($template, $data = []){
        ob_start();
        try {
            include $template;
        } catch (Exception $e) {
            echo 'Caught exception: ',  $e->getMessage(), "\n";
        }
        $contents = ob_get_contents();
        ob_end_clean();
        return $contents;
    }

    /**
     * @param $showCheckPayment
     * @param bool $PaySuccess
     * @return array
     */
    public function paySave($showCheckPayment, $PaySuccess = false){
        if(empty($showCheckPayment['CheckPaymentData']['checkSign']['checkSign']['orderId'])){
            return ['error' => 1, 'data' => 'checkSign orderId is empty'];
        }
        $order_id = $showCheckPayment['CheckPaymentData']['checkSign']['checkSign']['orderId'];

        $PaymentData = WC_W3pay_wp::instance()->getPaymentData($order_id);
        if(!empty($PaymentData['error'])){
            return $PaymentData;
        }

        $chainid = $showCheckPayment['CheckPaymentData']['chainid'];
        $tx = $showCheckPayment['CheckPaymentData']['tx'];
        $Link_payment_result = $PaymentData['PaymentData']['checkPaymentPageUrl'].'&chainid='.$chainid.'&tx='.$tx;

        $order = wc_get_order($order_id);
        $wc_order_data = $order->get_data();

        $order->add_meta_data( 'order_id', $order_id, true);
        $order->add_meta_data( 'chain_id', $chainid, true);
        $order->add_meta_data( 'transaction_hash', $tx, true);
        $order->add_meta_data( 'link_payment_result', $Link_payment_result, true);
        $order->save_meta_data();

        if($PaySuccess){
            $order->payment_complete();
            WC_w3pay_Logger::log( 'Order #' . $order_id . ' completed' );
        } else {
            $order->update_status( 'failed' );
            WC_w3pay_Logger::log( 'Order #' . $order_id . ' failed' );
        }

        return ['error' => 0, 'data' => 'Success'];
    }

    /**
     * @return array
     */
    public function getCheckPaymentData()
    {
        $PluginPaths = WC_W3pay_wp::instance()->getPluginPaths();

        if(!file_exists($PluginPaths['wW3payPath'])){
            return ['error' => 1, 'data' => 'File wW3pay not found'];
        }

        if(!file_exists($PluginPaths['sSettingsPath'])){
            return ['error' => 1, 'data' => 'File sSettings not found. The administrator can make <a target="blank" href="'.$PluginPaths['settings_url'].'">settings</a>.'];
        }

        $CheckPaymentData=[
            'wW3payPath' => $PluginPaths['wW3payPath'],
            'w3pay_url' => $PluginPaths['w3pay_url'],
        ];
        return ['error' => 0, 'data' => 'Success', 'CheckPaymentData'=>$CheckPaymentData];
    }

    /**
     * @param $order_id
     * @return array
     */
    public function getPaymentData($order_id){
        $PluginPaths = WC_W3pay_wp::instance()->getPluginPaths();

        $wW3payPath = $PluginPaths['wW3payPath'];
        if(!file_exists($wW3payPath)){
            return ['error' => 1, 'data' => 'File wW3pay not found'];
        }

        $sSettingsPath = $PluginPaths['sSettingsPath'];
        if(!file_exists($sSettingsPath)){
            return ['error' => 1, 'data' => 'File sSettings not found. The administrator can make <a target="blank" href="'.$PluginPaths['settings_url'].'">settings</a>.'];
        }

        $order = wc_get_order($order_id);
        if(!$order){
            return ['error' => 1, 'data' => 'Order not found'];
        }
        $wc_order_data = $order->get_data();

        /*echo '<pre>';
        print_r($wc_order_data);
        echo '</pre>';*/

        if(empty($wc_order_data['status'])){
            return ['error' => 1, 'data' => 'Order status is empty'];
        }
        if($wc_order_data['status']!='pending'){
            return ['error' => 1, 'data' => 'Order status is not Pending payment'];
        }
        if(empty($wc_order_data['id'])){
            return ['error' => 1, 'data' => 'Order id is empty'];
        }
        if(empty($wc_order_data['currency'])){
            return ['error' => 1, 'data' => 'Order currency is empty'];
        }
        /*if($wc_order_data['currency']!='USD'){
            return ['error' => 1, 'data' => 'Select USD currency'];
        }*/
        $currency = $wc_order_data['currency'];
        if(empty($wc_order_data['total'])){
            return ['error' => 1, 'data' => 'Order total is empty'];
        }
        if(empty($wc_order_data['order_key'])){
            return ['error' => 1, 'data' => 'Order order_key is empty'];
        }

        // Set prices to receive tokens
        $orderId = (int)$wc_order_data['id']; // Please enter your order number
        $payAmountInReceiveToken = floatval($wc_order_data['total']);; // Please enter a price for the order

        $w3pay_url = $PluginPaths['w3pay_url'];

        $webhook_url = $PluginPaths['webhook_url'];

        $pay_url = add_query_arg( 'wc-api', 'wc_w3pay', $webhook_url );
        $pay_url = add_query_arg( 'page', 'payment', $pay_url);
        $pay_url = add_query_arg( 'order', $order_id, $pay_url);

        $order_received_url = $this->getOrderReceivedUrl($order_id);

        $PaymentData=[
            'orderId' => $orderId,
            'payAmountInReceiveToken' => $payAmountInReceiveToken,
            'wW3payPath' => $wW3payPath,
            'w3pay_url' => $w3pay_url,
            'pay_url' => $pay_url,
            'checkPaymentPageUrl' => $PluginPaths['checkPaymentPageUrl'],
            'order_received_url' => $order_received_url,
            'currency' => $currency,
        ];
        return ['error' => 0, 'data' => 'Success', 'PaymentData'=>$PaymentData];
    }

    /**
     * @return array
     */
    public function getPluginPaths(){
        $PLUGIN_PATH = str_replace("\\", "/", WC_w3pay_PLUGIN_PATH);
        $PLUGIN_PATH = str_replace("//", "/", $PLUGIN_PATH);

        $wW3payPath = $PLUGIN_PATH . '/w3pay/w3payBackend/widget/wW3pay.php';
        $sSettingsPath = $PLUGIN_PATH . '/w3pay/w3payBackend/settings/sSettings.php';
        $sAssistantPath = $PLUGIN_PATH . '/w3pay/w3payBackend/settings/sAssistant.php';
        $template = $PLUGIN_PATH . '/template/template.php';

        $home_url = trailingslashit( get_home_url() );
        $admin_url = get_admin_url();
        $admin_url = str_replace($home_url, "/", $admin_url);

        $w3pay_url = plugins_url( 'w3pay', WC_w3pay_MAIN_FILE );
        $w3pay_url = str_replace($home_url, "/", $w3pay_url);

        $plugin_url = plugins_url( '', WC_w3pay_MAIN_FILE );
        $plugin_url = str_replace($home_url, "/", $plugin_url);

        $webhook_url = add_query_arg( 'wc-api', 'wc_w3pay', $home_url);
        $webhook_url = str_replace($home_url, "/", $webhook_url);

        $settingsSend_url = add_query_arg( 'page', 'load', $webhook_url);

        $icon_url = $plugin_url.'/public/images/icon.png';
        $imgb_url = $plugin_url.'/public/images/imgb.png';

        $checkPaymentPageUrl = add_query_arg( 'page', 'checkpayment', $webhook_url);
        //$checkPaymentPageUrl = add_query_arg( 'order', $order_id, $checkPaymentPageUrl);

        $w3payFrontend = $w3pay_url.'/w3payFrontend';
        $w3payBackend = __DIR__.'/../w3pay/w3payBackend';

        $PluginPaths = [
            'PluginPath' => $PLUGIN_PATH,
            'wW3payPath' => $wW3payPath,
            'sSettingsPath' => $sSettingsPath,
            'sAssistantPath' => $sAssistantPath,
            'template' => $template,
            'home_url' => $home_url,
            'w3pay_url' => $w3pay_url,
            'webhook_url' => $webhook_url,
            'settings_url' => $admin_url.'admin.php?page=wc-settings&tab=checkout&section=w3pay',
            'plugin_url' => $plugin_url,
            'admin_url' => $admin_url,
            'icon_url' => $icon_url,
            'imgb_url' => $imgb_url,
            'w3payFrontend' => $w3payFrontend,
            'w3payBackend' => $w3payBackend,
            'settingsSend_url' => $settingsSend_url,
            'checkPaymentPageUrl' => $checkPaymentPageUrl,
        ];
        return $PluginPaths;
    }

    /**
     * @param $order_id
     * @return string
     */
    public function getOrderReceivedUrl($order_id){
        $order = wc_get_order($order_id);
        if(!$order){
            return '';
        }
        $wc_order_data = $order->get_data();

        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        if(session_status() === PHP_SESSION_ACTIVE && !empty($_SESSION['w3pay'][$wc_order_data['id']]['startPay'])) {
            // public link with key. Show only to buyer
            $order_received_url = wc_get_endpoint_url( 'order-received', $wc_order_data['id'], wc_get_checkout_url() );
            $order_received_url = add_query_arg( 'key', $wc_order_data['order_key'], $order_received_url );
            $order_received_url = esc_url( $order_received_url);
        } else {
            // More about ordering with authorization
            $order_received_url = $order->get_view_order_url();
        }
        return $order_received_url;
    }

    /**
     * @param $order_id
     * @return bool
     */
    public function addSessionStartPay($order_id){
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        if(session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION['w3pay'][$order_id]['startPay'] = 1;
            return true;
        }
        return false;
    }

}

new WC_W3pay_wp();
