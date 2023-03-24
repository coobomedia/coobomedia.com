<?php
/**
 * Simple Pay: Edit form custom fields
 *
 * @package SimplePay\Pro\Post_Types\Simple_Pay\Edit_Form
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.8.0
 */

namespace SimplePay\Pro\Post_Types\Simple_Pay\Util;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Retrieves a form's custom fields.
 *
 * Formats legacy data in to a consumable structure.
 * Legacy structure has field types grouped under a `type` index.
 *
 * array(2) {
 *  ["text"]=>
 *  array(2) {
 *    [3]=>
 *    array(8) {
 *      ["id"]=>
 *      string(0) ""
 *      ["order"]=>
 *      string(1) "1"
 *    }
 *    [4]=>
 *    array(8) {
 *      ["id"]=>
 *      string(0) ""
 *      ["order"]=>
 *      string(1) "2"
 *    }
 *  }
 *  ["payment_button"]=>
 *  array(1) {
 *    [3]=>
 *    array(6) {
 *      ["id"]=>
 *      string(0) ""
 *      ["order"]=>
 *      string(1) "3"
 *    }
 *  }
 *
 * Create a flat list sorted by each field's `order` key.
 *
 *
 * @since 3.8.0
 *
 * @param int $post_id Current Payment Form ID.
 * @return array Flattened and sorted custom fields.
 */
function get_custom_fields( $post_id ) {
	$fields        = get_post_meta( $post_id, '_custom_fields', true );
	$sorted_fields = array();
	$count         = 0;

	if ( ! $fields || ! is_array( $fields ) ) {
		return $sorted_fields;
	}

	foreach ( $fields as $type => $fields ) {
		foreach ( $fields as $field ) {
			$field['type'] = $type;

			if ( ! isset( $field['order'] ) ) {
				$field['order'] = $count;
			}

			if ( 'payment_button' === $field['type'] ) {
				$field['order'] = 9999;
			}

			$sorted_fields[] = $field;
		}

		$count++;
	}

	uasort(
		$sorted_fields,
		function( $a, $b ) {
			if ( floatval( $a['order'] ) === floatval( $b['order'] ) ) {
				return 0;
			}

			return ( floatval( $a['order'] ) < floatval( $b['order'] ) ) ? -1 : 1;
		}
	);

	return $sorted_fields;
}
