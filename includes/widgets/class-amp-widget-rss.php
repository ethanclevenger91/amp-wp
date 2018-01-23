<?php
/**
 * Class AMP_Widget_RSS
 *
 * @package AMP
 */

/**
 * Class AMP_Widget_RSS
 *
 * @package AMP
 */
class AMP_Widget_RSS extends WP_Widget_RSS {

	/**
	 * Echoes the markup of the widget.
	 *
	 * @param array $args Widget display data.
	 * @param array $instance Data for widget.
	 * @return void.
	 */
	public function widget( $args, $instance ) {
		if ( is_amp_endpoint() ) {
			parent::widget( $args, $instance );
			return;
		}

		ob_start();
		parent::widget( $args, $instance );
		$output = ob_get_clean();
		echo AMP_Theme_Support::filter_the_content( $output ); // WPCS: XSS ok.
	}

}
