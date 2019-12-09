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

defined( 'ABSPATH' ) || exit; ?>

<div class="wps-list-order wps-list-box">
	<?php
	if ( ! empty( $orders ) ) :
		foreach ( $orders as $order ) :
			?>
			<div class="wps-order wps-box">
				<div class="wps-box-resume">
					<div class="wps-box-primary">
						<div class="wps-box-title"><?php echo esc_html( $order->data['datec']['rendered']['date'] ); ?></div>
						<ul class="wps-box-attributes">
							<li class="wps-box-subtitle-item"><i class="wps-box-subtitle-icon fas fa-shopping-cart"></i> <?php echo esc_attr( $order->data['title'] ); ?></li>
						</ul>
						<div class="wps-box-display-more">
							<i class="wps-box-display-more-icon fas fa-angle-right"></i>
							<span class="wps-box-display-more-text"><?php esc_html_e( 'View details', 'wpshop' ); ?></span>
						</div>
					</div>
					<div class="wps-box-secondary">
						<div class="wps-box-status"><span class="wps-box-status-dot"></span> <?php echo Doli_Statut::g()->display_status( $order ); ?></div>
						<div class="wps-box-price"><?php echo esc_html( number_format( $order->data['total_ttc'], 2, ',', '' ) ); ?>€</div>
					</div>
					<div class="wps-box-action">
						<a target="_blank"
							href="<?php echo esc_attr( admin_url( 'admin-post.php?action=wps_download_order&_wpnonce=' . wp_create_nonce( 'download_order' ) . '&order_id=' . $order->data['id'] ) ); ?>"
							class="wpeo-button button-primary button-square-50 button-rounded">
							<i class="button-icon fas fa-file-download"></i>
						</a>

						<div class="wps-more-options wpeo-dropdown dropdown-right">
							<div class="dropdown-toggle wpeo-button button-transparent button-square-40 button-size-large"><i class="button-icon fas fa-ellipsis-v"></i></div>
							<ul class="dropdown-content">
								<?php
								if ( ! $order->data['billed'] ) :
									?>
									<li>
										<a href="<?php echo esc_attr( admin_url( 'admin-post.php?action=wps_pay_order&order_id=' . $order->data['id'] ) . '&_wpnonce=' . wp_create_nonce( 'do_pay' ) ); ?>"
											class="dropdown-item">
											<i class="fas fa-money-bill"></i> <?php esc_html_e( 'Pay order', 'wpshop' );
											?>
										</a>
									</li>
									<?php
								endif;
								?>
								<li class="action-attribute dropdown-item"
									data-action="reorder"
									data-nonce="<?php echo esc_attr( wp_create_nonce( 'do_reorder' ) ); ?>"
									data-id="<?php echo esc_attr( $order->data['id'] ); ?>">
									<i class="fas fa-shopping-cart"></i> <?php esc_html_e( 'Reorder', 'wpshop' ); ?></li>
							</ul>
						</div>
					</div>
				</div>

				<div class="wps-box-detail wps-list-product">
					<?php
					if ( ! empty( $order->data['lines'] ) ) :
						foreach ( $order->data['lines'] as $line ) :
							$qty                  = $line['qty'];
							$product              = Product::g()->get( array(
								'meta_key'   => '_external_id',
								'meta_value' => (int) $line['fk_product'],
							), true );

							if ( empty( $product ) ) {
								$product = Product::g()->get( array( 'schema' => true ), true );
								$product->data['title'] = ! empty( $line['libelle'] ) ? $line['libelle'] : $line['desc'];
							}

							$product->data['qty'] = $qty;
							$product = $product->data;
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
				<div class="notice-title"><?php esc_html_e( 'No orders', 'wpshop' ); ?></div>
			</div>
		</div>
		<?php
	endif;
	?>
</div>
