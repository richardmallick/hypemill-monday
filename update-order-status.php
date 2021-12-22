<?php

/**
 * Change Shipping Status
 */

//hypeMillUpdateStatus();

function hypeMillUpdateStatus( ) {

    $token = 'eyJhbGciOiJIUzI1NiJ9.eyJ0aWQiOjEzODAxNjg4MSwidWlkIjoyNjQ4ODMxOCwiaWFkIjoiMjAyMS0xMi0yMVQxMzowMzoyOC43OTJaIiwicGVyIjoibWU6d3JpdGUiLCJhY3RpZCI6MTA2MzI5MTcsInJnbiI6InVzZTEifQ.RLf3jrN0xg-ttIFas_XbbnE6t5BiUkE1p4QaXGeLU5Y';
    $apiUrl = 'https://api.monday.com/v2';

    $order_id = 26181;

    $itemId = get_post_meta( $order_id, 'monday_created_id', true );


    $query = 'mutation ($myItemId:Int!, $columnVals: JSON!) { change_multiple_column_values ( item_id:$myItemId board_id:2008724190, column_values:$columnVals) { id } }';
    
    $vars = [
            'myItemId' => $itemId,
            'columnVals' => json_encode( 
                [
                    'status' => ['label' => 'Assembly'],
                ] 
            )
        ];

    $args = array(
        'method'  => 'POST',
        'headers' => array(
            'Content-Type'  => 'application/json',
            'Authorization' => $token,
        ),
        'body'    => json_encode( ['query' => $query, 'variables' => $vars] )
    );

    $request = wp_remote_post( $apiUrl, $args );

    write_log( $request['body'] );


}