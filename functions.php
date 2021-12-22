<?php

/**
 * Match Product Category for Monday
 */
function hypemill_product_cat( $cat_name ) {

    switch ($cat_name) {

        case 'Wood Hoods':
            $cat = 'Hood';
            break;
        case 'Island Wood Hoods':
            $cat = 'Island Hood';
            break;
        case 'Vent-A-Hood':
            $cat = 'Ventilation';
            break;
        case 'Hall Trees':
            $cat = 'Hall Tree';
            break;
        case 'Floating Shelves':
            $cat = 'Floating Shelf';
            break;
        case 'In Stock Hoods':
            $cat = 'In Stock Hood';
            break;
        case 'Quick Shipping':
            $cat = 'Quick Ship';
            break;
        
        default:
            $cat = '';
            break;
    }

    return $cat;
}

/**
 * Match Product for Monday subitem style
 */
function hypemill_product_style( $product_name ) {

    $_product_name = explode(' #', $product_name);
    $_product_name = explode(' -', $_product_name[0]);

    return $_product_name[0];
}

function casttoclass($class, $object)
{
  return unserialize(preg_replace('/^O:\d+:"[^"]++"/', 'O:' . strlen($class) . ':"' . $class . '"', serialize($object)));
}

/**
 * Function for get product size
 * 
 * return string;
 * 
 * eg 36x48
 */
function hypemill_product_size( $item ) {

    $extraProductOptions = get_option('thwepo_custom_sections')['default'];
    $islandHoodOptions = get_option('thwepo_custom_sections')['island_wood_hood_sizes'];

    $size_and_ventilation = casttoclass('stdClass', $extraProductOptions);

    $size_and_ventilation_keys = [];

    foreach ( $size_and_ventilation->fields as $key => $value ) {

        if ( casttoclass('stdClass', $value)->type === 'select' ) {

            $size_and_ventilation_keys[] = $key;
            
        }
        
    }

    $island_hood = [];

    foreach ( $islandHoodOptions->fields as $key => $value ) {

        if ( $value->type === 'select' ) {

            $island_hood[] = $key;
            
        }
        
    }

    $size_keys = array_merge($size_and_ventilation_keys, $island_hood);

    foreach ( $size_keys as $size_key ) {

        $size = $item->get_meta( $size_key, true );

        if ( $size ) {
            $size = $size;
            break;
        }  
        
    }

    preg_match_all('!\d+!', $size, $matches);

    $finalSize = $matches[0][0] ."x".$matches[0][1];

    return $finalSize;
}