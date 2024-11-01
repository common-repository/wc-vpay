<?php
    class WC_VPay_Gateway extends WC_Payment_Gateway {

        /**
         * Is test mode active?
         *
         * @var bool
         */
        public $testmode;

        /**
         * API public key
         *
         * @var string
         */
        public $public_key;

        public $enable_webhook_token_expiry_check;

        /**
         * API secret key
         *
         * @var string
         */
        public $secret_key;

        public $customer_service_channel;

        public $transaction_charge;
        public $transaction_charge_type;


        public function __construct() {
            $this->id = 'vpay'; // payment gateway plugin ID
            $this->icon = 'https://www.vpay.africa/static/media/vpayLogo.91e11322.svg'; // URL of the icon that will be displayed on checkout page near your gateway name
            $this->has_fields = false; // in case you need a custom credit card form
            $this->method_title = 'VPay';
            $this->method_description = sprintf('VPay is a payment solution that enables cashiers and online shopping carts to increase customer loyalty by accepting payments via bank transfer, USSD and card payment and instantly confirm these payments without depending on the business owner or accountant.<a href="%1$s" target="_blank">Sign up</a> for a VPay account, and <a href="%2$s" target="_blank">get your API keys</a>.', 'https://vpay.africa', 'https://vpay.africa'); // will be displayed on the options page


            $this->supports = array(
                'products'
            );

            // Method with all the options fields
	        $this->init_form_fields();

            // Load the settings.
	        $this->init_settings();

            $this->title = $this->get_option( 'title' );
            $this->description = $this->get_option( 'description' );
            $this->enabled = $this->get_option( 'enabled' );
            $this->enable_webhook_token_expiry_check = $this->get_option( 'enable_webhook_token_expiry_check' ) === 'yes' ? true : false;
            $this->testmode = $this->get_option( 'testmode' ) === 'yes' ? true : false;
            $this->secret_key = $this->testmode ? $this->get_option( 'test_secret_key' ) : $this->get_option( 'live_secret_key' );
            $this->public_key = $this->testmode ? $this->get_option( 'test_public_key' ) : $this->get_option( 'live_public_key' );
            $this->transaction_charge = $this->get_option( 'transaction_charge' );
            $this->transaction_charge_type = $this->get_option( 'transaction_charge_type' );

            // We need custom JavaScript to obtain a token
            add_action( 'wp_enqueue_scripts', array( $this, 'payment_scripts' ) );

            add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );

            // You can also register a webhook here
            
            add_action( 'woocommerce_api_{webhook name}', array( $this, 'webhook' ) );

            // This action hook saves the settings
            add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

            add_action( 'woocommerce_receipt_' . $this->id, array( $this, 'receipt_page' ) );

            // Payment listener/API hook.
            add_action( 'woocommerce_api_wc_vpay_gateway', array( $this, 'verify_vpay_payment' ) );

            // Check if the gateway can be used.
            if ( ! $this->is_valid_for_use() ) {
                $this->enabled = false;
            }
        }

        /**
         * Check if this gateway is enabled and available in the user's country.
         */
        public function is_valid_for_use() {
    
            if ( ! in_array( get_woocommerce_currency(), apply_filters( 'woocommerce_vpay_supported_currencies', array( 'NGN' ) ) ) ) {
    
                $this->msg = sprintf( __( 'VPay does not support your store currency. Kindly set it to NGN (&#8358) <a href="%s">here</a>', 'wc-vpay' ), admin_url( 'admin.php?page=wc-settings&tab=general' ) );
    
                return false;
    
            }
    
            return true;
    
        }

        /**
         * Display vbank payment icon.
         */
        public function get_icon() {

            $base_location = wc_get_base_location();

            if ( $this->testmode ) {
                $icon = '<img src="' . WC_HTTPS::force_https_url( plugins_url( 'assets/images/vpay.svg', WC_VPAY_MAIN_FILE ) ) . '" alt="VPay Payment Option" />';
            } else {
                $icon = '<img src="' . WC_HTTPS::force_https_url( plugins_url( 'assets/images/vpay.svg', WC_VPAY_MAIN_FILE ) ) . '" alt="VPay Payment Option" />';
            }

            return apply_filters( 'woocommerce_gateway_icon', $icon, $this->id );

        }

        /**
         * Check if VPay merchant details is filled.
         */
        public function admin_notices() {
    
            if ( $this->enabled == 'no' ) {
                return;
            }
    
            // Check required fields.
            if ( ! ( $this->public_key) ) {
                echo '<div class="error"><p>' . sprintf( 'Please enter your VPay merchant details <a href="%s">here</a> to be able to use the VPay WooCommerce plugin.', admin_url( 'admin.php?page=wc-settings&tab=checkout&section=vpay' ) ) . '</p></div>';
                return;
            }
    
        }

        /**
         * Plugin options, we deal with it in Step 3 too
         */
        public function init_form_fields(){

            $this->form_fields = array(
                'enabled' => array(
                    'title'       => 'Enable/Disable',
                    'label'       => 'Enable VPay Gateway',
                    'type'        => 'checkbox',
                    'description' => 'Enable VPay as a payment option on the checkout page',
                    'default'     => 'no',
                    'desc_tip'    => true,
                ),
                'enable_webhook_token_expiry_check' => array(
                    'title'       => 'Webhook Authentication Token Expiry Check',
                    'label'       => 'Enable Webhook JWT Authentication Token Expiry Check. Webhook URL: '.admin_url('admin-ajax.php').'?action=vpay_callback',
                    'type'        => 'checkbox',
                    'description' => 'Additional security to check fresh tokens are actually generated from VPay server. This uses your secret key for validation if present.',
                    'default'     => 'yes',
                    'desc_tip'    => true,
                ),
                'title' => array(
                    'title'       => 'Title',
                    'type'        => 'text',
                    'description' => 'This controls the title which the user sees during checkout.',
                    'default'     => 'VPay',
                    'desc_tip'    => true,
                ),
                'description' => array(
                    'title'       => 'Description',
                    'type'        => 'textarea',
                    'description' => 'This controls the description which the user sees during checkout.',
                    'default'     => 'Receive instant and fast  payments via bank transfer, USSD and card payment.',
                ),
                'customer_service_channel' => array(
                    'title'       => 'Customer Service Channel',
                    'type'        => 'text',
                    'description' => 'The customer service & support channels e.g. Tel: +2348030070000, Email: support@org.com. Your customers will reach out to you through this channel to get help.',
                    'default'     => '',
                    'desc_tip'    => true,
                ),
                'transaction_charge' => array(
                    'title'       => 'Transaction Charge',
                    'type'        => 'text',
                    'description' => 'Amount to be charged to paying customer in addition to the order amount above. This amount must be greater than 0 and will share currency with order amount.',
                    'default'     => '0',
                    'desc_tip'    => true,
                ),
                'transaction_charge_type' => array(
                    'title'       => 'Transaction Charge Type',
                    'type'        => 'select',
                    'description' => 'Either flat or percentage. This indicates how the payment total should be computed using the order amount and txn_charge above. E.g. order amount = 1000, txn_charge = 5, txn_charge_type = “percentage” then total = 1000 + (1000 * 5%) = 1000 + 50 = 1050.',
                    'default'     => 'flat',
                    'desc_tip'    => true,
                    'options'     => array(
                        'flat'    => 'flat',
                        'percentage'    => 'percentage',
                        )
                ),
                'testmode' => array(
                    'title'       => 'Test mode',
                    'label'       => 'Enable Test Mode',
                    'type'        => 'checkbox',
                    'description' => 'Test mode enables you to payments before going live.',
                    'default'     => 'yes',
                    'desc_tip'    => true,
                ),
                'test_public_key' => array(
                    'title'       => 'Test Public Key',
                    'type'        => 'text',
                    'default'     => '',
                ),
                'test_secret_key' => array(
                    'title'       => 'Test Secret Key',
                    'type'        => 'text',
                    'default'     => '',
                ),
                'live_public_key' => array(
                    'title'       => 'Live Public Key',
                    'type'        => 'text',
                    'default'     => '',
                ),
                'live_secret_key' => array(
                    'title'       => 'Live Secret Key',
                    'type'        => 'text',
                    'default'     => '',
                ),
                'autocomplete_order'               => array(
                    'title'       => 'Autocomplete Order After Payment',
                    'label'       => 'Autocomplete Order',
                    'type'        => 'checkbox',
                    'class'       => 'wc-vpay-autocomplete-order',
                    'description' => 'This would complete an order after successful payment if enabled.',
                    'default'     => 'no',
                    'desc_tip'    => true,
                ),
            );
        }

        /**
        * Used for custom credit card form.
        */
        public function payment_fields() {
        }

        /*
        * Custom CSS and JS, in most cases required only when you decided to go with a custom credit card form
        */
        public function payment_scripts() {
            if ( ! is_checkout_pay_page() ) {
                return;
            }
    
            if ( $this->enabled === 'no' ) {
                return;
            }

            // do not work with card detailes without SSL unless your website is in a test mode
            // if ( ! $this->testmode && ! is_ssl() ) {
            //     return;
            // }

            $order_key = sanitize_text_field( urldecode( $_GET['key'] ) );
            $order_id  = absint( get_query_var( 'order-pay' ) );
    
            $order = wc_get_order( $order_id );
    
            $payment_method = method_exists( $order, 'get_payment_method' ) ? $order->get_payment_method() : $order->payment_method;
    
            if ( $this->id !== $payment_method ) {
                return;
            }

            $suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

            wp_enqueue_script( 'jquery' );

            wp_enqueue_script( 'wc_vpay', plugins_url( 'assets/js/vpay' . $suffix . '.js', WC_VPAY_MAIN_FILE ), array( 'jquery' ), WC_VPAY_SCRIPTS_VERSION, false );
            wp_localize_script('wc_vpay', 'MyAjax', array( 'ajaxurl' => admin_url('admin-ajax.php'))); 
            wp_enqueue_style('wc_vpay_style', plugins_url( 'assets/css/vpay' . $suffix . '.css', WC_VPAY_MAIN_FILE ), false, WC_VPAY_SCRIPTS_VERSION, 'all');

            // $vpay_params = array(
            //     'key' => $this->public_key,
            // );

            if ( is_checkout_pay_page() && get_query_var( 'order-pay' ) ) {

                $email         = method_exists( $order, 'get_billing_email' ) ? $order->get_billing_email() : $order->billing_email;
                $amount        = $order->get_total();
                $txnref        = uniqid('vpayuser') . $order_id . time();
                $the_order_id  = method_exists( $order, 'get_id' ) ? $order->get_id() : $order->id;
                $the_order_key = method_exists( $order, 'get_order_key' ) ? $order->get_order_key() : $order->order_key;
                $currency      = method_exists( $order, 'get_currency' ) ? $order->get_currency() : $order->order_currency;

                if ( $the_order_id == $order_id && $the_order_key == $order_key ) {

                    $vpay_params['email']    = $email;
                    $vpay_params['amount']   = $amount;
                    $vpay_params['txnref']   = $txnref;
                    $vpay_params['orderid']   = $the_order_id;
                    $vpay_params['currency'] = $currency;
                    $vpay_params['key'] = $this->public_key;
                    $vpay_params['customer_service_channel'] = $this->customer_service_channel;
                    $vpay_params['domain'] = $this->testmode ? 'sandbox' : 'live';
                    $vpay_params['transaction_charge'] = $this->transaction_charge;
                    $vpay_params['transaction_charge_type'] = $this->transaction_charge_type;

                }

                $acct_number = "2041564572";
                $acct_name = "QMart Stores";
                $acct_bank = "V Bank (VFD MICROFINANCE)";
                $vpay_params['acct_number']    = $acct_number;
                $vpay_params['acct_name']    = $acct_name;
                $vpay_params['acct_bank']    = $acct_bank;

                $barcode_img = "https://barcode.tec-it.com/barcode.ashx?data=This+is+a+QR+Code+by+TEC-IT+for+mobile+applications&code=MobileQRCode&multiplebarcodes=false&translate-esc=true&unit=Fit&dpi=96&imagetype=Gif&rotation=0&color=%23000000&bgcolor=%23ffffff&codepage=Default&qunit=Mm&quiet=0&hidehrt=False&eclevel=L";

                $vpay_params['barcode_img']    = $barcode_img;

                update_post_meta( $order_id, '_vpay_txn_ref', $txnref );

            }

            wp_localize_script( 'wc_vpay', 'wc_vpay_params', $vpay_params );
   
        }

        /**
         * Custom CSS and JS for admin.
         */
        public function admin_scripts() {
    
            if ( 'woocommerce_page_wc-settings' !== get_current_screen()->id ) {
                return;
            }
    
            $suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
    
            $vpay_admin_params = array(
                'plugin_url' => WC_VPAY_URL,
            );
    
            wp_enqueue_script( 'wc_vpay_admin', plugins_url( 'assets/js/vpay-admin' . $suffix . '.js', WC_VPAY_MAIN_FILE ), array(), WC_VPAY_SCRIPTS_VERSION, true );
    
            wp_localize_script( 'wc_vpay_admin', 'wc_vpay_admin_params', $vpay_admin_params );
    
        }

        /*
         * Validate fields
        */
        public function validate_fields() {

    

        }

        /*
        * Process payment
        */
        public function process_payment( $order_id ) {

            $order = wc_get_order( $order_id );

            $order->update_status('pending-payment', 'Awaiting VPay payment');
            
            // WC()->cart->empty_cart();
            
            return array(
                'result'   => 'success',
                //  'complete' => $this->complete_transaction( $order->get_id(), $_REQUEST['order_id']),
                'redirect' => $order->get_checkout_payment_url( true ),
            );
                   
        }

                    /*
            When transaction completed it is check the status 
            is transaction completed or rejected
            */
        public function complete_transaction($order_id, $transaction_id, $paymentstatus) {
            
            global $woocommerce;       
            $order = new WC_Order( $order_id );        
           
            $items = $order->get_items(); 

            $order_type = null;    
            
            foreach ($items as $item) {
                //Get the WC_Order_Item_Product object properties in an array
                $item_data = $item->get_data();
                if ($item['quantity'] > 0) {

                    //get the WC_Product object
                    $product = wc_get_product($item['product_id']);
                    
                    $is_virtual_downloadable_item = $product->is_downloadable() || $product->is_virtual();
                    
                    if (!$is_virtual_downloadable_item) {
                     //this order contains a physical product do what you want here or return false
                        $order_type = "false";
                        //processOrder($order_type);
                        break;  
                    } else {
                         $order_type = "true";
                         //processOrder($order_type);
                    }
                }
            }

            
            if($paymentstatus==='paid' || $paymentstatus === "Successful") {

                //add success note to  order 
                $order->add_order_note( sprintf( 'Payment via VPay successful (Transaction Reference: %s)', $transaction_id ) );
                
                //set order completed if order is virtual / downloadable
                if ($order_type==="true") {     
                    $order->update_status('completed');
                    wc_add_notice( '' . __( 'Thank you for shopping with us. Your account has been charged and your transaction is successful.', 'woocommerce' ), 'success' );
                } 

                if ($order_type==="false") {    
                    // set order to processing if order is not virtual /downloadable
                    $order->payment_complete();
                    wc_add_notice( '' . __( 'Thank you for shopping with us. Your account has been charged and your transaction is successful.
                    We will be shipping your order to you soon.', 'woocommerce' ), 'success' );
                }
             
                //set rediect url & encode to json 
                $redirect=$order->get_checkout_order_received_url() ;

                echo json_encode(array("statusCode"=>200, "data"=>"$redirect"));           
            }
            else{

                //Change the status to pending / unpaid
                $order->update_status('failed', __('Payment Cancelled', 'error'));    

                //Add error for the customer when we return back to the cart

                // wc_add_notice( '<strong></strong> ' . __($message, 'error' ), 'error' ); 

                // Redirect back to the last step in the checkout process
                $redirect=$order->get_cancel_order_url( $order ) ;
                echo json_encode(array("statusCode"=>200, "data"=>"$redirect"));
                exit;          
            }
            
        }
        

        public function sendRequest($gateway_url, $request_string){
            // var_dump($resp);
        }

        public function process_token_payment( $token, $order_id ) {

        }

        /**
         * Displays the payment page.
         *
         * @param $order_id
         */
        public function receipt_page( $order_id ) {

            $order = wc_get_order( $order_id );

            $icon = '<img class="wc-vpay-form-logo" src="' . WC_HTTPS::force_https_url( plugins_url( 'assets/images/vpay.svg', WC_VPAY_MAIN_FILE ) ) . '" alt="VPay Logo" />';

            if ($this->testmode) {
                $icon = '<img class="wc-vpay-form-logo" src="' . plugins_url( 'assets/images/vpay.svg', WC_VPAY_MAIN_FILE ) . '" alt="VPay Logo" />';
            }

            echo '<div id="wc-vpay-form" class="wc-vpay-form">';
            echo '
                <div class="wc-vpay-form-header">
                    <div class="left">
                        ' . $icon . '
                    </div>
                    <div class="right">
                        <p class="email"> '. (method_exists( $order, 'get_billing_email' ) ? esc_html( $order->get_billing_email() ) : esc_html( $order->billing_email ) ) .'</p>
                        <p class="header-amount"> <b>Pay <span class="vpay-primary">'. esc_html( $order->get_currency() ) . ' ' . esc_html( $order->get_total() ) .'</span> </b></p>
                    </div>
                </div>
            ';


            // <p class="amount"> <b>Transfer '. $order->get_currency() . ' ' . $order->get_total() .' to </b></p>
            echo '
                <div class="wc-vpay-form-body">



                        
                    <div id="vpay-form">
                        
                        <button class="button button-primary" onclick="openVPayPopup()"  id="vpay-payment-button"> Pay with VPay </button>
                    </div>

                </div>
            ';

            echo '</div>';

            echo '
                <div id="vpay-process-modal" class="vpay-process-modal">
                 <!-- Modal content -->
                  <div class="vpay-process-modal-content">
                    <span class="vpay-process-close">&times;</span>
					<div class="vpay-process-modal-loader">
						<div class="vpay-process-modal-content-loading-circle">
							<div class="vpay-process-modal-content-checkmark">
							</div>
						</div>
					</div>
                    <div class="vpay-process-modal-content-text">
                    </div>
                  </div>
                </div>
            ';
        }

        public function verify_vpay_payment() {

            global $woocommerce;

            if ( isset( $_REQUEST['txnref'] ) ) {
                $payment_reference = sanitize_text_field( $_REQUEST['txnref'] );
            } else {
                $payment_reference = false;
            }

            @ob_clean();
            if ($payment_reference) {
                // TODO: Should Verify Payment With Reference

                $order_details = explode( '_', $payment_reference );
                $order_id      = (int) $order_details[0];
                $order         = wc_get_order( $order_id );

                if ( in_array( $order->get_status(), array( 'processing', 'completed', 'on-hold' ) ) ) {

                    wp_redirect( $this->get_return_url( $order ) );

                    exit;

                }

                $order->payment_complete( $payment_reference );
                $order->add_order_note( sprintf( 'Payment via VPay successful (Transaction Reference: %s)', $payment_reference ) );

                if ( $this->is_autocomplete_order_enabled( $order ) ) {
                    $order->update_status( 'completed' );
                }
                // Empty cart
                $woocommerce->cart->empty_cart();
                wp_redirect($order->get_checkout_order_received_url() );


                wp_redirect( $this->get_return_url( $order ) );
                exit;
            }

            wp_redirect( wc_get_page_permalink( 'cart' ) );

		    exit;
            
        }

        /*
        * Webhook for payments.
        */
        public function webhook() {

        
                   
        }


        // have a functon tht checks the payment status
        // 



        /**
         * Checks if autocomplete order is enabled for the payment method.
         *
         */
        protected function is_autocomplete_order_enabled( $order ) {
            $autocomplete_order = false;

            $payment_method = $order->get_payment_method();

            $vpay_settings = get_option('woocommerce_' . $payment_method . '_settings');

            if ( isset( $vpay_settings['autocomplete_order'] ) && 'yes' === $vpay_settings['autocomplete_order'] ) {
                $autocomplete_order = true;
            }

            return $autocomplete_order;
        }
    }