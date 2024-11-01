<?php
	function pay_load(){
        $transactionref = sanitize_text_field($_POST['transactionref']);
        $order_id = sanitize_text_field($_POST['orderid']);
        $paymentstatus = sanitize_text_field($_POST['paymentstatus']);
		
		if(class_exists('WC_VPay_Gateway')){
			$objects = new WC_VPay_Gateway;
		}

		$objects->complete_transaction($order_id, $transactionref, $paymentstatus);
		wp_die();
 	}

     function encode($str){
         //return rtrim(strtr(base64_encode($str), '+/', '-_'), '='); // base64 encode string
         return rtrim(strtr(base64_encode($str), '+/', '-_'), '='); // base64 encode string
     }

    function validate_jwt_token($token, $secret_key, $enable_webhook_token_expiry_check) {
        // Split the token into header, payload, and signature
        list($header, $payload, $signature) = explode('.', $token);

        // Decode the header and payload
        $decoded_header = json_decode(base64_decode($header), true);
        $decoded_payload = json_decode(base64_decode($payload), true);

        if(!$decoded_payload){
            return false;
        }

        if($enable_webhook_token_expiry_check){
            if($decoded_payload['exp'] - time() < 0){
                return false; //expired token
            }
        }

        // Verify the signature
        $valid_signature = hash_hmac('SHA256', "$header.$payload", $secret_key, true);
        $base64_signature = encode($valid_signature);

        if (hash_equals($signature, $base64_signature)) {
            // Signature is valid
            return true;
        } else {
            // Signature is invalid
            return false;
        }
    }


    function webhook_callback(){
        global $wpdb;
        $json_body = file_get_contents('php://input');
        $json = json_decode($json_body, true);

         $transactionref = sanitize_text_field($json['transactionref']);

         $tbl = $wpdb->prefix.'postmeta';
         $prepare_guery = $wpdb->prepare( "SELECT post_id FROM $tbl where meta_key ='_vpay_txn_ref' and meta_value = '%s'", $transactionref);
         $get_values = $wpdb->get_col( $prepare_guery );

         if($get_values){
             $post_id = $order_id = $get_values[0];
         }else{
             $post_id = 0;
         }

         if(class_exists('WC_VPay_Gateway')){
             $objects = new WC_VPay_Gateway;
         }

         // webhook auth
         if($objects->secret_key){
             if(!isset($_SERVER['HTTP_X_PAYLOAD_AUTH']) || !$_SERVER['HTTP_X_PAYLOAD_AUTH']){
                 wp_die('Webhook authentication missing in request');
             }
             // validate the token
             if(!validate_jwt_token($_SERVER['HTTP_X_PAYLOAD_AUTH'], $objects->secret_key, $objects->enable_webhook_token_expiry_check)){
                 wp_die('Webhook authentication failed');
             }
         }

         if($post_id){

             if($objects->testmode){
                 $url_status = "https://pluto.vpay.africa/api/v1/webintegration/cp/$transactionref";
             }else{
                 $url_status = "https://saturn.vpay.africa/api/v1/webintegration/cp/$transactionref";
             }

             $status_response = wp_remote_get($url_status, array(
                 'headers' => array(
                     'Publickey' => $objects->public_key
                 )
             ));

             $status_body = wp_remote_retrieve_body($status_response);
             $status_json = json_decode($status_body, true);

             $paymentstatus = isset($status_json['data']['paymentstatus']) ? $status_json['data']['paymentstatus'] : 'pending';

             $objects->complete_transaction($order_id, $transactionref, $paymentstatus);

         }

         wp_die();
     }
add_action( 'wp_ajax_pay_load', 'pay_load' );
add_action( 'wp_ajax_nopriv_pay_load', 'pay_load' );
add_action( 'wp_ajax_vpay_callback', 'webhook_callback' );
add_action( 'wp_ajax_nopriv_vpay_callback', 'webhook_callback' );

 

