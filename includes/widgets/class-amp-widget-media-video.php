<?php
/**
 * Class AMP_Widget_Media_Video
 *
 * @package AMP
 */

if ( class_exists( 'WP_Widget_Media_Video' ) ) {
	/**
	 * Class AMP_Widget_Media_Video
	 *
	 * @package AMP
	 */
	class AMP_Widget_Media_Video extends WP_Widget_Media_Video {

		/**
		 * Echoes the markup of the widget.
		 *
		 * @param array $instance Data for widget.
		 * @return void.
		 */
		public function render_media( $instance ) {
			if ( ! is_amp_endpoint() ) {
				parent::render_media( $instance );
				return;
			}

			ob_start();
			parent::render_media( $instance );
			$output = ob_get_clean();
			echo AMP_Theme_Support::filter_the_content( $output ); // WPCS: XSS ok.
		}

	}
}
