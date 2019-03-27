<?php
/**
 * Affichage des données commercials d'un tier: devis, commande et facture.
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

<ul class="list-commercial">
	<?php if ( ! empty( $propal->data ) ) : ?>
		<li class="commercial type-propal">
			<i class="fas fa-file-signature"></i>
			<span class="commercial-date"><?php echo $propal->data['datec']['rendered']['date']; ?></span>
			<span class="commercial-title"><a href="<?php echo esc_attr( $doli_url . '/comm/propal/card.php?id=' . $propal->data['external_id']; ?>"><?php echo $propal->data['title']; ?></a></span> <?php // @TODO: lien Propal. ?>
			<span class="commercial-price"><?php echo $propal->data['total_ttc']; ?>€TTC</span>
			<span class="commercial-status"><?php echo 'STATUS'; ?></span>
		</li>
	<?php endif; ?>
	<?php if ( ! empty( $order->data ) ) : ?>
		<li class="commercial type-order">
			<i class="fas fa-shopping-cart"></i>
			<span class="commercial-date"><?php echo $order->data['datec']['rendered']['date']; ?></span>
			<span class="commercial-title"><a href="<?php echo esc_attr( admin_url( 'admin.php?page=wps-order&id=' . $order->data['id'] ) ); ?>"><?php echo $order->data['title']; ?></a></span>
			<span class="commercial-price"><?php echo $order->data['total_ttc']; ?>€TTC</span>
			<span class="commercial-status"><?php echo 'STATUS'; ?></span>
		</li>
	<?php endif; ?>
	<?php if ( ! empty( $invoice->data ) ) : ?>
		<li class="commercial type-invoice">
			<i class="fas fa-file-invoice-dollar"></i>
			<span class="commercial-date"><?php echo $invoice->data['date']['rendered']['date']; ?></span>
			<span class="commercial-title"><a href="<?php echo esc_attr( $doli_url . '/compta/facture/card.php?id=' . $invoice->data['external_id']; ?>"><?php echo $invoice->data['title']; ?></a></span> <?php // @TODO: lien Invoice. ?>
			<span class="commercial-price"><?php echo $invoice->data['total_ttc']; ?>€TTC</span>
			<span class="commercial-status"><?php echo 'STATUS'; ?></span>
		</li>
	<?php endif; ?>
</ul>
