<?php
/**
 * Metabox des commandes dans le dashboard
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

<div class="wps-metabox view gridw-3">
	<h3 class="metabox-title"><?php esc_html_e( 'Last Orders', 'wpshop' ); ?></h3>

	<div class="wpeo-table table-flex table-4">
		<div class="table-row table-header">
			<div class="table-cell">#</div>
			<div class="table-cell">Tier</div>
			<div class="table-cell">Prix</div>
			<div class="table-cell">Date</div>
		</div>

		<?php
		if ( ! empty( $orders ) ) :
			foreach ( $orders as $order ) :
				?>
				<div class="table-row">
					<div class="table-cell"><a href="<?php echo esc_attr( admin_url( 'admin.php?page=wps-order&id=' . $order->data['id'] ) ); ?>"><?php echo esc_html( $order->data['title'] ); ?></a></div>
					<div class="table-cell"><a href="<?php echo esc_attr( admin_url( 'admin.php?page=wps-third-party&id=' . $order->data['third_party']->data['id'] ) ); ?>"><?php echo esc_html( $order->data['third_party']->data['title'] ); ?></a></div>
					<div class="table-cell"><?php echo esc_html( number_format( $order->data['total_ttc'], 2, ',', '' ) ); ?>€</div>
					<div class="table-cell"><?php echo esc_html( $order->data['date']['rendered']['date_time'] ); ?></div>
				</div>
				<?php
			endforeach;
		else:
			?>
			<div class="table-row">
				<div class="table-cell">
					<?php esc_html_e( 'No orders for now', 'wpshop' ); ?>
				</div>
			</div>
			<?php
		endif;
		?>
	</div>
</div>