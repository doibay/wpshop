<?php
/**
 * Classe principale des emails
 *
 * @author    Eoxia <dev@eoxia.com>
 * @copyright (c) 2011-2019 Eoxia <dev@eoxia.com>.
 *
 * @license   AGPLv3 <https://spdx.org/licenses/AGPL-3.0-or-later.html>
 *
 * @package   WPshop\Classes
 *
 * @since     2.0.0
 */

namespace wpshop;

defined( 'ABSPATH' ) || exit;

/**
 * Emails Class.
 */
class Emails extends \eoxia\Singleton_Util {

	/**
	 * Tableau contenant les mails par défaut.
	 *
	 * @since 2.0.0
	 *
	 * @var array
	 */
	public $emails;

	/**
	 * Constructeur.
	 *
	 * @since 2.0.0
	 */
	protected function construct() {
		$this->emails['wps_email_new_order'] = array(
			'title'             => __( 'New order', 'wpshop' ),
			'filename_template' => 'admin-new-order.php',
		);

		$this->emails['wps_email_customer_processing_order'] = array(
			'title'             => __( 'Pending order', 'wpshop' ),
			'filename_template' => 'customer-processing-order.php',
		);

		$this->emails['wps_email_customer_invoice'] = array(
			'title'             => __( 'Send invoice', 'wpshop' ),
			'filename_template' => 'customer-invoice.php',
		);

		$this->emails['wps_email_customer_completed_order'] = array(
			'title'             => __( 'Completed order', 'wpshop' ),
			'filename_template' => 'customer-completed-order.php',
		);

		$this->emails['wps_email_customer_shipment_tracking'] = array(
			'title'             => __( 'Delivered order', 'wpshop' ),
			'filename_template' => 'customer-delivered-order.php',
		);

		$this->emails['wps_email_customer_new_account'] = array(
			'title'             => __( 'New account', 'wpshop' ),
			'filename_template' => 'customer-new-account.php',
		);
	}

	/**
	 * Récupères le chemin ABS vers le template du mail dans le thème.
	 * Si introuvable récupère le template du mail dans le plugin WPshop.
	 *
	 * @since 2.0.0
	 *
	 * @param  string $filename Le nom du template.
	 * @return string           Le chemin vers le template.
	 */
	public function get_path( $filename ) {
		$path = locate_template( array( 'wpshop/emails/view/' . $filename ) );

		if ( empty( $path ) ) {
			$path = \eoxia\Config_Util::$init['wpshop']->emails->path . '/view/' . $filename;
		}

		return $path;
	}

	/**
	 * Renvoies true si le template se trouve dans le thème. Sinon false.
	 *
	 * @since 2.0.0
	 *
	 * @param string $filename Le nom du template.
	 *
	 * @return boolean           True ou false.
	 */
	public function is_override( $filename ) {
		if ( locate_template( array( 'wpshop/emails/view/' . $filename ) ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Envoies un mail.
	 *
	 * @since 2.0.0
	 *
	 * @use wp_mail.
	 *
	 * @param  string $to   Mail du destinataire.
	 * @param  string $type Le template à utilisé.
	 * @param  array  $data Les données utilisé par le template.
	 */
	public function send_mail( $to, $type, $data = array() ) {
		$shop_options = get_option( 'wps_dolibarr', Settings::g()->default_settings );

		if ( empty( $shop_options['shop_email'] ) ) {
			return;
		}

		$to          = empty( $to ) ? $shop_options['shop_email'] : $to;
		$blog_name   = get_bloginfo();
		$mail        = Emails::g()->emails[ $type ];
		$path_file   = Emails::g()->get_path( $mail['filename_template'] );
		$attachments = null;

		if ( ! empty( $data['attachments'] ) ) {
			$attachments = $data['attachments'];
		}

		ob_start();
		include $path_file;
		$content = ob_get_clean();

		$headers     = array();
		$headers[]   = 'From: ' . $blog_name . ' <' . $shop_options['shop_email'] . '>';
		$headers[]   = 'Content-Type: text/html; charset=UTF-8';
		$mail_statut = wp_mail( $to, $mail['title'], $content, $headers, $attachments );

		// translators: Send mail to test@eoxia.com, subject "sujet mail" with result true.
		\eoxia\LOG_Util::log( sprintf( 'Send mail to %s, subject %s with result %s', $to, $mail['title'], $mail_statut ), 'wpshop2' );
	}
}

Emails::g();
