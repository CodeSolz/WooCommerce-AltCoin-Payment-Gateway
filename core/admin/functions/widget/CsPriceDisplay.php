<?php namespace WooGateWayCoreLib\admin\functions\widget;

/**
 * Coin recent prices - Widget
 *
 * @package WAPG Admin
 * @since 1.0.0
 * @author CoinMarketStats <support@coinmarketstats.online>
 */

if ( ! defined( 'CS_WAPG_VERSION' ) ) {
	exit;
}

use WooGateWayCoreLib\lib\Util;

class CsPriceDisplay extends \WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			'altcoin_display_coin_prices', // Base ID
			esc_html__( 'Altcoin - Display Coin Prices', 'woo-altcoin-payment-gateway' ), // Name
			array( 'description' => esc_html__( 'Display Coin Prices', 'woo-altcoin-payment-gateway' ) ) // Args
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		echo esc_html( $args['before_widget'] );
		if ( ! empty( $instance['title'] ) ) {
			echo esc_html( $args['before_title'] ) . apply_filters( 'widget_title', $instance['title'] ) . esc_html( $args['after_title'] );
		}
		?>
		<div class="altcoin_widget_price_display">
			<div id='tag-cloud'></div>
		</div>
			
		<?php
		echo esc_html( $args['after_widget'] );
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : esc_html__( 'Coins Recent Prices', 'woo-altcoin-payment-gateway' );
		?>
		<p>
			<label for="<?php echo esc_html( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title:', 'woo-altcoin-payment-gateway' ); ?></label> 
			<input class="widefat" id="<?php echo esc_html( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
			Please choose coin to show from <a href="<?php echo esc_url( Util::cs_generate_admin_url( 'cs-woo-altcoin-widget-settings' )); ?>"> AltCoin Payment</a> -> <a href="<?php echo esc_url( Util::cs_generate_admin_url( 'cs-woo-altcoin-widget-settings' )); ?>">Widget Settings</a> menu.
		</p>
		<?php
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance          = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? sanitize_text_field( $new_instance['title'] ) : '';

		return $instance;
	}

}
