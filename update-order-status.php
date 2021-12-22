<?php

/**
 * Change Shipping Status
 */

//hypeMillUpdateStatus();
class UpdateStatusTrello{

    /**
     * Monday API Token
     */
    public $token;

    /**
     * Monday API URL
     */
    public $apiUrl;


    public function __construct($token, $apiUrl) {
        $this->token = $token;
        $this->apiUrl = $apiUrl;
        add_action('rest_api_init', [$this, 'register_rest_api']);
    }

    public function register_rest_api(){
        register_rest_route( 'trello-to-wc/v1', '/602405b69f2dd71331735099', array(
			'methods' => 'POST',
			'callback' => [$this, 'hypeMillUpdateStatus']
		));
		register_rest_route( 'trello-to-wc/v1', '/60448c038190b70d529e4757', array(
			'methods' => 'POST',
			'callback' => [$this, 'hypeMillUpdateStatus']
		));
        register_rest_route( 'aftership/v1', '/monday', array(
			'methods' => 'POST',
			'callback' => [$this, 'aftership_changes']
		));
    }

    public function hypeMillUpdateStatus($request) {
        write_log('response from trello');
        $response = $request->get_params();
        if(!empty($response['action']['data']['card']['name'])){
            $order_id = $response['action']['data']['card']['name'];
            $order = wc_get_order( $order_id );
            $changed_card = $response['action']['data']['listAfter']['name'];
            $card_id = $response['action']['data']['card']['id'];
        }
    
        $itemId =  (int)get_post_meta( $order_id, 'monday_created_id', true );
    
    
        $query = 'mutation ($myItemId:Int!, $columnVals: JSON!) { change_multiple_column_values ( item_id:$myItemId board_id:2008724190, column_values:$columnVals) { id } }';
        
        $vars = [
                'myItemId' => $itemId,
                'columnVals' => json_encode( 
                    [
                        'status' => ['label' => $changed_card],
                    ] 
                )
            ];
    
        $args = array(
            'method'  => 'POST',
            'headers' => array(
                'Content-Type'  => 'application/json',
                'Authorization' => $this->token,
            ),
            'body'    => json_encode( ['query' => $query, 'variables' => $vars] )
        );
    
        $request = wp_remote_post( $this->apiUrl, $args );

    }

    
    public function aftership_changes($request){
        $data = $request->get_json_params();
        
	    if(!empty($data['msg']['order_id'])){
            $order_id = $data['msg']['order_id'];
            $order = wc_get_order( $order_id );
            $tracking_number = $data['msg']['tracking_number'];
            // setting order status from rl carriers
            if(!empty($tracking_number)){
                $order_status = '';
                $changed_status = $data['msg']['tag'];
                if($changed_status == 'Delivered'){
                    $order_status = 'Delivered';
                }else if($changed_status == 'InTransit'){
                    $order_status = 'In Transit';
                }
            }
        
            $itemId = (int)get_post_meta( $order_id, 'monday_created_id', true );
        
        
            $query = 'mutation ($myItemId:Int!, $columnVals: JSON!) { change_multiple_column_values ( item_id:$myItemId board_id:2008724190, column_values:$columnVals) { id } }';
            
            $vars = [
                    'myItemId' => $itemId,
                    'columnVals' => json_encode( 
                        [
                            'status' => ['label' => $order_status],
                        ] 
                    )
                ];
        
            $args = array(
                'method'  => 'POST',
                'headers' => array(
                    'Content-Type'  => 'application/json',
                    'Authorization' => $this->token,
                ),
                'body'    => json_encode( ['query' => $query, 'variables' => $vars] )
            );
        
            $request = wp_remote_post( $this->apiUrl, $args );
        }
    }
}