<?php
/**
 * Plugin Name: HypeMill Monday
 * Plugin URI:  https://keendevs.com
 * Description: This is a order automation plugin for woocommerce.
 * Version:     1.0.0
 * Author:      KeenDevs
 * Author URI:  https://keendevs.com
 * Text Domain: hypemill-monday
 * Domain Path: /languages/
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

final class hypemill_monday {

    /**
     * Monday API Token
     */
    public $token;

    /**
     * Monday API URL
     */
    public $apiUrl;

    /**
     * Plugins Constructor
     */
    public function __construct() {

        $this->token = 'eyJhbGciOiJIUzI1NiJ9.eyJ0aWQiOjEzNzEyNTAyOCwidWlkIjoyNjQ5MTU5OSwiaWFkIjoiMjAyMS0xMi0xNFQxMzowOToxNC4wMDBaIiwicGVyIjoibWU6d3JpdGUiLCJhY3RpZCI6MTA2MzI5MTcsInJnbiI6InVzZTEifQ.BdZU5K4BUbucmBHIS_wTcNLyL6k7R03dDPwXtNEQnOc';
        $this->apiUrl = 'https://api.monday.com/v2';

        // add_action( 'woocommerce_thankyou', [$this, 'hypeMillCustomers'], 0, 1 );
        // add_action( 'woocommerce_thankyou', [$this, 'hypeMillOrders'], 0, 1 );
        //$this->wc_thank_you_order_data();
    }

    /**
     * Test Function
     */
    public function wc_thank_you_order_data( $order_id = '' ) {

        //topics = Billing Group ID
        // text0 = First Name
        // text = Last Name
        // address = Address
        // phone = Phone
        // email = Email
        // text4 = Notes

        //duplicate_of_billing_details = Shipping Group ID

        //new_group = Hoodsly Wholesalers Group ID

        $billing_firstName = "Azizul";
        $billing_lastName = "Tex";
        $billing_fullName = $billing_firstName . " " . $billing_lastName;
        $billing_Address = "Azizul Tex Bhobor Para, Mujibnagar, Meherpur Mujibnagar NC 71023 US";
        $billing_phone = "18547220499";
        $billing_email = "richardsetu1@gmail.com";
        $customer_note = "This is from our plugin";

        $billing_query = 'mutation ($billingItemName: String!, $billing_columnVals: JSON!) { create_item (board_id:2024968615, group_id:"topics", item_name:$billingItemName, column_values:$billing_columnVals) { id } }';

        $billing_vars = ['billingItemName' => $billing_fullName,
            'billing_columnVals'                  => json_encode( [
                'text0'   => $billing_firstName,
                'text'    => $billing_lastName,
                'address' => $billing_Address,
                'phone'   => $billing_phone,
                'email'   => ['label' => $billing_email],
                'text4'   => $customer_note,
            ] )];

        $billing_args = array(
            'method'  => 'POST',
            'headers' => array(
                'Content-Type'  => 'application/json',
                'Authorization' => $this->token,
            ),
            'body'    => json_encode( ['query' => $billing_query, 'variables' => $billing_vars] ),
            'content' => json_encode( ['query' => $billing_query, 'variables' => $billing_vars] ),
        );

        $billing_request = wp_remote_post( $this->apiUrl, $billing_args );

        write_log( $billing_request['body'] );
    }

    /**
     * Insert Customer details to monday when create order
     */
    public function hypeMillCustomers( $order_id ) {

    }

    /**
     * Insert order details to monday when create order
     */
    public function hypeMillOrders( $order_id ) {

        $trackingNumber = get_post_meta( $order_id, '_aftership_tracking_number', true ) != '' ? get_post_meta( $order_id, '_aftership_tracking_number', true ) : '';
        $billingAddress = get_post_meta( $order_id, '_billing_address_index', true ) != '' ? get_post_meta( $order_id, '_billing_address_index', true ) : '';
        $shippingAddress = get_post_meta( $order_id, '_shipping_address_index', true ) != '' ? get_post_meta( $order_id, '_shipping_address_index', true ) : '';

        $query = 'mutation ($myItemName: String!, $columnVals: JSON!) { create_item (board_id:2008724190, group_id:"topics", item_name:$myItemName, column_values:$columnVals) { id } }';

        $vars = ['myItemName' => 'Hello world!',
            'columnVals'          => json_encode( [
                'status'   => ['label' => 'Finishing'],
                'date4'    => ['date' => '1993-08-27'],
                // 'location' => ['date' => '1993-08-27'],
                // 'files' => ['date' => '1993-08-27'],
                // 'files4' => ['date' => '1993-08-27'],
                'text'     => 'w3432432432',
                'status_1' => ['label' => 'Standard'],
            ] )];

        $args = array(
            'method'  => 'POST',
            'headers' => array(
                'Content-Type'  => 'application/json',
                'Authorization' => $this->token,
            ),
            'body'    => json_encode( ['query' => $query, 'variables' => $vars] ),
            'content' => json_encode( ['query' => $query, 'variables' => $vars] ),
        );

        $request = wp_remote_post( $this->apiUrl, $args );

        write_log( $query );
        //write_log( $request['body'] );
    }

}

new hypemill_monday;

//$query = '{ items (limit:50) {column_values { id value }} }';

//$query = "query {boards (ids: 2008724190) {owner{ id }  columns { id title type }}}";

// $request = wp_remote_post($apiUrl, $args);

// write_log($request['body']);

// if (is_wp_error($request)) {
//     return false; // exit
// }
