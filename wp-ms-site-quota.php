<?php
/*
Plugin Name: WP MS Disk Quota Column
Description: Adds a "Disk Quota" column on the WP Sites network dashboard page.
Author: r-a-y
Author URI: http://profiles.wordpress.org/r-a-y
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

add_action( 'plugins_loaded', array( 'Ray_MS_Site_Quota_Column', 'init' ) );

class Ray_MS_Site_Quota_Column {
	/**
	 * Internal name used to register our disk quota column.
	 *
	 * @var string
	 */
	public $column_name = 'quota';

	/**
	 * Static init method.
	 */
	public static function init() {
		return new self();
	}

	/**
	 * Constructor.
	 */
	protected function __construct() {
		if ( ! is_admin() ) {
			return;
		}

		add_action( 'admin_head-sites.php', array( $this, 'setup_hooks' ) );
	}

	/**
	 * Callback method used to setup hooks.
	 *
	 * Fired on the 'admin_head-sites.php' hook so our plugin only runs inside
	 * the WP Sites dashboard.
	 */
	public function setup_hooks() {
		// time to register some hooks!
		add_filter( 'wpmu_blogs_columns',         array( $this, 'register_column' ) );
		add_action( 'manage_sites_custom_column', array( $this, 'setup_column' ), 10, 2 );

		// might as well inject some CSS while we're here!
	?>

		<style type="text/css">
		</style>

	<?php
	}

	/**
	 * Register our custom quota column.
	 *
	 * @param  array $retval Current registered columns.
	 * @return array
	 */
	public function register_column( $retval ) {
		$retval[$this->column_name] = __( 'Disk Quota', 'wp-ms-site-quota' );
		return $retval;
	}

	/**
	 * Output our custom quota column content.
	 *
	 * @param string $column_name The registered column name that the list table is currently on.
	 * @param int $user_id The blog ID associated with the current blog row.
	 */
	public function setup_column( $column_name, $blog_id ) {
		if ( $this->column_name !== $column_name ) {
			return;
		}

		switch_to_blog( $blog_id );

		// You might recognize this from wp_dashboard_quota().
		$quota = get_space_allowed();
		$used = get_space_used();

		if ( $used > $quota ) {
			$percentused = '100';
		} else {
			$percentused = ( $used / $quota ) * 100;
		}

		$text = sprintf(
			/* translators: 1: number of megabytes, 2: percentage */
			__( '%1$s MB / %2$s MB (%3$s%%)' ),
			number_format_i18n( round( $used, 2 ), 2 ),
			number_format_i18n( $quota, 2 ),
			number_format( $percentused )
		);
		printf(
			'<a href="%1$s" title="%2$s" class="musublink">%3$s</a>',
			esc_url( admin_url( 'upload.php' ) ),
			__( 'Manage Uploads' ),
			$text
		);

		restore_current_blog();
	}

}