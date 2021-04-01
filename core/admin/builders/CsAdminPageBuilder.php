<?php namespace WooGateWayCoreLib\admin\builders;

/**
 * Page Builder
 *
 * @package Admin
 * @since 1.2.0
 * @author CodeSolz <customer-service@codesolz.com>
 */

if ( ! defined( 'CS_WAPG_VERSION' ) ) {
	exit;
}

class CsAdminPageBuilder {

	/**
	 * Hold admin pages
	 *
	 * @var type
	 */
	protected $admin_page = array();

	/**
	 * Hold current screen id
	 *
	 * @var type
	 */
	public $current_screen;

	/**
	 * Call Classes
	 *
	 * @param type $name
	 * @param type $arguments
	 * @return type
	 */
	public function __call( $name, $arguments ) {
		return $this->getClass( ucwords( $name ) );
	}

	/**
	 * Get classes
	 *
	 * @param string $class
	 * @return type
	 */
	private function getClass( $class ) {
		$class_path = '\WooGateWayCoreLib\\admin\\options\\pages\\' . $class;

		if ( ! class_exists( $class_path ) ) {
			return " Class / Method - '{$class_path}' - not found!";
		}

		if ( ! array_key_exists( $class_path, $this->admin_page ) ) {
			$this->admin_page[ $class_path ] = new $class_path( $this );
		}

		return $this->admin_page[ $class_path ];
	}

	/**
	 * Init current screen
	 *
	 * @return type
	 */
	private function init_current_screen() {
		$this->current_screen = get_current_screen();
		return $this->current_screen;
	}

	/**
	 * Generate page
	 *
	 * @param type $argc
	 */
	public function generate_page( $argc ) {
		// init current screen
		$this->init_current_screen();

		$content    = '';
		$top_notice = isset( $argc['well'] ) ? $argc['well'] : '';
		if ( ! empty( $top_notice ) ) {
			$content = '<div class="well">' . $top_notice . '</div>';
		}

		$content .= isset( $argc['content'] ) ? $argc['content'] : '----';

		return sprintf(
			$this->page_wrapper( $argc ),
			$this->generate_header( $argc ),
			$content,
			$this->generate_button_block( $argc ),
			$this->generate_footer()
		);
	}

	/**
	 * Get page wrapper
	 *
	 * @return string
	 */
	private function page_wrapper( $argc ) {
		$form_start = '';
		$form_end   = '';
		if ( isset( $argc['show_btn'] ) ) {
			$form_start = '<form method="post"  enctype="multipart/form-data">';
			$form_end   = '</form>';
		}

		$body_class = isset( $argc['body_class'] ) ? $argc['body_class'] : '';

		return "<div class=\"wrap\"> 
        <div id=\"product_binder\">
        <div class=\"panel\"> %s 
        {$form_start}
        <div class=\"panel-body bg-white {$body_class}\">
        <div class=\"container\"> %s 
        </div></div> %s %s
        {$form_end}
        </div></div></div>";
	}

	/**
	 * Generate page header block
	 *
	 * @param type $argc
	 * @return type
	 */
	private function generate_header( $argc ) {
		$title     = isset( $argc['title'] ) ? $argc['title'] : '---';
		$sub_title = isset( $argc['sub_title'] ) ? $argc['sub_title'] : '---';
		$res       = '<div class="panel-heading">
            <h3 class="title"> ' . $title . '</h3>
            <p> ' . $sub_title . ' </p>
        </div>';

		return $res;
	}

	/**
	 * Generate button block
	 *
	 * @return string
	 */
	private function generate_button_block( $argc ) {
		if ( ! isset( $argc['show_btn'] ) ) {
			return false;
		}
		$btn_text      = isset( $argc['btn_text'] ) ? $argc['btn_text'] : 'Submit';
		$prepend_btn   = isset( $argc['prepend_btn'] ) ? $argc['prepend_btn'] : '';
		$hidden_fields = isset( $argc['hidden_fields'] ) ? $argc['hidden_fields'] : '';
		return '<div class="section-submit-button">
            ' . $hidden_fields . '
            ' . wp_nonce_field( SECURE_AUTH_SALT, 'cs_token' ) . '
            ' . $prepend_btn . '
            <input type="submit" class="btn btn-custom-submit" value="' . $btn_text . '" />
        </div>';
	}

	/**
	 * Generate footer
	 *
	 * @return string
	 */
	private function generate_footer() {
		return '<div class="panel-footer">
            <p>Thank you for choosing us! <a href="http://coinmarketstats.online" target="_blank">coinmarketstats.online</a></p>
        </div>';
	}
}
