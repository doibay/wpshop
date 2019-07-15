<?php
/**
 * Affichage des commandes dans la page "Mon compte"
 *
 * @author    Eoxia <dev@eoxia.com>
 * @copyright (c) 2011-2019 Eoxia <dev@eoxia.com>.
 *
 * @license   AGPLv3 <https://spdx.org/licenses/AGPL-3.0-or-later.html>
 *
 * @package   WPshop\Templates
 *
 * @since     2.0.0
 */

namespace wpshop;

defined( 'ABSPATH' ) || exit;

?>
<div class="wps-list-invoice wps-list-box">
	<?php
	if ( ! empty( $invoices ) ) :
		foreach ( $invoices as $invoice ) :
			?>
			<div class="wps-order wps-box">
				<div class="wps-box-resume">
					<div class="wps-box-primary">
						<div class="wps-box-title"><?php echo esc_html( $invoice->data['date']['rendered']['date'] ); ?></div>
						<ul class="wps-box-attributes">
							<li class="wps-box-subtitle-item"><i class="wps-box-subtitle-icon fas fa-shopping-cart"></i> <?php echo esc_attr( $invoice->data['title'] ); ?></li>
						</ul>
						<div class="wps-box-display-more">
							<i class="wps-box-display-more-icon fas fa-angle-right"></i>
							<span class="wps-box-display-more-text"><?php esc_html_e( 'View details', 'wpshop' ); ?></span>
						</div>
					</div>
					<div class="wps-box-secondary">
						<div class="wps-box-status"><span class="wps-box-status-dot"></span> <?php echo Payment::g()->make_readable_statut( $invoice ); ?></div>
						<div class="wps-box-price"><?php echo esc_html( number_format( $invoice->data['total_ttc'], 2, ',', '' ) ); ?>€</div>
					</div>
					<div class="wps-box-action">
						<a target="_blank" href="<?php echo esc_attr( admin_url( 'admin-post.php?action=wps_download_invoice&_wpnonce=' . wp_create_nonce( 'download_invoice' ) . '&avoir=' . $invoice->data['avoir'] . '&order_id=' . $invoice->data['parent_id'] ) ); ?>" class="wpeo-button button-primary button-square-50 button-rounded">
							<i class="button-icon fas fa-file-download"></i>
						</a>
					</div>
				</div>

				<div class="wps-box-detail wps-list-product">
					<?php
					if ( ! empty( $invoice->data['lines'] ) ) :
						foreach ( $invoice->data['lines'] as $line ) :
							$qty                  = $line['qty'];
							$product              = Product::g()->get( array(
								'meta_key'   => '_external_id',
								'meta_value' => (int) $line['fk_product'],
							), true );
							$product->data['qty'] = $qty;
							$product              = $product->data;
							$product['price_ttc'] = ( $line['total_ttc'] / $qty );
							include( Template_Util::get_template_part( 'products', 'wps-product-list' ) );
						endforeach;
					else :
						esc_html_e( 'No products to display', 'wpshop' );
					endif;
					?>
				</div>
			</div>
			<?php
		endforeach;
	else :
		?>
		<div class="wpeo-notice notice-info">
			<div class="notice-content">
				<div class="notice-title"><?php esc_html_e( 'No invoice', 'wpshop' ); ?></div>
			</div>
		</div>
		<?php
	endif;
	?>
</div>
