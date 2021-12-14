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

if( ! defined('ABSPATH') ){
    exit;
}




//$headers = ['Content-Type: application/json', 'Authorization: ' . $token];

//$query = '{ items (limit:50) {column_values { id value }} }';

//$query = "query {boards (ids: 2008724190) {owner{ id }  columns { id title type }}}";

$token = 'eyJhbGciOiJIUzI1NiJ9.eyJ0aWQiOjEzNzEyNTAyOCwidWlkIjoyNjQ5MTU5OSwiaWFkIjoiMjAyMS0xMi0xNFQxMzowOToxNC4wMDBaIiwicGVyIjoibWU6d3JpdGUiLCJhY3RpZCI6MTA2MzI5MTcsInJnbiI6InVzZTEifQ.BdZU5K4BUbucmBHIS_wTcNLyL6k7R03dDPwXtNEQnOc';
$apiUrl = 'https://api.monday.com/v2';
$query = 'mutation ($myItemName: String!, $columnVals: JSON!) { create_item (board_id:2008724190, group_id:"topics", item_name:$myItemName, column_values:$columnVals) { id } }';

$vars = ['myItemName' => 'Hello world!', 
  'columnVals' => json_encode([
    'status' => ['label' => 'Finishing'],
    'date4' => ['date' => '1993-08-27'],
    // 'location' => ['date' => '1993-08-27'],
    // 'files' => ['date' => '1993-08-27'],
    // 'files4' => ['date' => '1993-08-27'],
    'text' => 'w3432432432',
    'status_1' => ['label' => 'Standard'],
])];

$args = array(
    'method' => 'POST',
    'headers' => array(
        'Content-Type' => 'application/json',
        'Authorization' => $token
    ),
    'body' => json_encode(['query' => $query, 'variables' => $vars]),
    'content' => json_encode(['query' => $query, 'variables' => $vars])
);

// $request = wp_remote_post($apiUrl, $args);

// write_log($request['body']);

// if (is_wp_error($request)) {
//     return false; // exit
// }
