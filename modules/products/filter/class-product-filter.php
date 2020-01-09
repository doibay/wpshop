<?php
/**
 * Gestion des filtres des produits.
 *
 * @author    Eoxia <dev@eoxia.com>
 * @copyright (c) 2011-2019 Eoxia <dev@eoxia.com>.
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
 * Product Filter Class.
 */
class Product_Filter {

	/**
	 * Constructeur.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {
		add_filter( 'eo_model_wps-product_register_post_type_args', array( $this, 'callback_register_post_type_args' ) );
		add_filter( 'eo_model_wps-product_wps-product-cat', array( $this, 'callback_taxonomy' ) );
		add_filter( 'the_content', array( $this, 'display_content_grid_product' ) );
		add_filter( 'the_content', array( $this, 'display_single_page_product' ) );

		add_filter( 'wps_product_add_to_cart_attr', array( $this, 'button_add_to_cart_tooltip' ), 10, 2 );
		add_filter( 'wps_product_add_to_cart_class', array( $this, 'disable_button_add_to_cart' ), 10, 2 );
		add_filter( 'wps_product_single', array( $this, 'display_stock' ), 10, 2 );
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
			'not_found_in_trash' => __( 'No products found in Trash.', 'wpshop' ),
		);

		$args['labels']            = $labels;
		$args['supports']          = array( 'title', 'editor', 'thumbnail' );
		$args['public']            = true;
		$args['has_archive']       = false;
		$args['show_ui']           = true;
		$args['show_in_nav_menus'] = false;
		$args['show_in_menu']      = false;
		$args['show_in_admin_bar'] = true;
		$args['rewrite']           = array(
			'slug' => __( 'product', 'wpshop' ),
		);

		$args['register_meta_box_cb'] = array( Product::g(), 'callback_register_meta_box' );

		flush_rewrite_rules();
		return $args;
	}

	/**
	 * Entregistres la taxonomy catégorie de produit.
	 *
	 * @since 2.0.0
	 *
	 * @param  array $args Les données à filtrer.
	 * @return array       Les données filtrées.
	 */
	public function callback_taxonomy( $args ) {
		$labels = array(
			'name'              => _x( 'Products category', 'taxonomy general name', 'wpshop' ),
			'singular_name'     => _x( 'Product category', 'taxonomy singular name', 'wpshop' ),
			'search_items'      => __( 'Search Products category', 'wpshop' ),
			'all_items'         => __( 'All Products category', 'wpshop' ),
			'parent_item'       => __( 'Parent Product category', 'wpshop' ),
			'parent_item_colon' => __( 'Parent Product: category', 'wpshop' ),
			'edit_item'         => __( 'Edit Product category', 'wpshop' ),
			'update_item'       => __( 'Update Product category', 'wpshop' ),
			'add_new_item'      => __( 'Add New Product category', 'wpshop' ),
			'new_item_name'     => __( 'New Product  categoryName', 'wpshop' ),
			'menu_name'         => __( 'Product category', 'wpshop' ),
		);

		$args = array(
			'hierarchical'      => true,
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_nav_menus' => true,
			'query_var'         => true,
			'rewrite'           => array(
				'slug' => __( 'category-product', 'wpshop' ),
			),
		);

		return $args;
	}

	/**
	 * Affihe la grille des produits sur les pages concernées
	 *
	 * @since 2.0.0
	 *
	 * @param  string $content Contenu de la page.
	 * @return string
	 */
	public function display_content_grid_product( $content ) {
		if ( ! is_admin() ) {

			global $post;
			global $wp_query;

			$shipping_cost_option = get_option( 'wps_shipping_cost', Settings::g()->shipping_cost_default_settings );
			$page_ids_options     = get_option( 'wps_page_ids', Pages::g()->default_options );

			if ( is_object( $post ) && $post->ID === $page_ids_options['shop_id'] ) {
				$args = array(
					'post_type'   => 'wps-product',
					'paged'       => get_query_var('paged') ? get_query_var('paged') : 1,
					'post_parent' => 0,
				);

				if ( ! empty( $shipping_cost_option['shipping_product_id'] ) ) {
					$args['post__not_in'] = array( $shipping_cost_option['shipping_product_id'] );
				}

				$wps_query = new \WP_Query( $args );

				foreach ( $wps_query->posts as $key => &$product ) {
					$product->price_ttc    = get_post_meta( $product->ID, '_price_ttc', true );
					$product->manage_stock = get_post_meta( $product->ID, '_manage_stock', true );
					$product->stock        = get_post_meta( $product->ID, '_stock', true );
				}

				ob_start();
				include( Template_Util::get_template_part( 'products', 'wps-product-grid-container' ) );
				$view = ob_get_clean();

				$content .= $view;
			}
		}
		return $content;
	}

	/**
	 * Affihe la page single des produits
	 *
	 * @since 2.0.0
	 *
	 * @param  string $content Contenu de la page.
	 * @return string
	 */
	public function display_single_page_product( $content ) {
		global $post;
		global $wp_query;

		if ( is_singular( Product::g()->get_type() ) ) {
			$product = Product::g()->get( array( 'id' => get_the_ID() ), true );

			ob_start();
			include( Template_Util::get_template_part( 'products', 'wps-product-single' ) );
			$view = ob_get_clean();

			$content = $view;
		}

		return $content;
	}

	public function change_product_category_title( $title ) {
		return 'yo';
	}

	/**
	 * Ajoutes le message "Rupture de stock" sur le produit.
	 *
	 * @since 2.0.0
	 *
	 * @param string        $attr    Attribut du bouton.
	 * @param Product_Model $product Les données du produit.
	 */
	public function button_add_to_cart_tooltip( $attr, $product ) {
		if ( $product->data['manage_stock'] && 0 >= $product->data['stock'] ) {
			$attr .= 'aria-label="' . __( 'Sold out', 'wpshop' ) . '"';
		}

		return $attr;
	}

	/**
	 * Rend le bouton "Ajouter au panier" grisé.
	 *
	 * @since 2.0.0
	 *
	 * @param string        $class   Les classes du bouton.
	 * @param Product_Model $product Les données du produit.
	 */
	public function disable_button_add_to_cart( $class, $product ) {
		if ( $product->data['manage_stock'] && 0 >= $product->data['stock'] ) {
			$class = 'button-disable wpeo-tooltip-event button-event';
		}

		return $class;
	}

	/**
	 * Affichage du stock.
	 *
	 * @since 2.0.0
	 *
	 * @param  string        $content Le contenu.
	 * @param  Product_Model $product Les données du produit.
	 *
	 * @return string                 Le contenu modifié.
	 */
	public function display_stock( $content, $product ) {
		if ( $product->data['manage_stock'] ) {
			ob_start();
			include( Template_Util::get_template_part( 'products', 'wps-product-stock' ) );
			$content .= ob_get_clean();
		}

		return $content;
	}
}

new Product_Filter();
