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
    $_product_name = explode('-', $_product_name[0]);

    return $_product_name[0];
}