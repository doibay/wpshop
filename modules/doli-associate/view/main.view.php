<?php
/**
 * Le contenu de la modal d'association.
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

<p>
	<?php
	printf( __( 'This %s is not associated to a %s in Dolibarr', 'wpshop' ), esc_html( $label ), esc_html( $label ) );
	?>
</p>

<p>
	<?php
	printf( __( 'Please select a %s to associate in the next elements:', 'wpshop' ), esc_html( $label ) );
	?>
</p>

<p>
	<?php
	printf( __( '%s avaibles in your ERP', 'wpshop' ), ucfirst( esc_html( $label ) ) );
	?>
</p>

<input type="hidden" name="entry_id" />
<input type="hidden" name="wp_id" value="<?php echo $wp_id; ?>" />
<input type="hidden" name="route" value="<?php echo $route; ?>" />
<input type="hidden" name="type" value="<?php echo $type; ?>" />

<input type="text" class="filter-entry" placeholder="<?php esc_attr_e( 'Search...', 'wpshop' ); ?>" />

<ul class="select">
	<?php
	if ( ! empty( $entries ) ) :
		foreach ( $entries as $entry ) :
			?>
			<li data-id="<?php echo esc_attr( $entry->id ); ?>">
				<?php echo apply_filters( 'wps_associate_entry', '#' . $entry->id . ' ' . ( ! empty( $entry->name ) ? $entry->name : $entry->label ), $entry ); ?>
			</li>
			<?php
		endforeach;
	endif;
	?>
</ul>
