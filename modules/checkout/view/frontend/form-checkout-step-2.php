<?php
/**
 * Le formulaire pour créer son adresse de livraison
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

<?php do_action( 'wps_checkout_before_payment' ); ?>

<form method="post" class="wps-checkout-step-2">

	<?php do_action( 'wps_checkout_payment' ); ?>

</form>

<?php do_action( 'wps_checkout_after_payment' ); ?>
