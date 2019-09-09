<?php
/**
 * Affichage des devis dans la page "Mon compte".
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

<?php
if ( ! empty( $transient ) ) :
	foreach ( $transient as $key => $text ) :
		?>
		<div class="wpeo-notice notice-error">
			<div class="notice-content">
				<div class="notice-subtitle"><?php echo $text; ?></div>
			</div>
		</div>
		<?php
	endforeach;
endif;

?>

<form method="POST" action="<?php echo admin_url( 'admin-post.php'); ?>" class="wpeo-form">
	<input type="hidden" name="action" value="update_account_details" />
	<?php wp_nonce_field( 'update_account_details' ); ?>

	<div class="form-element form-element-required">
		<span class="form-label">Email</span>
		<label class="form-field-container">
			<input type="text" class="form-field" name="email" value="<?php echo esc_attr( $contact->data['email'] ); ?>" />
		</label>
	</div>

	<div class="form-element">
		<span class="form-label">Current password</span>
		<label class="form-field-container">
			<input type="text" class="form-field" name="current_password" />
		</label>

		<span class="form-sublabel"><?php echo esc_html_e( 'Leave blank to leave unchanged', 'wpshop' ); ?></span>
	</div>

	<div class="form-element">
		<span class="form-label">New password</span>
		<label class="form-field-container">
			<input type="text" class="form-field" name="new_password" />
		</label>
		<span class="form-sublabel"><?php echo esc_html_e( 'Leave blank to leave unchanged', 'wpshop' ); ?></span>
	</div>

	<div class="form-element">
		<span class="form-label">Confirm new password</span>
		<label class="form-field-container">
			<input type="text" class="form-field" name="confirm_new_password" />
		</label>
	</div>

	<input type="submit" class="wpeo-button" value="<?php esc_html_e( 'Save changes', 'wpshop' ); ?>" />
</form>