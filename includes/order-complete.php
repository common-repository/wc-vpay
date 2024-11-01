<?php
/*
When transaction completed it is check the status 
is transaction completed or rejected
*/
 function verify_vpay_payment() {

    global $woocommerce;

    if ( isset( $_REQUEST['txnref'] ) ) {
        $payment_reference = sanitize_text_field( $_REQUEST['txnref'] );
    } else {
        $payment_reference = false;
    }

    @ob_clean();
    if ($payment_reference) {
        // TODO: Should Verify Payment With Reference
        file_put_contents('test.log', json_encode($_REQUEST));

        $order_details = explode( '_', $payment_reference );
        $order_id      = (int) $order_details[0];
        $order         = wc_get_order( $order_id );

        if ( in_array( $order->get_status(), array( 'processing', 'completed', 'on-hold' ) ) ) {

            // wp_redirect( $this->get_return_url( $order ) );

            exit;

        }

        $order->payment_complete( $payment_reference );
        $order->reduce_order_stock();
        $order->add_order_note( sprintf( 'Payment via VPay successful (Transaction Reference: %s)', $payment_reference ) );

        // Empty cart
        $woocommerce->cart->empty_cart();
        wp_redirect($order->get_checkout_order_received_url( $order ) );

         if ( !$this->is_autocomplete_order_enabled( $order ) ) {
             $order->update_status( 'completed' );
         }

        // wp_redirect( $this->get_return_url( $order ) );
        // exit;
    }

    wp_redirect( wc_get_page_permalink( 'cart' ) );

    exit;
    
}