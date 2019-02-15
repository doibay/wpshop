<?php
/**
 * Gestion des proposals.
 *
 * @author Eoxia <dev@eoxia.com>
 * @since 2.0.0
 * @version 2.0.0
 * @copyright 2018 Eoxia
 * @package wpshop
 */

namespace wpshop;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Gestion des Proposals CRUD.
 */
class Emails_Class extends \eoxia\Singleton_Util {
	public $emails;

	protected function construct() {
		$this->emails['wps_email_new_order'] = array(
			'title'             => __( 'Nouvelle commande', 'wpshop' ),
			'filename_template' => 'admin-new-order.php',
		);

		$this->emails['wps_email_customer_processing_order'] = array(
			'title'             => __( 'Commande en cours', 'wpshop' ),
			'filename_template' => 'customer-processing-order.php',
		);

		$this->emails['wps_email_customer_completed_order'] = array(
			'title'             => __( 'Commande complété', 'wpshop' ),
			'filename_template' => 'customer-completed-order.php',
		);

		$this->emails['wps_email_customer_new_account'] = array(
			'title'             => __( 'Nouveau compte', 'wpshop' ),
			'filename_template' => 'customer-new-account.php',
		);

		$this->emails['wps_email_customer_reset_password'] = array(
			'title'             => __( 'Reset password', 'wpshop' ),
			'filename_template' => 'customer-reset-password.php',
		);
	}

	public function get_path( $filename ) {
		$path = locate_template( array( 'wpshop/emails/view/' . $filename ) );

		if ( empty( $path ) ) {
			$path = \eoxia\Config_Util::$init['wpshop']->emails->path . '/view/' . $filename;
		}

		return $path;
	}

	public function is_override( $filename ) {
		if ( locate_template( array( 'wpshop/emails/view/' . $filename ) ) ) {
			return true;
		}

		return false;
	}
}

Emails_Class::g();
