<?php
/**
 * La vue principale de la page de réglages
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

<table class="wpeo-table">
	<thead>
		<tr>
			<th><?php esc_html_e( 'E-mail', 'wpshop' ); ?></th>
			<th></th>
		</tr>
	</thead>
	<tbody>
		<?php
		if ( ! empty( $emails ) ) :
			foreach ( $emails as $key => $email ) :
				?>
				<tr>
					<td><?php echo $email['title']; ?></td>
					<td>
						<a href="<?php echo admin_url( 'admin-post.php?action=wps_load_settings_tab&page=wps-settings&tab=emails&section=' . $key ); ?>" class="wpeo-button button-main">
							<span>Gérer</span>
						</a>
					</td>
				</tr>
				<?php
			endforeach;
		endif;
		?>
	</tbody>
</table>
