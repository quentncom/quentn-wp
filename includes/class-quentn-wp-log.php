<?php
/**
 *
 * This class manage logs of user for Quentn related activities.
 *
 * @since      1.2.8
 * @package    Quentn_Wp
 * @subpackage Quentn_Wp/includes
 * @author     Quentn Team < info@quentn.com>
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Quentn_Wp_Log {

	const LOG_ENABLED = 1;
	const LOG_DISABLED = 2;
	const LOG_EXPIRY_TIME_UPDATED = 3;
	const EMAIL_ACCESS_GRANTED_BY_API = 4;
	const EMAIL_ACCESS_GRANTED_MANUALLY  = 5;
	const EMAIL_ACCESS_REVOKED_BY_API = 6;
	const EMAIL_ACCESS_REVOKED_MANUALLY = 7;
	const USER_CREATED_BY_API = 8;
	const USER_UPDATED_BY_API = 9;
	const ROLE_ADDED_BY_API = 10;
	const ROLE_REMOVED_BY_API = 11;
	const USER_VISITS_RESTRICTED_PAGE = 12;
	const USER_DENIED_RESTRICTED_PAGE_ACCESS = 13;
	const USER_LOGGED_IN_BY_AUTOLOGIN_LINK = 14;
	const USER_TRIED_REUSE_AUTOLOGIN_LINK = 15;
	const USER_REDIRECTED_TO_RESET_PASSWORD_PAGE = 16;

	/**
	 * Constructor method.
	 */
	public function __construct() {

		add_action( 'add_option_quentn_add_log', array( $this, 'quentn_add_log_option_added' ), 10, 2 );
		add_action( 'update_option_quentn_add_log', array( $this, 'quentn_add_log_option_updated' ), 10, 2 );

		if ( get_option( 'quentn_add_log', true ) ) {
			add_action( 'update_option_quentn_log_expire_days', array( $this, 'quentn_log_expire_days_option_updated' ), 10, 3 );

			add_action( 'quentn_access_granted', array( $this, 'quentn_access_granted' ), 10, 3 );
			add_action( 'quentn_access_revoked', array( $this, 'quentn_access_revoked' ), 10, 3 );

			add_action( 'quentn_user_created', array( $this, 'quentn_user_created' ), 10, 2 );
			add_action( 'quentn_user_updated', array( $this, 'quentn_user_updated' ), 10, 2 );

			add_action( 'quentn_user_role_added', array( $this, 'quentn_user_role_added' ), 10, 3 );
			add_action( 'quentn_user_role_removed', array( $this, 'quentn_user_role_removed' ), 10, 3 );


			add_action( 'quentn_user_visit_restricted_page', array( $this, 'quentn_user_visit_restricted_page' ), 10, 2 );
			add_action( 'quentn_user_access_denied', array( $this, 'quentn_user_access_denied' ), 10, 2 );

			add_action( 'quentn_user_autologin', array( $this, 'quentn_user_autologin' ) );
			add_action( 'quentn_user_reset_password', array( $this, 'quentn_user_reset_password' ) );

			add_action( 'quentn_user_autologin_failed', array( $this, 'quentn_user_autologin_failed' ), 10, 2 );
		}
	}


	/**
	 * Add log when user first time update is log option
	 */
	public function quentn_add_log_option_added( $option, $value ) {
		//if log is disabled by user which by default is enabled
		if ( ! $value ) {
			$this->add_quentn_log( self::LOG_DISABLED );
		}
	}

	/**
	 * Add log when user update is log option
	 */
	public function quentn_add_log_option_updated( $old_value, $value ) {
		if ( $old_value !== $value ) {
			$event_id = $value ? self::LOG_ENABLED : self::LOG_DISABLED;
			$this->add_quentn_log( $event_id );
		}
	}

	/**
	 * Add log when user created by quentn API
	 */
	public function quentn_user_created( $email, $user_id ) {
		$this->add_quentn_log( self::USER_CREATED_BY_API, [ 'email' => $email, 'context' => "User ID: " . $user_id ] );
	}

	/**
	 * Add log when user update by quentn API
	 */
	public function quentn_user_updated( $email, $user_id ) {
		$this->add_quentn_log( self::USER_UPDATED_BY_API, [ 'email' => $email, 'context' => "User ID: " . $user_id ] );
	}

	/**
	 * Add log when user role added by quentn API
	 */
	public function quentn_user_role_added( $email, $user_id, $role ) {
		$this->add_quentn_log( self::ROLE_ADDED_BY_API, [ 'email' => $email, 'context' => "User ID: " . $user_id . " Role: " . $role ] );
	}

	/**
	 * Add log when user role removed by quentn API
	 */
	public function quentn_user_role_removed( $email, $user_id, $role ) {
		$this->add_quentn_log( self::ROLE_REMOVED_BY_API, [ 'email' => $email, 'context' => "User ID: " . $user_id . " Role: " . $role ] );
	}

	/**
	 * Add log when user automatically logged using quentn generated url
	 */
	public function quentn_user_autologin( $email ) {
		$this->add_quentn_log( self::USER_LOGGED_IN_BY_AUTOLOGIN_LINK, [ 'email' => $email ] );
	}
	/**
	 * Add log when user try to auto login but redirected to reset password page
	 */
	public function quentn_user_reset_password( $email ) {
		$this->add_quentn_log( self::USER_REDIRECTED_TO_RESET_PASSWORD_PAGE, [ 'email' => $email ] );
	}

	/**
	 * Add log when user try to auto login but failed
	 */
	public function quentn_user_autologin_failed( $email, $reason ) {

		$context = '';
		if ( $reason == QUENTN_WP_LOGIN_URL_ALREADY_USED ) {
			$context = 'Login link already used';
		} else if ( $reason == QUENTN_WP_LOGIN_SECURITY_FAILURE ) {
			$context = 'Invalid key';
		} else if ( $reason == QUENTN_WP_LOGIN_URL_EXPIRED ) {
			$context = 'Login link expired';
		}

		$this->add_quentn_log( self::USER_TRIED_REUSE_AUTOLOGIN_LINK, [ 'email' => $email, 'context' => $context ] );
	}

	/**
	 * Add log when user visited a restricted page
	 */
	public function quentn_user_visit_restricted_page( $page_id, $email ) {
		$this->add_quentn_log( self::USER_VISITS_RESTRICTED_PAGE, [ 'email' => $email, 'page_id' => $page_id ]  );
	}

	/**
	 * Add log when user try to visit restricted page but access denied
	 */
	public function quentn_user_access_denied( $page_id, $emails ) {
		if ( empty( $emails ) || empty( $page_id ) ) {
			return;
		}

		//get email addresses from email hash
		global $wpdb;
		$emails = implode( "','", $emails );
		$sql = "SELECT email FROM " . $wpdb->prefix . TABLE_QUENTN_RESTRICTIONS. " where email_hash IN ('" . $emails . "')";
		$results = $wpdb->get_results( $sql, 'ARRAY_A' );
		$email_addresses = array_column($results, 'email');
		$logs = [];
		foreach ( $email_addresses as $email ) {
			$logs[] = array(
				'email'   => $email,
				'page_id' => $page_id,
			);
		}
		$this->add_quentn_logs( self::USER_DENIED_RESTRICTED_PAGE_ACCESS, $logs );

	}

	/**
	 * Add log when updated log expiry option
	 */
	public function quentn_log_expire_days_option_updated( $old_value, $value, $option ) {
		$context = $value." days";
		if ( $old_value !== $value ) {
			$this->add_quentn_log( self::LOG_EXPIRY_TIME_UPDATED, [ 'context' => $context ] );

		}
	}

	/**
	 * Add log when user access are granted to a restricted page
	 */
	public function quentn_access_granted( $emails, $pages, $added_by ) {
		if ( empty( $emails ) || empty( $pages ) ) {
			return;
		}
		$logs = [];
		foreach ( $emails as $email ) {
			foreach ( $pages as $page ) {
				$logs[] = array(
					'email'   => $email,
					'page_id' => $page,
				);
			}
		}
		$event_id = ( $added_by == QUENTN_WP_ACCESS_ADDED_BY_API ) ? self::EMAIL_ACCESS_GRANTED_BY_API : self::EMAIL_ACCESS_GRANTED_MANUALLY;
		$this->add_quentn_logs( $event_id, $logs );
	}

	/**
	 * Add log when user access are revoked from a restricted page
	 */
	public function quentn_access_revoked( $emails, $pages, $revoked_by ) {

		if ( empty( $emails ) || empty( $pages ) ) {
			return;
		}
		$logs = [];
		foreach ( $emails as $email ) {
			foreach ( $pages as $page ) {
				$logs[] = array(
					'email'   => $email,
					'page_id' => $page,
				);
			}
		}
		$event_id = ( $revoked_by == QUENTN_WP_ACCESS_REVOKED_BY_API ) ? self::EMAIL_ACCESS_REVOKED_BY_API : self::EMAIL_ACCESS_REVOKED_MANUALLY;
		$this->add_quentn_logs( $event_id, $logs );
	}

	/**
	 * Add log to database
	 */
	public function add_quentn_log( $event_id, $log = [] ) {

		$values['event']      = $event_id;
		$place_holders[]      = '%d';
		$values['created_at'] = time();
		$place_holders[]      = '%s';
		if ( isset( $log['email'] ) ) {
			$values['email'] = $log['email'];
			$place_holders[] = '%s';
		}
		if ( isset( $log['page_id'] ) ) {
			$values['page_id'] = $log['page_id'];
			$place_holders[]   = '%d';
		}
		if ( isset( $log['page_id'] ) ) {
			$values['page_id'] = $log['page_id'];
			$place_holders[]   = '%d';
		}
		if ( isset( $log['context'] ) ) {
			$values['context'] = $log['context'];
			$place_holders[]   = '%s';
		}
		global $wpdb;

		$wpdb->insert( $wpdb->prefix . TABLE_QUENTN_LOG, $values, $place_holders );

	}

	/**
	 * Add multiple logs to database at once
	 */
	public function add_quentn_logs( $event_id, $logs = [] ) {
		if ( empty( $logs ) ) {
			return;
		}
		$values        = array();
		$place_holders = array();

		foreach ( $logs as $log ) {
			$email   = ! empty( $log['email'] ) ? $log['email'] : null;
			$page_id   = ! empty( $log['page_id'] ) ? $log['page_id'] : null;
			$context   = ! empty( $log['context'] ) ? $log['context'] : null;

			array_push( $values, $event_id, $email, $page_id, time(), $context );
			$place_holders[] = "('%d', '%s', '%d', '%s', '%s')";
		}

		global $wpdb;
		//insert into database
		$query = "INSERT INTO " . $wpdb->prefix . TABLE_QUENTN_LOG . " ( event, email, page_id, created_at, context ) VALUES ";
		$query .= implode( ', ', $place_holders );
		$wpdb->query( $wpdb->prepare( $query, $values ) );
	}

	/**
	 * Return an instance of this class.
	 *
	 * @return object A single instance of this class.
	 */
	public static function get_instance() {

		static $instance = null;

		if ( is_null( $instance ) ) {
			$instance = new self;
		}

		return $instance;
	}
}

Quentn_Wp_Log::get_instance();