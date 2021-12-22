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

require_once __DIR__ . "/functions.php";
require_once __DIR__ . "/update-order-status.php";

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

        $this->token = 'eyJhbGciOiJIUzI1NiJ9.eyJ0aWQiOjEzODAxNjg4MSwidWlkIjoyNjQ4ODMxOCwiaWFkIjoiMjAyMS0xMi0yMVQxMzowMzoyOC43OTJaIiwicGVyIjoibWU6d3JpdGUiLCJhY3RpZCI6MTA2MzI5MTcsInJnbiI6InVzZTEifQ.RLf3jrN0xg-ttIFas_XbbnE6t5BiUkE1p4QaXGeLU5Y';
        $this->apiUrl = 'https://api.monday.com/v2';

        add_action( 'woocommerce_thankyou', [$this, 'hypemill_order_to_monday']);

        //add_action( 'init', [$this, 'hgetOrdersss'] );
        new UpdateStatusTrello($this->token, $this->apiUrl);

        

    }

    public function hgetOrdersss( ) {

        $order_id = 26189;

        $order = wc_get_order( $order_id );

        foreach ( $order->get_items() as $item_id => $item ) {

            $product_id = $item->get_product_id();
            $allmeta = $item->get_meta_data();

            
           
            // Done
            $terms = get_the_terms( $product_id, 'product_cat' );
            $catName = hypemill_product_cat( $terms[0]->name );

            $item_name = $item->get_name();
            $product_name = hypemill_product_style( $item_name );

            $color = ucwords($item->get_meta( 'pa_color', true ));

            


            // write_log("==================MetaData====================");
            // write_log($allmeta); 
        }

    }

    /**
     * All methods handler method.
     */
    public function hypemill_order_to_monday( $order_id ) {

        $billingId = $this->hypeMillBillingDetails( $order_id );
        $shippingId = $this->hypeMillShippingDetails( $order_id );
        $orderItemId = $this->hypeMillOrders( $order_id, $billingId, $shippingId );
        $this->CreateHypeMillSubItem($order_id, $orderItemId);

    }

    /**
     * Insert Billing details to monday when create order
     */
    public function hypeMillBillingDetails( $order_id ) {

        $order = wc_get_order( $order_id );

        $billing_firstName = $order->get_billing_first_name();
        $billing_lastName = $order->get_billing_last_name();
        $billing_fullName = $billing_firstName . " " . $billing_lastName;
        $billing_Address = $order->get_billing_address_1() ." ". $order->get_billing_address_2() .", ".$order->get_billing_city() .", ". $order->get_billing_state() . " " . $order->get_billing_postcode();
        $billing_phone = $order->get_billing_phone();
        $billing_email = $order->get_billing_email();
        $customer_note = $order->get_customer_note();

        $billing_query = 'mutation ($billingItemName: String!, $billing_columnVals: JSON!) { create_item (board_id:2024968615, group_id:"topics", item_name:$billingItemName, column_values:$billing_columnVals) { id } }';

        $billing_vars = ['billingItemName' => $billing_fullName,
            'billing_columnVals'  => json_encode( [
                'text0'   => $billing_firstName,
                'text'    => $billing_lastName,
                'address' => $billing_Address,
                'phone'   => $billing_phone,
                'email'   => ['email' => $billing_email, 'text' => $billing_email],
                'text4'   => $customer_note,

            ] )];

        $billing_args = array(
            'method'  => 'POST',
            'headers' => array(
                'Content-Type'  => 'application/json',
                'Authorization' => $this->token,
            ),
            'body'    => json_encode( ['query' => $billing_query, 'variables' => $billing_vars] )
        );

        $billing_request = wp_remote_post( $this->apiUrl, $billing_args );
        
        $jsonId = json_decode($billing_request['body'], true);
        $itemId = $jsonId['data']['create_item']['id'];

        return $itemId;

    }

    /**
     * Insert Shipping details to monday when create order
     */
    public function hypeMillShippingDetails( $order_id ) {

        $order = wc_get_order( $order_id );

        $shipping_firstName = $order->get_shipping_first_name() ? $order->get_shipping_first_name() : '';
        $shipping_lastName = $order->get_shipping_last_name() ? $order->get_shipping_last_name() : '';
        $shipping_fullName = $shipping_firstName . " " . $shipping_lastName;
        $shipping_Address = '';
        if ( $shipping_firstName ) {
            $shipping_Address = $order->get_shipping_address_1() ." ". $order->get_shipping_address_2() .", ".$order->get_shipping_city() .", ". $order->get_shipping_state() . " " . $order->get_shipping_postcode();
        }

        $shipping_query = 'mutation ($shippingItemName: String!, $shipping_columnVals: JSON!) { create_item (board_id:2046779965, group_id:"topics", item_name:$shippingItemName, column_values:$shipping_columnVals) { id } }';

        $shipping_vars = ['shippingItemName' => $shipping_fullName,
            'shipping_columnVals'  => json_encode( [
                'text0'   => $shipping_firstName,
                'text'    => $shipping_lastName,
                'address' => $shipping_Address,
            ] )];

        $shipping_args = array(
            'method'  => 'POST',
            'headers' => array(
                'Content-Type'  => 'application/json',
                'Authorization' => $this->token,
            ),
            'body'    => json_encode( ['query' => $shipping_query, 'variables' => $shipping_vars] )
        );

        $shipping_request = wp_remote_post( $this->apiUrl, $shipping_args );
        
        $jsonId = json_decode($shipping_request['body'], true);
        $itemId = $jsonId['data']['create_item']['id'];

        return $itemId;

    }

    /**
     * Insert order details to monday when create order
     */
    public function hypeMillOrders( $order_id, int $billingId, int $shippingId ) {

        $order = wc_get_order( $order_id );

        $shippingMethod = $order->get_shipping_method();

        switch ($shippingMethod) {
            case 'Free Curbside Delivery':
                $shipping = 'Standard';
                break;
            case 'Inside Delivery':
                $shipping = 'Inside Delivery';
                break;
            case 'Local Pickup':
                $shipping = 'Local Pickup';
                break;
            default:
                # code...
                break;
        }

        $trackingNumber = get_post_meta( $order_id, '_aftership_tracking_number', true ) != '' ? get_post_meta( $order_id, '_aftership_tracking_number', true ) : '';
        $estimatedShippingDate = get_post_meta( $order_id, 'estimated_shipping_date', true ) != '' ? get_post_meta( $order_id, 'estimated_shipping_date', true ) : '';
        $billofLandingId = get_post_meta( $order_id, 'bill_of_landing_id', true ) != '' ? get_post_meta( $order_id, 'bill_of_landing_id', true ) : '';
        
        $text = $bolPdf = $shippingLabelPdf = '';

        if ( $billofLandingId ) {
            $bolPdf = "http://staging-hoodsly.kinsta.cloud/wp-content/uploads/bol/$billofLandingId.pdf";
            $shippingLabelPdf = "http://staging-hoodsly.kinsta.cloud/wp-content/uploads/bol/shipping_label_$billofLandingId.pdf";
            $text = 'View';
        }
        
        $ti = strtotime($estimatedShippingDate);
        $date = (date("Y-m-d", $ti));
        $query = 'mutation ($myItemName: String!, $columnVals: JSON!) { create_item (board_id:2008724190, group_id:"topics", item_name:$myItemName, column_values:$columnVals) { id } }';
        
        $billingIDs = [$billingId];
        $shippingIDs = [$shippingId];
        
        $vars = ['myItemName' => '#'.$order_id,
            'columnVals'          => json_encode( [
                'status'   => ['label' => 'In Production'],
                'date4'    => ['date' => $date],
                'connect_boards0'    => ['item_ids' => $billingIDs],
                'dup__of_billing_details'    => ['item_ids' => $shippingIDs],
                'link' => ['url' => $bolPdf, 'text' => $text],
                'link9' => ['url' => $shippingLabelPdf, 'text' => $text],
                'text'     => $trackingNumber,
                'status_1' => ['label' => $shipping]
            ] )];

        $args = array(
            'method'  => 'POST',
            'headers' => array(
                'Content-Type'  => 'application/json',
                'Authorization' => $this->token,
            ),
            'body'    => json_encode( ['query' => $query, 'variables' => $vars] )
        );

        $request = wp_remote_post( $this->apiUrl, $args );

        $jsonId = json_decode($request['body'], true);
        $itemId = $jsonId['data']['create_item']['id'];

        add_post_meta( $order_id, 'monday_created_id', $itemId);
 
        return $itemId;

    }

    /**
     * Create order subitem to monday when create order
     */
    public function CreateHypeMillSubItem( $order_id, int $parentItemId ) {

        $order = wc_get_order( $order_id );

        foreach ( $order->get_items() as $item_id => $item ) {

            $product_id = $item->get_product_id();

            $terms = get_the_terms( $product_id, 'product_cat' );
            $productType = hypemill_product_cat( $terms[0]->name );

            $product_name = $item->get_name();
            $product_style = hypemill_product_style( $product_name );

            $Finish = ucwords($item->get_meta( 'pa_color', true ));

            $size = hypemill_product_size( $item );


            $query = 'mutation ($parentItemId: Int!, $myItemName: String!, $columnVals: JSON!) { create_subitem (parent_item_id: $parentItemId, item_name:$myItemName, column_values:$columnVals) { id board { id } } }';
            
            $vars = ['myItemName' => $product_name,
                    'parentItemId' => $parentItemId,
                    'columnVals'   => json_encode( [
                    'status'   => ['label' => $productType],
                    'status4'   => ['label' => $product_style],
                    'status301'   => ['label' => $Finish],
                    'status3'   => ['label' => $size],
                    // 'status0'   => ['label' => 'Ventilation'],
                    // 'status1'   => ['label' => 'Recirculating Filter'],
                    // 'status304'   => ['label' => 'Recirculating Vents'],
                    // 'status8'   => ['label' => 'Trim'],
                    // 'status9'   => ['label' => 'Trim Install'],
                    // 'status30'   => ['label' => 'Depth'],
                    // 'status21'   => ['label' => 'Reduce Height'],
                    // 'status09'   => ['label' => 'Chimney Extension'],
                    // 'status36'   => ['label' => 'Solid Bottom'],
                    // 'status6'   => ['label' => 'Rushed'],
                ] )];

            $args = array(
                'method'  => 'POST',
                'headers' => array(
                    'Content-Type'  => 'application/json',
                    'Authorization' => $this->token,
                ),
                'body'    => json_encode( ['query' => $query, 'variables' => $vars] )
            );

            $request = wp_remote_post( $this->apiUrl, $args );

            write_log($vars);

            write_log("================Body===============");

            write_log($request['body']);
        }

    }


}

new hypemill_monday;

//$query = '{ items (limit:50) {column_values { id value }} }';

//$query = "query {boards (ids: 2008724190) {owner{ id }  columns { id title type }}}";
//$query = "query {boards (ids: 2008724190) {groups{ id title } columns { id title type }}}";

// $request = wp_remote_post($apiUrl, $args);

// write_log($request['body']);

// if (is_wp_error($request)) {
//     return false; // exit
// }