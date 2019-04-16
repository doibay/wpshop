<?php
/**
 * La vue principale de la page des produits (wps-product)
 *
 * @author    Eoxia <dev@eoxia.com>
 * @copyright (c) 2011-2018 Eoxia <dev@eoxia.com>.
 *
 * @license   AGPLv3 <https://spdx.org/licenses/AGPL-3.0-or-later.html>
 *
 * @package   WPshop\Templates
 *
 * @since     2.0.0
 */

namespace wpshop;

defined( 'ABSPATH' ) || exit; ?>

<div class="wpeo-wrap">
	<div class="wpeo-form">
		<?php wp_nonce_field( basename( __FILE__ ), 'wpshop_data_fields' ); ?>

		<div class="wpeo-gridlayout grid-3">

			<div class="form-element">
				<span class="form-label"><?php esc_html_e( 'Price HT(€)', 'wpshop' ); ?></span>
				<label class="form-field-container">
					<input type="text" class="form-field" name="product_data[price]" value="<?php echo esc_attr( $product->data['price'] ); ?>" />
				</label>
			</div>

			<div class="form-element">
				<span class="form-label"><?php esc_html_e( 'VAT Rate', 'wpshop' ); ?></span>
				<label class="form-field-container">
					<input type="text" class="form-field" name="product_data[tva_tx]" value="<?php echo esc_attr( $product->data['tva_tx'] ); ?>" />
				</label>
			</div>

			<div class="form-element">
				<span class="form-label"><?php esc_html_e( 'Price TTC(€)', 'wpshop' ); ?></span>
				<label class="form-field-container">
					<span><?php echo esc_attr( $product->data['price_ttc'] ); ?>€</span>
				</label>
			</div>

		</div>
	</div>
</div>
