<?php
/**
 * Gestion des filtres des produits.
 *
 * @author    Eoxia <dev@eoxia.com>
 * @copyright (c) 2011-2018 Eoxia <dev@eoxia.com>.
 *
 * @license   AGPLv3 <https://spdx.org/licenses/AGPL-3.0-or-later.html>
 *
 * @package   WPshop\Filters
 *
 * @since     2.0.0
 */

namespace wpshop;

defined( 'ABSPATH' ) || exit;

/**
 * Filters of product module.
 */
class Product_Filter {

	/**
	 * Constructor.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {
		add_filter( 'eo_model_wps-product_register_post_type_args', array( $this, 'callback_register_post_type_args' ) );
		add_filter( 'eo_model_wps-product_wps-product-cat', array( $this, 'callback_taxonomy' ) );
		add_filter( 'single_template', array( $this, 'get_custom_post_type_template' ), 11 );
	}

	/**
	 * Permet d'ajouter l'argument public à true pour le register_post_type de EOModel.
	 *
	 * @since 2.0.0
	 *
	 * @param  array $args Les arguments pour le register_post_type.
	 * @return array       Les arguments pour le register_post_type avec public à true.
	 */
	public function callback_register_post_type_args( $args ) {
		$labels = array(
			'name'               => _x( 'Products', 'post type general name', 'wpshop' ),
			'singular_name'      => _x( 'Product', 'post type singular name', 'wpshop' ),
			'menu_name'          => _x( 'Products', 'admin menu', 'wpshop' ),
			'name_admin_bar'     => _x( 'Product', 'add new on admin bar', 'wpshop' ),
			'add_new'            => _x( 'Add New', 'product', 'wpshop' ),
			'add_new_item'       => __( 'Add New Product', 'wpshop' ),
			'new_item'           => __( 'New Product', 'wpshop' ),
			'edit_item'          => __( 'Edit Product', 'wpshop' ),
			'view_item'          => __( 'View Product', 'wpshop' ),
			'all_items'          => __( 'All Products', 'wpshop' ),
			'search_items'       => __( 'Search Products', 'wpshop' ),
			'parent_item_colon'  => __( 'Parent Products:', 'wpshop' ),
			'not_found'          => __( 'No products found.', 'wpshop' ),
			'not_found_in_trash' => __( 'No products found in Trash.', 'wpshop' )
		);

		$args['labels']               = $labels;
		$args['supports']             = array( 'title', 'editor', 'thumbnail' );
		$args['public']               = true;
		$args['has_archive']          = true;
		$args['show_ui']              = true;
		$args['show_in_nav_menus']    = false;
		$args['show_in_menu']         = false;

		$shop_page_slug = Pages_Class::g()->get_slug_shop_page();

		if ( ! empty( $shop_page_slug ) ) {
			$args['rewrite'] = array(
				'slug' => $shop_page_slug,
			);
		}

		$args['register_meta_box_cb'] = array( Product_Class::g(), 'callback_register_meta_box' );

		flush_rewrite_rules();
		return $args;
	}

	public function callback_taxonomy( $args ) {
		$labels = array(
		'name'              => _x( 'Products category', 'taxonomy general name', 'textdomain' ),
		'singular_name'     => _x( 'Product category', 'taxonomy singular name', 'textdomain' ),
		'search_items'      => __( 'Search Products category', 'textdomain' ),
		'all_items'         => __( 'All Products category', 'textdomain' ),
		'parent_item'       => __( 'Parent Product category', 'textdomain' ),
		'parent_item_colon' => __( 'Parent Product: category', 'textdomain' ),
		'edit_item'         => __( 'Edit Product category', 'textdomain' ),
		'update_item'       => __( 'Update Product category', 'textdomain' ),
		'add_new_item'      => __( 'Add New Product category', 'textdomain' ),
		'new_item_name'     => __( 'New Product  categoryName', 'textdomain' ),
		'menu_name'         => __( 'Product category', 'textdomain' ),
	);

		$args = array(
		'hierarchical'      => true,
		'labels'            => $labels,
		'show_ui'           => true,
		'show_admin_column' => true,
		'show_in_nav_menus' => true,
		'query_var'         => true,
	);

		return $args;
	}

	/**
	 * Get template for single post type
	 *
	 * @since  2.0.0
	 * @param  string $single_template Template path.
	 * @return string $single_template Template path.
	 */
	public function get_custom_post_type_template( $single_template ) {
		global $post;

		if ( Product_Class::g()->get_type() === $post->post_type ) {
			$single_template = Template_Util::get_template_part( 'products', 'single-product' );
		}

		return $single_template;
	}

}

new Product_Filter();