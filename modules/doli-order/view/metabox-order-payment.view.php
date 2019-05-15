<?php
/**
 * Affichage des détails de paiement la commande
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

<div class="wps-metabox wps-order-payment">
	<h3 class="metabox-title"><?php esc_html_e( 'Payments', 'wpshop' ); ?></h3>

	<table class="wpeo-table">
		<thead>
			<tr>
				<th><?php esc_html_e( 'Date', 'wpshop' ); ?></th>
				<th><?php esc_html_e( 'Type', 'wpshop' ); ?></th>
				<th class="table-end"><?php esc_html_e( 'Amount TTC', 'wpshop' ); ?></th>
			</tr>
		</thead>

		<tbody>
			<?php
			if ( ! empty( $invoice->data['payments'] ) ) :
				foreach ( $invoice->data['payments'] as $payment ) :
					?>
					<tr>
						<td><?php echo esc_html( $payment->data['date']['rendered']['date'] ); ?></td>
						<td><?php echo esc_html( $payment->data['payment_type'] ); ?></td>
						<td class="table-end"><?php echo number_format( $payment->data['amount'], 2, ',', '' ); ?>€</td>
					</tr>
					<?php
				endforeach;
			endif;
			?>
		</tbody>

		<tfoot>
			<tr>
				<td colspan="2"><?php esc_html_e( 'Already paid' ); ?></td>
				<td class="table-end"><?php echo ( ! empty( $invoice ) ) ? number_format( $invoice->data['totalpaye'], 2, ',', '' ) : '00,00'; ?>€</td>
			</tr>
			<tr>
				<td colspan="2"><?php esc_html_e( 'Billed' ); ?></td>
				<td class="table-end"><?php echo ( ! empty( $invoice ) ) ? number_format( $invoice->data['total_ttc'], 2, ',', '' ) : number_format( $order->data['total_ttc'], 2, ',', '' ); ?>€</td>
			</tr>
			<tr>
				<td colspan="2"><?php esc_html_e( 'Remaining unpaid' ); ?></td>
				<td class="table-end"><strong><?php echo ( ! empty( $invoice ) ) ? number_format( $invoice->data['resteapayer'], 2, ',', '' ) : number_format( $order->data['total_ttc'], 2, ',', '' ); ?>€</strong></td>
			</tr>
		</tfoot>
	</table>
</div>
