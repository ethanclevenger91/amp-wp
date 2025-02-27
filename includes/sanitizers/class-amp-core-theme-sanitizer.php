<?php
/**
 * Class AMP_Core_Theme_Sanitizer.
 *
 * @package AMP
 * @since 1.0
 */

/**
 * Class AMP_Core_Theme_Sanitizer
 *
 * Fixes up common issues in core themes and others.
 *
 * @see AMP_Validation_Error_Taxonomy::accept_core_theme_validation_errors()
 * @since 1.0
 */
class AMP_Core_Theme_Sanitizer extends AMP_Base_Sanitizer {

	/**
	 * Array of flags used to control sanitization.
	 *
	 * @since 1.0
	 * @var array {
	 *      @type string $stylesheet     Stylesheet slug.
	 *      @type string $template       Template slug.
	 *      @type array  $theme_features List of theme features that need to be applied. Features are method names,
	 * }
	 */
	protected $args;

	/**
	 * Body element.
	 *
	 * @since 1.0
	 * @var DOMElement
	 */
	protected $body;

	/**
	 * XPath.
	 *
	 * @since 1.0
	 * @var DOMXPath
	 */
	protected $xpath;

	/**
	 * Config for features needed by themes.
	 *
	 * @since 1.0
	 * @var array
	 */
	protected static $theme_features = [
		// Twenty Twenty.
		'twentytwenty'    => [
			'dequeue_scripts'                         => [
				'twentytwenty-js',
			],
			'remove_actions'                          => [
				'wp_head' => [
					'twentytwenty_no_js_class', // AMP is essentially no-js, with any interactivity added explicitly via amp-bind.
				],
			],
			'add_smooth_scrolling'                    => [
				// @todo Only replaces twentytwenty.smoothscroll.scrollToAnchor, but not twentytwenty.smoothscroll.scrollToElement
				'//a[ starts-with( @href, "#" ) and not( @href = "#" )and not( @href = "#0" ) and not( contains( @class, "do-not-scroll" ) ) and not( contains( @class, "skip-link" ) ) ]',
			],
			'add_twentytwenty_modals'                 => [],
			'add_twentytwenty_toggles'                => [],
			'add_nav_menu_styles'                     => [],
			'add_twentytwenty_masthead_styles'        => [],
			'add_twentytwenty_current_page_awareness' => [],
		],

		// Twenty Nineteen.
		'twentynineteen'  => [
			'dequeue_scripts'                    => [
				'twentynineteen-skip-link-focus-fix', // This is part of AMP. See <https://github.com/ampproject/amphtml/issues/18671>.
				'twentynineteen-priority-menu',
				'twentynineteen-touch-navigation', // @todo There could be an AMP implementation of this, similar to what is implemented on ampproject.org.
			],
			'remove_actions'                     => [
				'wp_print_footer_scripts' => [
					'twentynineteen_skip_link_focus_fix', // See <https://github.com/WordPress/twentynineteen/pull/47>.
				],
			],
			'add_twentynineteen_masthead_styles' => [],
			'adjust_twentynineteen_images'       => [],
		],

		// Twenty Seventeen.
		'twentyseventeen' => [
			// @todo Try to implement belowEntryMetaClass().
			'dequeue_scripts'                     => [
				'twentyseventeen-html5', // Only relevant for IE<9.
				'twentyseventeen-global', // There are somethings not yet implemented in AMP. See todos below.
				'jquery-scrollto', // Implemented via add_smooth_scrolling().
				'twentyseventeen-navigation', // Handled by add_nav_menu_styles, add_nav_menu_toggle, add_nav_sub_menu_buttons.
				'twentyseventeen-skip-link-focus-fix', // Unnecessary since part of the AMP runtime.
			],
			'remove_actions'                      => [
				'wp_head' => [
					'twentyseventeen_javascript_detection', // AMP is essentially no-js, with any interactivity added explicitly via amp-bind.
				],
			],
			'force_fixed_background_support'      => [],
			'add_twentyseventeen_masthead_styles' => [],
			'add_twentyseventeen_image_styles'    => [],
			'add_twentyseventeen_sticky_nav_menu' => [],
			'add_has_header_video_body_class'     => [],
			'add_nav_menu_styles'                 => [
				'sub_menu_button_toggle_class' => 'toggled-on',
				'no_js_submenu_visible'        => true,
			],
			'add_smooth_scrolling'                => [
				'//header[@id = "masthead"]//a[ contains( @class, "menu-scroll-down" ) ]',
			],
			'set_twentyseventeen_quotes_icon'     => [],
			'add_twentyseventeen_attachment_image_attributes' => [],
		],

		// Twenty Sixteen.
		'twentysixteen'   => [
			// @todo Figure out an AMP solution for onResizeARIA().
			// @todo Try to implement belowEntryMetaClass().
			'dequeue_scripts'     => [
				'twentysixteen-script',
				'twentysixteen-html5', // Only relevant for IE<9.
				'twentysixteen-keyboard-image-navigation', // AMP does not yet allow for listening to keydown events.
				'twentysixteen-skip-link-focus-fix', // Unnecessary since part of the AMP runtime.
			],
			'remove_actions'      => [
				'wp_head' => [
					'twentysixteen_javascript_detection', // AMP is essentially no-js, with any interactivity added explicitly via amp-bind.
				],
			],
			'add_nav_menu_styles' => [
				'sub_menu_button_toggle_class' => 'toggled-on',
				'no_js_submenu_visible'        => true,
			],
		],

		// Twenty Fifteen.
		'twentyfifteen'   => [
			// @todo Figure out an AMP solution for onResizeARIA().
			'dequeue_scripts'     => [
				'twentyfifteen-script',
				'twentyfifteen-keyboard-image-navigation', // AMP does not yet allow for listening to keydown events.
				'twentyfifteen-skip-link-focus-fix', // Unnecessary since part of the AMP runtime.
			],
			'remove_actions'      => [
				'wp_head' => [
					'twentyfifteen_javascript_detection', // AMP is essentially no-js, with any interactivity added explicitly via amp-bind.
				],
			],
			'add_nav_menu_styles' => [
				'sub_menu_button_toggle_class' => 'toggle-on',
				'no_js_submenu_visible'        => true,
			],
		],

		// Twenty Fourteen.
		'twentyfourteen'  => [
			// @todo Figure out an AMP solution for onResizeARIA().
			'dequeue_scripts'                    => [
				'twentyfourteen-script',
				'twentyfourteen-keyboard-image-navigation', // AMP does not yet allow for listening to keydown events.
				'jquery-masonry', // Masonry style layout is not supported in AMP.
				'twentyfourteen-slider',
			],
			'add_nav_menu_styles'                => [],
			'add_twentyfourteen_masthead_styles' => [],
			'add_twentyfourteen_slider_carousel' => [],
			'add_twentyfourteen_search'          => [],
		],

		// Twenty Thirteen.
		'twentythirteen'  => [
			'dequeue_scripts'          => [
				'jquery-masonry', // Masonry style layout is not supported in AMP.
				'twentythirteen-script',
			],
			'add_nav_menu_toggle'      => [],
			'add_nav_sub_menu_buttons' => [],
			'add_nav_menu_styles'      => [],
		],

		// Twenty Twelve.
		'twentytwelve'    => [
			'dequeue_scripts'     => [
				'twentytwelve-navigation',
			],
			'add_nav_menu_styles' => [],
		],

		'twentyeleven'    => [],
		'twentyten'       => [],
	];

	/**
	 * Get list of supported core themes.
	 *
	 * @since 1.0
	 *
	 * @return string[] Slugs for supported themes.
	 */
	public static function get_supported_themes() {
		return array_keys( self::$theme_features );
	}

	/**
	 * Get the acceptable validation errors.
	 *
	 * @since 1.0
	 *
	 * @param string $template Template.
	 * @return array Acceptable errors.
	 */
	public static function get_acceptable_errors( $template ) {
		if ( isset( self::$theme_features[ $template ] ) ) {
			return [
				'illegal_css_at_rule' => [
					[
						'at_rule' => 'viewport',
					],
					[
						'at_rule' => '-ms-viewport',
					],
				],
			];
		}
		return [];
	}

	/**
	 * Adds extra theme support arguments on the fly.
	 *
	 * This method is neither a buffering hook nor a sanitization callback and is called manually by
	 * {@see AMP_Theme_Support}. Typically themes will add theme support directly and don't need such
	 * a method. In this case, it is a workaround for adding theme support on behalf of external themes.
	 *
	 * @since 1.1
	 */
	public static function extend_theme_support() {
		$args = self::get_theme_support_args( get_template() );

		if ( empty( $args ) ) {
			return;
		}

		$support = AMP_Theme_Support::get_theme_support_args();
		if ( ! is_array( $support ) ) {
			$support = [];
		}

		add_theme_support( AMP_Theme_Support::SLUG, array_merge( $support, $args ) );
	}

	/**
	 * Returns extra arguments to pass to `add_theme_support()`.
	 *
	 * @since 1.1
	 *
	 * @param string $theme Theme slug.
	 * @return array Arguments to merge with existing theme support arguments.
	 */
	protected static function get_theme_support_args( $theme ) {
		// phpcs:disable WordPress.WP.I18n.TextDomainMismatch
		switch ( $theme ) {
			case 'twentytwelve':
				return [
					'nav_menu_toggle' => [
						'nav_container_xpath'        => '//nav[ @id = "site-navigation" ]//ul',
						'nav_container_toggle_class' => 'toggled-on',
						'menu_button_xpath'          => '//nav[ @id = "site-navigation" ]//button[ contains( @class, "menu-toggle" ) ]',
						'menu_button_toggle_class'   => 'toggled-on',
					],
				];
			case 'twentythirteen':
				return [
					'nav_menu_toggle'   => [
						'nav_container_id'           => 'site-navigation',
						'nav_container_toggle_class' => 'toggled-on',
						'menu_button_xpath'          => '//nav[ @id = "site-navigation" ]//button[ contains( @class, "menu-toggle" ) ]',
					],
					'nav_menu_dropdown' => [
						'sub_menu_button_class'        => 'dropdown-toggle',
						'sub_menu_button_toggle_class' => 'toggle-on',
						'expand_text'                  => __( 'expand child menu', 'amp' ),
						'collapse_text'                => __( 'collapse child menu', 'amp' ),
					],
					'nav_menu_styles'   => [],
				];
			case 'twentyfourteen':
				return [
					'nav_menu_toggle' => [
						'nav_container_id'           => 'primary-navigation',
						'nav_container_toggle_class' => 'toggled-on',
						'menu_button_xpath'          => '//header[ @id = "masthead" ]//button[ contains( @class, "menu-toggle" ) ]',
						'menu_button_toggle_class'   => '',
					],
				];

			case 'twentyfifteen':
				return [
					'nav_menu_toggle'   => [
						'nav_container_id'           => 'secondary',
						'nav_container_toggle_class' => 'toggled-on',
						'menu_button_xpath'          => '//header[ @id = "masthead" ]//button[ contains( @class, "secondary-toggle" ) ]',
						'menu_button_toggle_class'   => 'toggled-on',
					],
					'nav_menu_dropdown' => [
						'sub_menu_button_class'        => 'dropdown-toggle',
						'sub_menu_button_toggle_class' => 'toggle-on',
						'expand_text '                 => __( 'expand child menu', 'twentyfifteen' ),
						'collapse_text'                => __( 'collapse child menu', 'twentyfifteen' ),
					],
				];

			case 'twentysixteen':
				return [
					'nav_menu_toggle'   => [
						'nav_container_id'           => 'site-header-menu',
						'nav_container_toggle_class' => 'toggled-on',
						'menu_button_xpath'          => '//header[@id = "masthead"]//button[ @id = "menu-toggle" ]',
						'menu_button_toggle_class'   => 'toggled-on',
					],
					'nav_menu_dropdown' => [
						'sub_menu_button_class'        => 'dropdown-toggle',
						'sub_menu_button_toggle_class' => 'toggled-on',
						'expand_text '                 => __( 'expand child menu', 'twentysixteen' ),
						'collapse_text'                => __( 'collapse child menu', 'twentysixteen' ),
					],
				];

			case 'twentyseventeen':
				$config = [
					'nav_menu_toggle'   => [
						'nav_container_id'           => 'site-navigation',
						'nav_container_toggle_class' => 'toggled-on',
						'menu_button_xpath'          => '//nav[@id = "site-navigation"]//button[ contains( @class, "menu-toggle" ) ]',
						'menu_button_toggle_class'   => 'toggled-on',
					],
					'nav_menu_dropdown' => [
						'sub_menu_button_class'        => 'dropdown-toggle',
						'sub_menu_button_toggle_class' => 'toggled-on',
						'expand_text '                 => __( 'expand child menu', 'twentyseventeen' ),
						'collapse_text'                => __( 'collapse child menu', 'twentyseventeen' ),
					],
				];

				if ( function_exists( 'twentyseventeen_get_svg' ) ) {
					$config['nav_menu_dropdown']['icon'] = twentyseventeen_get_svg(
						[
							'icon'     => 'angle-down',
							'fallback' => true,
						]
					);
				}

				return $config;
		}
		// phpcs:enable WordPress.WP.I18n.TextDomainMismatch

		return [];
	}

	/**
	 * Get theme config.
	 *
	 * @since 1.0
	 * @deprecated 1.1
	 *
	 * @param string $theme Theme slug.
	 * @return array Class names.
	 */
	protected static function get_theme_config( $theme ) {
		_deprecated_function( __METHOD__, '1.1' );

		$args = self::get_theme_support_args( $theme );

		// This returns arguments in a backward-compatible way.
		return array_merge( $args['nav_menu_toggle'], $args['nav_menu_dropdown'] );
	}

	/**
	 * Find theme features for core theme.
	 *
	 * @since 1.0
	 *
	 * @param array $args   Args.
	 * @param bool  $static Static. that is, whether should run during output buffering.
	 * @return array Theme features.
	 */
	protected static function get_theme_features( $args, $static = false ) {
		$theme_features   = [];
		$theme_candidates = wp_array_slice_assoc( $args, [ 'stylesheet', 'template' ] );
		foreach ( $theme_candidates as $theme_candidate ) {
			if ( isset( self::$theme_features[ $theme_candidate ] ) ) {
				$theme_features = self::$theme_features[ $theme_candidate ];
				break;
			}
		}

		// Allow specific theme features to be requested even if the theme is not in core.
		if ( isset( $args['theme_features'] ) ) {
			$theme_features = array_merge( $args['theme_features'], $theme_features );
		}

		$final_theme_features = [];
		foreach ( $theme_features as $theme_feature => $feature_args ) {
			if ( ! method_exists( __CLASS__, $theme_feature ) ) {
				continue;
			}
			try {
				$reflection = new ReflectionMethod( __CLASS__, $theme_feature );
				if ( $reflection->isStatic() === $static ) {
					$final_theme_features[ $theme_feature ] = $feature_args;
				}
			} catch ( Exception $e ) {
				unset( $e );
			}
		}
		return $final_theme_features;
	}

	/**
	 * Add filters to manipulate output during output buffering before the DOM is constructed.
	 *
	 * @since 1.0
	 *
	 * @param array $args Args.
	 */
	public static function add_buffering_hooks( $args = [] ) {
		$theme_features = self::get_theme_features( $args, true );
		foreach ( $theme_features as $theme_feature => $feature_args ) {
			if ( method_exists( __CLASS__, $theme_feature ) ) {
				call_user_func( [ __CLASS__, $theme_feature ], $feature_args );
			}
		}
	}

	/**
	 * Add filter to output the quote icons in front of the article content.
	 *
	 * This is only used in Twenty Seventeen.
	 *
	 * @since 1.0
	 * @link https://github.com/WordPress/wordpress-develop/blob/f4580c122b7d0d2d66d22f806c6fe6e11023c6f0/src/wp-content/themes/twentyseventeen/assets/js/global.js#L105-L108
	 */
	public static function set_twentyseventeen_quotes_icon() {
		add_filter(
			'the_content',
			static function ( $content ) {

				// Why isn't Twenty Seventeen doing this to begin with? Why is it using JS to add the quote icon?
				if ( function_exists( 'twentyseventeen_get_svg' ) && 'quote' === get_post_format() ) {
					$icon    = twentyseventeen_get_svg( [ 'icon' => 'quote-right' ] );
					$content = preg_replace( '#(<blockquote.*?>)#s', '$1' . $icon, $content );
				}

				return $content;
			}
		);
	}

	/**
	 * Add filter to adjust the attachment image attributes to ensure attachment pages have a consistent <amp-img> rendering.
	 *
	 * This is only used in Twenty Seventeen.
	 *
	 * @since 1.0
	 * @link https://github.com/WordPress/wordpress-develop/blob/ddc8f803c6e99118998191fd2ea24124feb53659/src/wp-content/themes/twentyseventeen/functions.php#L545:L554
	 */
	public static function add_twentyseventeen_attachment_image_attributes() {
		/*
		 * The max-height of the `.custom-logo-link img` is defined as being 80px, unless
		 * there is header media in which case it is 200px. Issues related to vertically-squashed
		 * images can be avoided if we just make sure that the image has this height to begin with.
		 */
		add_filter(
			'get_custom_logo',
			static function( $html ) {
				$src = wp_get_attachment_image_src( get_theme_mod( 'custom_logo' ), 'full' );
				if ( ! $src ) {
					return $html;
				}

				if ( 'blank' === get_header_textcolor() && has_custom_header() ) {
					$height = 200;
				} else {
					$height = 80;
				}
				$width = $height * ( $src[1] / $src[2] ); // Note that float values are allowed.

				$html = preg_replace( '/(?<=width=")\d+(?=")/', $width, $html );
				$html = preg_replace( '/(?<=height=")\d+(?=")/', $height, $html );
				return $html;
			}
		);
	}

	/**
	 * Fix up core themes to do things in the AMP way.
	 *
	 * @since 1.0
	 */
	public function sanitize() {
		$this->body = $this->dom->getElementsByTagName( 'body' )->item( 0 );
		if ( ! $this->body ) {
			return;
		}

		$this->xpath = new DOMXPath( $this->dom );

		$theme_features = self::get_theme_features( $this->args, false );
		foreach ( $theme_features as $theme_feature => $feature_args ) {
			if ( method_exists( $this, $theme_feature ) ) {
				$this->$theme_feature( $feature_args );
			}
		}
	}

	/**
	 * Dequeue scripts.
	 *
	 * @since 1.0
	 *
	 * @param string[] $handles Handles, where each item value is the script handle.
	 */
	public static function dequeue_scripts( $handles = [] ) {
		add_action(
			'wp_enqueue_scripts',
			static function() use ( $handles ) {
				foreach ( $handles as $handle ) {
					wp_dequeue_script( $handle );
				}
			},
			PHP_INT_MAX
		);
	}

	/**
	 * Remove actions.
	 *
	 * @since 1.0
	 *
	 * @param array $actions Actions, with action name as key and value being callback.
	 */
	public static function remove_actions( $actions = [] ) {
		foreach ( $actions as $action => $callbacks ) {
			foreach ( $callbacks as $callback ) {
				$priority = has_action( $action, $callback );
				if ( false !== $priority ) {
					remove_action( $action, $callback, $priority );
				}
			}
		}
	}

	/**
	 * Add smooth scrolling from link to target element.
	 *
	 * @since 1.0
	 *
	 * @param string[] $link_xpaths XPath queries to the links that should smooth scroll.
	 */
	public function add_smooth_scrolling( $link_xpaths ) {
		foreach ( $link_xpaths as $link_xpath ) {
			foreach ( $this->xpath->query( $link_xpath ) as $link ) {
				if ( $link instanceof DOMElement && preg_match( '/#(.+)/', $link->getAttribute( 'href' ), $matches ) ) {
					$link->setAttribute( 'on', sprintf( 'tap:%s.scrollTo(duration=600)', $matches[1] ) );

					// Prevent browser from jumping immediately to the link target.
					$link->removeAttribute( 'href' );
					$link->setAttribute( 'tabindex', '0' );
					$link->setAttribute( 'role', 'button' );
				}
			}
		}
	}

	/**
	 * Force SVG support, replacing no-svg class name with svg class name.
	 *
	 * @since 1.0
	 *
	 * @link https://github.com/WordPress/wordpress-develop/blob/1af1f65a21a1a697fb5f33027497f9e5ae638453/src/wp-content/themes/twentyseventeen/assets/js/global.js#L211-L213
	 * @link https://caniuse.com/#feat=svg
	 */
	public function force_svg_support() {
		$class = $this->dom->documentElement->getAttribute( 'class' );

		if ( $class ) {
			$count = 0;
			$class = preg_replace(
				'/(^|\s)no-svg(\s|$)/',
				' svg ',
				$class,
				-1,
				$count
			);

			if ( $count > 0 ) {
				$this->dom->documentElement->setAttribute( 'class', $class );
			}
		}
	}

	/**
	 * Force support for fixed background-attachment.
	 *
	 * @since 1.0
	 *
	 * @link https://github.com/WordPress/wordpress-develop/blob/1af1f65a21a1a697fb5f33027497f9e5ae638453/src/wp-content/themes/twentyseventeen/assets/js/global.js#L215-L217
	 * @link https://caniuse.com/#feat=background-attachment
	 */
	public function force_fixed_background_support() {
		$this->dom->documentElement->setAttribute(
			'class',
			$this->dom->documentElement->getAttribute( 'class' ) . ' background-fixed'
		);
	}

	/**
	 * Add body class when there is a header video.
	 *
	 * @since 1.0
	 * @link https://github.com/WordPress/wordpress-develop/blob/a26c24226c6b131a0ed22c722a836c100d3ba254/src/wp-content/themes/twentyseventeen/assets/js/global.js#L244-L247
	 *
	 * @param array $args Args.
	 */
	public static function add_has_header_video_body_class( $args = [] ) {
		$args = array_merge(
			[
				'class_name' => 'has-header-video',
			],
			$args
		);

		add_filter(
			'body_class',
			static function( $body_classes ) use ( $args ) {
				if ( has_header_video() ) {
					$body_classes[] = $args['class_name'];
				}
				return $body_classes;
			}
		);
	}

	/**
	 * Get the (common) navigation outer height.
	 *
	 * @todo If the nav menu has many items and it spans multiple rows, this will be too small.
	 * @link https://github.com/WordPress/wordpress-develop/blob/fd5ba80c5c3d9cf62348567073945e246285fbca/src/wp-content/themes/twentyseventeen/assets/js/global.js#L50
	 *
	 * @return int Navigation outer height.
	 */
	protected static function get_twentyseventeen_navigation_outer_height() {
		return 72;
	}

	/**
	 * Add required styles for featured image header and image blocks in Twenty Twenty.
	 */
	public static function add_twentytwenty_masthead_styles() {
		add_action(
			'wp_enqueue_scripts',
			static function() {
				ob_start();
				?>
				<style>
				.featured-media amp-img {
					position: static;
				}

				.wp-block-image img {
					display: block;
				}
				</style>
				<?php
				$styles = str_replace( [ '<style>', '</style>' ], '', ob_get_clean() );
				wp_add_inline_style( get_template() . '-style', $styles );
			},
			11
		);
	}

	/**
	 * Add required styles for featured image header in Twenty Nineteen.
	 *
	 * The following is necessary because the styles in the theme apply to the featured img,
	 * and the CSS parser will then convert the selectors to amp-img. Nevertheless, object-fit
	 * does not apply on amp-img and it needs to apply on an actual img.
	 *
	 * @link https://github.com/WordPress/wordpress-develop/blob/5.0/src/wp-content/themes/twentynineteen/style.css#L2276-L2299
	 * @since 1.0
	 */
	public static function add_twentynineteen_masthead_styles() {
		add_action(
			'wp_enqueue_scripts',
			static function() {
				ob_start();
				?>
				<style>
				.site-header.featured-image .site-featured-image .post-thumbnail amp-img > img {
					height: auto;
					left: 50%;
					max-width: 1000%;
					min-height: 100%;
					min-width: 100vw;
					position: absolute;
					top: 50%;
					transform: translateX(-50%) translateY(-50%);
					width: auto;
					z-index: 1;
					/* When image filters are active, make it grayscale to colorize it blue. */
				}

				@supports (object-fit: cover) {
					.site-header.featured-image .site-featured-image .post-thumbnail amp-img > img {
						height: 100%;
						left: 0;
						object-fit: cover;
						top: 0;
						transform: none;
						width: 100%;
					}
				}
				</style>
				<?php
				$styles = str_replace( [ '<style>', '</style>' ], '', ob_get_clean() );
				wp_add_inline_style( get_template() . '-style', $styles );
			},
			11
		);
	}

	/**
	 * Add required styles for video and image headers.
	 *
	 * This is currently used exclusively for Twenty Seventeen.
	 *
	 * @since 1.0
	 * @link https://github.com/WordPress/wordpress-develop/blob/1af1f65a21a1a697fb5f33027497f9e5ae638453/src/wp-content/themes/twentyseventeen/style.css#L1687
	 * @link https://github.com/WordPress/wordpress-develop/blob/1af1f65a21a1a697fb5f33027497f9e5ae638453/src/wp-content/themes/twentyseventeen/style.css#L1743
	 */
	public static function add_twentyseventeen_masthead_styles() {
		/*
		 * The following is necessary because the styles in the theme apply to img and video,
		 * and the CSS parser will then convert the selectors to amp-img and amp-video respectively.
		 * Nevertheless, object-fit does not apply on amp-img and it needs to apply on an actual img.
		 */
		add_action(
			'wp_enqueue_scripts',
			static function() {
				$is_front_page_layout = ( is_front_page() && 'posts' !== get_option( 'show_on_front' ) ) || ( is_home() && is_front_page() );
				ob_start();
				?>
				<style>
				.has-header-image .custom-header-media amp-img > img,
				.has-header-video .custom-header-media amp-video > video{
					position: fixed;
					height: auto;
					left: 50%;
					max-width: 1000%;
					min-height: 100%;
					min-width: 100%;
					min-width: 100vw; /* vw prevents 1px gap on left that 100% has */
					width: auto;
					top: 50%;
					padding-bottom: 1px; /* Prevent header from extending beyond the footer */
					-ms-transform: translateX(-50%) translateY(-50%);
					-moz-transform: translateX(-50%) translateY(-50%);
					-webkit-transform: translateX(-50%) translateY(-50%);
					transform: translateX(-50%) translateY(-50%);
				}
				.has-header-image:not(.twentyseventeen-front-page):not(.home) .custom-header-media amp-img > img {
					bottom: 0;
					position: absolute;
					top: auto;
					-ms-transform: translateX(-50%) translateY(0);
					-moz-transform: translateX(-50%) translateY(0);
					-webkit-transform: translateX(-50%) translateY(0);
					transform: translateX(-50%) translateY(0);
				}
				/* For browsers that support object-fit */
				@supports ( object-fit: cover ) {
					.has-header-image .custom-header-media amp-img > img,
					.has-header-video .custom-header-media amp-video > video,
					.has-header-image:not(.twentyseventeen-front-page):not(.home) .custom-header-media amp-img > img {
						height: 100%;
						left: 0;
						-o-object-fit: cover;
						object-fit: cover;
						top: 0;
						-ms-transform: none;
						-moz-transform: none;
						-webkit-transform: none;
						transform: none;
						width: 100%;
					}
				}

				.navigation-top.site-navigation-fixed {
					display: none;
				}

				/* This is needed by add_smooth_scrolling because it removes the [href] attribute. */
				.menu-scroll-down {
					cursor: pointer;
				}

				<?php if ( $is_front_page_layout && ! has_custom_header() ) : ?>
					/* https://github.com/WordPress/wordpress-develop/blob/fd5ba80c5c3d9cf62348567073945e246285fbca/src/wp-content/themes/twentyseventeen/assets/js/global.js#L92-L94 */
					.site-branding {
						margin-bottom: <?php echo (int) AMP_Core_Theme_Sanitizer::get_twentyseventeen_navigation_outer_height(); ?>px;
					}
				<?php endif; ?>

				@media screen and (min-width: 48em) {
					/* Note that adjustHeaderHeight() is irrelevant with this change */
					<?php if ( ! $is_front_page_layout ) : ?>
						.navigation-top {
							position: static;
						}
					<?php endif; ?>

					/* Initial styles that amp-animations for navigationTopShow and navigationTopHide will override */
					.navigation-top.site-navigation-fixed {
						opacity: 0;
						transform: translateY( -<?php echo (int) AMP_Core_Theme_Sanitizer::get_twentyseventeen_navigation_outer_height(); ?>px );
						display: block;
					}
				}
				</style>
				<?php
				$styles = str_replace( [ '<style>', '</style>' ], '', ob_get_clean() );
				wp_add_inline_style( get_template() . '-style', $styles );
			},
			11
		);
	}

	/**
	 * Override the featured image header styling in style.css.
	 * Used only for Twenty Seventeen.
	 *
	 * @since 1.0
	 * @link https://github.com/WordPress/wordpress-develop/blob/1af1f65a21a1a697fb5f33027497f9e5ae638453/src/wp-content/themes/twentyseventeen/style.css#L2100
	 */
	public static function add_twentyseventeen_image_styles() {
		add_action(
			'wp_enqueue_scripts',
			static function() {
				ob_start();
				?>
				<style>
				/* Override the display: block in twentyseventeen/style.css, as <amp-img> is usually inline-block. */
				.single-featured-image-header amp-img {
					display: inline-block;
				}

				/* Because the <amp-img> is inline-block, its container needs this rule to center it. */
				.single-featured-image-header {
					text-align: center;
				}
				</style>
				<?php
				$styles = str_replace( [ '<style>', '</style>' ], '', ob_get_clean() );
				wp_add_inline_style( get_template() . '-style', $styles );
			},
			11
		);
	}

	/**
	 * Add sticky nav menu to Twenty Seventeen.
	 *
	 * This is implemented by cloning the navigation-top element, giving it a fixed position outside of the viewport,
	 * and then showing it at the top of the window as soon as the original nav begins to get scrolled out of view.
	 * In order to improve accessibility, the cloned nav gets aria-hidden=true and all of the links get tabindex=-1
	 * to prevent the keyboard from focusing on elements off the screen; it is not necessary to focus on the elements
	 * in the fixed nav menu because as soon as the original nav menu is focused then the window is scrolled to the
	 * top anyway.
	 *
	 * @since 1.0
	 */
	public function add_twentyseventeen_sticky_nav_menu() {
		/**
		 * Elements.
		 *
		 * @var DOMElement $link
		 * @var DOMElement $element
		 * @var DOMElement $navigation_top
		 * @var DOMElement $navigation_top_fixed
		 */
		$navigation_top = $this->xpath->query( '//header[ @id = "masthead" ]//div[ contains( @class, "navigation-top" ) ]' )->item( 0 );
		if ( ! $navigation_top ) {
			return;
		}

		$navigation_top_fixed = $navigation_top->cloneNode( true );
		$navigation_top_fixed->setAttribute( 'class', $navigation_top_fixed->getAttribute( 'class' ) . ' site-navigation-fixed' );

		$navigation_top_fixed->setAttribute( 'aria-hidden', 'true' );
		foreach ( $navigation_top_fixed->getElementsByTagName( 'a' ) as $link ) {
			$link->setAttribute( 'tabindex', '-1' );
		}

		$navigation_top->parentNode->insertBefore( $navigation_top_fixed, $navigation_top->nextSibling );
		foreach ( $this->xpath->query( './/*[ @id ]', $navigation_top_fixed ) as $element ) {
			$element->setAttribute( 'id', $element->getAttribute( 'id' ) . '-fixed' );
		}

		$attributes = [
			'layout'              => 'nodisplay',
			'intersection-ratios' => 1,
			'on'                  => implode(
				';',
				[
					'exit:navigationTopShow.start',
					'enter:navigationTopHide.start',
				]
			),
		];
		if ( is_admin_bar_showing() ) {
			$attributes['viewport-margins'] = '32px 0';
		}
		$position_observer = AMP_DOM_Utils::create_node( $this->dom, 'amp-position-observer', $attributes );
		$navigation_top->appendChild( $position_observer );

		$animations = [
			'navigationTopShow' => [
				'duration'   => 0,
				'fill'       => 'both',
				'animations' => [
					'selector'  => '.navigation-top.site-navigation-fixed',
					'media'     => '(min-width: 48em)',
					'keyframes' => [
						'opacity'   => 1.0,
						'transform' => 'translateY( 0 )',
					],
				],
			],
			'navigationTopHide' => [
				'duration'   => 0,
				'fill'       => 'both',
				'animations' => [
					'selector'  => '.navigation-top.site-navigation-fixed',
					'media'     => '(min-width: 48em)',
					'keyframes' => [
						'opacity'   => 0.0,
						'transform' => sprintf( 'translateY( -%dpx )', self::get_twentyseventeen_navigation_outer_height() ),
					],
				],
			],
		];

		foreach ( $animations as $animation_id => $animation ) {
			$amp_animation   = AMP_DOM_Utils::create_node(
				$this->dom,
				'amp-animation',
				[
					'id'     => $animation_id,
					'layout' => 'nodisplay',
				]
			);
			$position_script = $this->dom->createElement( 'script' );
			$position_script->setAttribute( 'type', 'application/json' );
			$position_script->appendChild( $this->dom->createTextNode( wp_json_encode( $animation ) ) );
			$amp_animation->appendChild( $position_script );
			$this->body->appendChild( $amp_animation );
		}
	}

	/**
	 * Add styles for the nav menu specifically to deal with AMP running in a no-js context.
	 *
	 * @since 1.0
	 *
	 * @param array $args Args.
	 */
	public static function add_nav_menu_styles( $args = [] ) {
		add_action(
			'wp_enqueue_scripts',
			static function() use ( $args ) {
				ob_start();
				?>
				<style>
				<?php if ( ! empty( $args['no_js_submenu_visible'] ) ) : ?>
					/* Override no-js selector in parent theme. */
					<?php
					$selector = is_string( $args['no_js_submenu_visible'] ) ? $args['no_js_submenu_visible'] : '.no-js .main-navigation ul ul';
					?>
					<?php echo esc_html( $selector ); ?> {
						display: none;
					}
				<?php endif; ?>

				<?php if ( ! empty( $args['sub_menu_button_toggle_class'] ) ) : ?>
					/* Use sibling selector and re-use class on button instead of toggling toggle-on class on ul.sub-menu */
					.main-navigation ul .<?php echo esc_html( $args['sub_menu_button_toggle_class'] ); ?> + .sub-menu {
						display: block;
					}
				<?php endif; ?>

				<?php if ( 'twentytwenty' === get_template() ) : ?>
					.cover-modal {
						display: inherit;
					}

					.menu-modal-inner {
						height: 100%;
					}

					.admin-bar .cover-modal {
						/* Use padding to shift down modal because amp-lightbox has top:0 !important. */
						padding-top: 32px;
					}

					@media (max-width: 782px) {
						.admin-bar .cover-modal {
							/* Use padding to shift down modal because amp-lightbox has top:0 !important. */
							padding-top: 46px;
						}
					}

				}
				<?php elseif ( 'twentyseventeen' === get_template() ) : ?>
					/* Show the button*/
					.no-js .menu-toggle {
						display: block;
					}
					.no-js .main-navigation > div > ul {
						display: none;
					}
					.no-js .main-navigation.toggled-on > div > ul {
						display: block;
					}
					@media screen and (min-width: 48em) {
						.no-js .menu-toggle,
						.no-js .dropdown-toggle {
							display: none;
						}
						.no-js .main-navigation ul,
						.no-js .main-navigation ul ul,
						.no-js .main-navigation > div > ul {
							display: block;
						}
					}
				<?php elseif ( 'twentysixteen' === get_template() ) : ?>
					@media screen and (max-width: 56.875em) {
						/* Show the button*/
						.no-js .menu-toggle {
							display: block;
						}
						.no-js .site-header-menu {
							display: none;
						}
						.no-js .site-header-menu.toggled-on {
							display: block;
						}
					}
					@media screen and (min-width: 56.875em) {
						.no-js .main-navigation ul ul {
							display: block;
						}
					}
				<?php elseif ( 'twentyfifteen' === get_template() ) : ?>
					@media screen and (min-width: 59.6875em) {
						/* Attempt to emulate https://github.com/WordPress/wordpress-develop/blob/5e9a39baa7d4368f7d3c36dcbcd53db6317677c9/src/wp-content/themes/twentyfifteen/js/functions.js#L108-L149 */
						#sidebar {
							position: sticky;
							top: -9vh;
							max-height: 109vh;
							overflow-y: auto;
						}
					}

				<?php elseif ( 'twentythirteen' === get_template() ) : ?>
					@media (min-width: 644px) {
						.dropdown-toggle {
							display: none;
						}
					}
					@media (max-width: 643px) {
						.nav-menu .toggle-on + .sub-menu {
							clip: inherit;
							overflow: inherit;
							height: inherit;
							width: inherit;
						}
						/* Override :hover selector rules in theme which would cause submenu to persist open. */
						ul.nav-menu li:hover button:not( .toggle-on ) +  ul,
						.nav-menu ul li:hover button:not( .toggle-on ) +  ul {
							height: 1px;
							width: 1px;
							overflow: hidden;
							clip: rect(1px, 1px, 1px, 1px);
						}
						.menu-item-has-children {
							position: relative;
						}
						.dropdown-toggle {
							-moz-osx-font-smoothing: grayscale;
							-webkit-font-smoothing: antialiased;
							display: inline-block;
							font-size: 16px;
							font-style: normal;
							font-weight: normal;
							font-variant: normal;
							line-height: 1;
							text-align: center;
							text-decoration: inherit;
							text-transform: none;
							vertical-align: top;
							border: 0;
							box-sizing: content-box;
							content: "";
							height: 42px;
							padding: 0;
							background: transparent;
							width: 42px;
							position: absolute;
							top: 3px;
							<?php if ( is_rtl() ) : ?>
								left: 0;
							<?php else : ?>
								right: 0;
							<?php endif; ?>
						}
						.dropdown-toggle:active,
						.dropdown-toggle:focus,
						.dropdown-toggle:hover {
							padding: 0;
							border: 0;
							background: transparent;
						}
						.dropdown-toggle:after {
							color: #333;
							speak: none;
							font-family: "Genericons";
							content: "\f431";
							font-size: 24px;
							line-height: 42px;
							position: relative;
							top: 0;
							width: 42px;
							<?php if ( is_rtl() ) : ?>
								left: 1px;
							<?php else : ?>
								right: 1px;
							<?php endif; ?>
						}
						.dropdown-toggle.toggle-on:after {
							content: "\f432";
						}
						.dropdown-toggle:hover,
						.dropdown-toggle:focus {
							background-color: rgba(51, 51, 51, 0.1);

						.dropdown-toggle:focus {
							outline: 1px solid rgba(51, 51, 51, 0.3);
						}
					}
				<?php endif; ?>
				</style>
				<?php
				$styles = str_replace( [ '<style>', '</style>' ], '', ob_get_clean() );
				wp_add_inline_style( get_template() . '-style', $styles );
			},
			11
		);
	}

	/**
	 * Adjust images in twentynineteen.
	 *
	 * @since 1.1
	 */
	public static function adjust_twentynineteen_images() {

		// Make sure the featured image gets responsive layout.
		add_filter(
			'wp_get_attachment_image_attributes',
			static function( $attributes ) {
				if ( preg_match( '/(^|\s)(attachment-post-thumbnail)(\s|$)/', $attributes['class'] ) ) {
					$attributes['data-amp-layout'] = 'responsive';
				}
				return $attributes;
			}
		);
	}

	/**
	 * Add styles for Twenty Fourteen masthead.
	 *
	 * @since 1.1
	 */
	public static function add_twentyfourteen_masthead_styles() {
		add_action(
			'wp_enqueue_scripts',
			static function() {
				ob_start();
				?>
				<style>
					/* Styles for featured content */
					.grid #featured-content .post-thumbnail,
					.slider #featured-content .post-thumbnail {
						padding-top: 0; /* Override responsive hack which is handled by AMP layout. */
						overflow: visible;
					}
					.featured-content .post-thumbnail amp-img {
						position: static;
						left: auto;
						top: auto;
					}
					.slider #featured-content .hentry {
						display: block;
					}

					/*
					 * The following are needed because clicking on the :before pseudo element does not trigger a tap event.
					 * So instead of positioning the screen reader text off screen, we just position it to cover cover the
					 * toggle button entirely, with a zero opacity.
					 */
					.search-toggle {
						position: relative;
					}
					.search-toggle > a.screen-reader-text {
						left: 0;
						top: 0;
						right: 0;
						bottom: 0;
						width: auto;
						height: auto;
						clip: unset;
						opacity: 0;
					}

					<?php if ( 'slider' === get_theme_mod( 'featured_content_layout' ) ) : ?>

						/*
						 * Styles for slider carousel.
						 */
						.featured-content-inner > amp-carousel {
							position: relative;
						}
						body.slider amp-carousel > .amp-carousel-button {
							-webkit-font-smoothing: antialiased;
							background-color: black;
							background-image: none;
							border-radius: 0;
							border-color: #fff;
							border-style: solid;
							border-width: 2px 1px 0 0;
							box-sizing: border-box;
							cursor: pointer;
							display: inline-block;
							font: normal 16px/1 Genericons;
							height: 48px;
							left: auto;
							opacity: 1;
							text-align: center;
							text-decoration: inherit;
							top: auto;
							width: 50%;
							transform: none;
						}
						body.slider amp-carousel > .amp-carousel-button:focus {
							outline: white thin dotted;
						}
						body.slider amp-carousel > .amp-carousel-button:hover {
							background-color: #24890d;
							outline: none;
						}
						body.slider amp-carousel > .amp-carousel-button-prev:before {
							color: #fff;
							content: "\f430";
							font-size: 32px;
							line-height: 46px;
						}
						body.slider amp-carousel > .amp-carousel-button-next:before {
							color: #fff;
							content: "\f429";
							font-size: 32px;
							line-height: 46px;
						}
						.featured-content .post-thumbnail amp-img > img {
							object-fit: cover;
							object-position: top;
						}

						@media screen and (max-width: 672px) {
							.slider-control-paging {
								float: none;
								margin: 0;
							}
							.featured-content .post-thumbnail amp-img {
								height: 55.49132947vw;
							}
							.slider-control-paging li {
								display: inline-block;
								float: none;
							}
						}
						@media screen and (min-width: 673px) {
							body.slider amp-carousel > .amp-carousel-button {
								width: 48px;
								border: 0;
								bottom: 0;
							}
							body.slider amp-carousel > .amp-carousel-button-prev {
								right: 50px;
							}
							body.slider amp-carousel > .amp-carousel-button-next {
								right: 0;
							}
						}

					<?php endif; ?>
				</style>
				<?php
				$css = str_replace( [ '<style>', '</style>' ], '', ob_get_clean() );

				wp_add_inline_style( 'twentyfourteen-style', $css );
			},
			11
		);
	}

	/**
	 * Add amp-carousel for slider in Twenty Fourteen.
	 *
	 * @since 1.1
	 */
	public function add_twentyfourteen_slider_carousel() {
		if ( 'slider' !== get_theme_mod( 'featured_content_layout' ) ) {
			return;
		}

		$featured_content = $this->dom->getElementById( 'featured-content' );
		if ( ! $featured_content ) {
			return;
		}

		$featured_content_inner = $this->xpath->query( './div[ @class = "featured-content-inner" ]', $featured_content )->item( 0 );
		if ( ! $featured_content_inner ) {
			return;
		}

		$selected_slide_default  = 0;
		$selected_slide_state_id = 'twentyFourteenSelectedSlide';

		// Create the slider state.
		$amp_state = $this->dom->createElement( 'amp-state' );
		$amp_state->setAttribute( 'id', $selected_slide_state_id );
		$script = $this->dom->createElement( 'script' );
		$script->setAttribute( 'type', 'application/json' );
		$script->appendChild( $this->dom->createTextNode( wp_json_encode( $selected_slide_default ) ) );
		$amp_state->appendChild( $script );
		$featured_content->appendChild( $amp_state );

		// Create the carousel slider.
		$amp_carousel_desktop_id = 'twentyFourteenSliderDesktop';
		$amp_carousel_mobile_id  = 'twentyFourteenSliderMobile';
		$amp_carousel_attributes = [
			'layout' => 'responsive',
			'on'     => "slideChange:AMP.setState( { $selected_slide_state_id: event.index } )",
			'width'  => '100',
			'type'   => 'slides',
			'loop'   => '',
			AMP_DOM_Utils::AMP_BIND_DATA_ATTR_PREFIX . 'slide' => $selected_slide_state_id,
		];
		$amp_carousel_desktop    = AMP_DOM_Utils::create_node(
			$this->dom,
			'amp-carousel',
			array_merge(
				$amp_carousel_attributes,
				[
					'id'     => $amp_carousel_desktop_id,
					'media'  => '(min-width: 672px)',
					'height' => '55.49132947', // Value comes from <https://github.com/WordPress/wordpress-develop/blob/fc2a8f0e11316d066a686995b8578d82cd5546cf/src/wp-content/themes/twentyfourteen/style.css#L3024>.
				]
			)
		);
		$amp_carousel_mobile     = AMP_DOM_Utils::create_node(
			$this->dom,
			'amp-carousel',
			array_merge(
				$amp_carousel_attributes,
				[
					'id'     => $amp_carousel_mobile_id,
					'media'  => '(max-width: 672px)',
					'height' => '73',
				]
			)
		);

		while ( $featured_content_inner->firstChild ) {
			$node = $featured_content_inner->removeChild( $featured_content_inner->firstChild );
			$amp_carousel_desktop->appendChild( $node );
			$amp_carousel_mobile->appendChild( $node->cloneNode( true ) );
		}
		$featured_content_inner->appendChild( $amp_carousel_desktop );
		$featured_content_inner->appendChild( $amp_carousel_mobile );

		// Create the selector.
		$amp_selector = $this->dom->createElement( 'amp-selector' );
		$amp_selector->setAttribute( 'layout', 'container' );
		$slider_control_nav = $this->dom->createElement( 'ol' );
		$slider_control_nav->setAttribute( 'class', 'slider-control-nav slider-control-paging' );
		$count = $amp_carousel_desktop->getElementsByTagName( 'article' )->length;
		for ( $i = 0; $i < $count; $i++ ) {
			$li = $this->dom->createElement( 'li' );
			$a  = $this->dom->createElement( 'a' );
			if ( $selected_slide_default === $i ) {
				$li->setAttribute( 'selected', '' );
				$a->setAttribute( 'class', 'slider-active' );
			}
			$a->setAttribute( AMP_DOM_Utils::AMP_BIND_DATA_ATTR_PREFIX . 'class', "$selected_slide_state_id == $i ? 'slider-active' : ''" );
			$a->setAttribute( 'role', 'button' );
			$a->setAttribute( 'on', "tap:AMP.setState( { $selected_slide_state_id: $i } )" );
			$li->setAttribute( 'option', (string) $i );
			$a->appendChild( $this->dom->createTextNode( $i + 1 ) );
			$li->appendChild( $a );
			$slider_control_nav->appendChild( $li );
		}
		$amp_selector->appendChild( $slider_control_nav );
		$featured_content->appendChild( $amp_selector );
	}

	/**
	 * Use AMP-based solutions for toggling search bar in Twenty Fourteen.
	 *
	 * @link https://github.com/WordPress/wordpress-develop/blob/fc2a8f0e11316d066a686995b8578d82cd5546cf/src/wp-content/themes/twentyfourteen/js/functions.js#L69-L87
	 */
	public function add_twentyfourteen_search() {
		$search_toggle_div  = $this->xpath->query( '//div[ contains( @class, "search-toggle" ) ]' )->item( 0 );
		$search_toggle_link = $this->xpath->query( './a', $search_toggle_div )->item( 0 );
		$search_container   = $this->dom->getElementById( 'search-container' );
		if ( ! $search_toggle_div || ! $search_toggle_link || ! $search_container ) {
			return;
		}

		// Create the <amp-state> element that contains whether the search bar is shown.
		$amp_state       = $this->dom->createElement( 'amp-state' );
		$hidden_state_id = 'twentyfourteenSearchHidden';
		$hidden          = true;
		$amp_state->setAttribute( 'id', $hidden_state_id );
		$script = $this->dom->createElement( 'script' );
		$script->setAttribute( 'type', 'application/json' );
		$script->appendChild( $this->dom->createTextNode( wp_json_encode( $hidden ) ) );
		$amp_state->appendChild( $script );
		$search_container->appendChild( $amp_state );

		// Update AMP state to show the search bar and focus on search input when tapping on the search button.
		$search_input_id = 'twentyfourteen_search_input';
		$search_input_el = $this->xpath->query( './/input[ @name = "s" ]', $search_container )->item( 0 );
		$search_toggle_link->removeAttribute( 'href' );
		$on = "tap:AMP.setState( { $hidden_state_id: ! $hidden_state_id } )";
		if ( $search_input_el ) {
			$search_input_el->setAttribute( 'id', $search_input_id );
			$on .= ",$search_input_id.focus()";
		}
		$search_toggle_link->setAttribute( 'on', $on );
		$search_toggle_link->setAttribute( 'tabindex', '0' );
		$search_toggle_link->setAttribute( 'role', 'button' );

		// Set visibility and aria-expanded based of the link based on whether the search bar is expanded.
		$search_toggle_link->setAttribute( 'aria-expanded', wp_json_encode( $hidden ) );
		$search_toggle_link->setAttribute( AMP_DOM_Utils::AMP_BIND_DATA_ATTR_PREFIX . 'aria-expanded', "$hidden_state_id ? 'false' : 'true'" );
		$search_toggle_div->setAttribute( AMP_DOM_Utils::AMP_BIND_DATA_ATTR_PREFIX . 'class', "$hidden_state_id ? 'search-toggle' : 'search-toggle active'" );
		$search_container->setAttribute( AMP_DOM_Utils::AMP_BIND_DATA_ATTR_PREFIX . 'class', "$hidden_state_id ? 'search-box-wrapper hide' : 'search-box-wrapper'" );
	}

	/**
	 * Wrap a modal node tree in an <amp-lightbox> element.
	 *
	 * @param array $args {
	 *     Associative array of arguments.
	 *
	 *     @type string   $modal_id            ID to use for the modal and its associated buttons.
	 *     @type string   $modal_content_xpath XPath to query the contents of the modal.
	 *     @type string[] $open_button_xpath   Array of XPaths to query the buttons that open the modal.
	 *     @type string[] $close_button_xpath  Array of XPaths to query the buttons that close the modal. These should be contained within the modal.
	 *     @type string   $animate_in          Optional. What animation to use for showing the modal. Valid options are: 'fade-in', 'fly-in-bottom', 'fly-in-top'. Defaults to 'fade-in'.
	 *     @type bool     $scrollable          Optional. Whether the inner content of the modal should be scrollable. Defaults to true.
	 * }
	 */
	public function wrap_modal_in_lightbox( $args = [] ) {
		if ( ! isset( $args['modal_id'], $args['modal_content_xpath'], $args['open_button_xpath'], $args['close_button_xpath'] ) ) {
			return;
		}

		$modal_id           = $args['modal_id'];
		$modal_content_node = $this->xpath->query( $args['modal_content_xpath'] )->item( 0 );

		if ( ! is_string( $modal_id ) || ! $modal_content_node instanceof DOMElement ) {
			return;
		}

		$body_id = AMP_DOM_Utils::get_element_id( $this->get_body_node(), 'body' );

		$open_xpaths  = isset( $args['open_button_xpath'] ) ? $args['open_button_xpath'] : [];
		$close_xpaths = isset( $args['close_button_xpath'] ) ? $args['close_button_xpath'] : [];

		$modal_actions = [
			"{$modal_id}.open"  => $open_xpaths,
			// Although we add the 'show-modal' class here, we don't remove it again, as it will
			// _first_ remove the correct positioning and only _then_ start the fade-out animation.
			// See: https://youtu.be/aooq-liRtMs .
			"{$modal_id}.toggleClass(class=show-modal,force=true)" => $open_xpaths,
			"{$body_id}.toggleClass(class=showing-modal,force=true)" => $open_xpaths,
			"{$modal_id}.close" => $close_xpaths,
			"{$body_id}.toggleClass(class=showing-modal,force=false)" => $close_xpaths,
		];

		// As we have the toggle targets, we need to go backwards from their and find all
		// nodes that are meant to toggle these targets.
		// The triple loop below is generally a double loop (modals x toggles), however
		// we need the third loop as we cannot guarantee that each xpath will only ever
		// retrieve a single result.
		foreach ( $modal_actions as $modal_action => $toggle_xpaths ) {
			foreach ( $toggle_xpaths as $toggle_xpath ) {
				foreach ( $this->xpath->query( $toggle_xpath ) as $toggle_node ) {
					if ( $toggle_node instanceof DOMElement ) {
						AMP_DOM_Utils::add_amp_action( $toggle_node, 'tap', $modal_action );
					}
				}
			}
		}

		// Create an <amp-lightbox> element that will contain the modal.
		$amp_lightbox = $this->dom->createElement( 'amp-lightbox' );
		$amp_lightbox->setAttribute( 'id', $modal_id );
		$amp_lightbox->setAttribute( 'layout', 'nodisplay' );
		$amp_lightbox->setAttribute( 'animate-in', isset( $args['animate_in'] ) ? $args['animate_in'] : 'fade-in' );
		$amp_lightbox->setAttribute( 'scrollable', isset( $args['scrollable'] ) ? $args['scrollable'] : true );
		$amp_lightbox->setAttribute( 'role', $this->guess_modal_role( $modal_content_node ) );
		// Setting tabindex to -1 (not reachable) as keyboard focus is handled through toggles.
		$amp_lightbox->setAttribute( 'tabindex', -1 );

		$parent_node = $modal_content_node->parentNode;
		$parent_node->replaceChild( $amp_lightbox, $modal_content_node );

		$strip_wrapper_levels = isset( $args['strip_wrapper_levels'] ) ? $args['strip_wrapper_levels'] : 0;

		while ( $strip_wrapper_levels > 0 ) {
			$children = [];
			foreach ( $modal_content_node->childNodes as $child_node ) {
				if ( $child_node instanceof DOMElement && ! $child_node instanceof DOMComment ) {
					$children[] = $child_node;
				}
			}

			if ( count( $children ) > 1 ) {
				break;
			}

			// Add class(es) and action(s) of removed wrapper to lightbox to avoid breaking CSS selectors.
			AMP_DOM_Utils::copy_attributes( [ 'class', 'on', 'data-toggle-target' ], $modal_content_node, $amp_lightbox );

			$modal_content_node = $modal_content_node->removeChild( $children[0] );

			$strip_wrapper_levels--;
		}

		$amp_lightbox->appendChild( $modal_content_node );
	}

	/**
	 * Add generic modal interactivity compat for the Twentytwenty theme.
	 *
	 * Modals implemented in JS will be transformed into <amp-lightbox> equivalents,
	 * with the tap actions being attached to their associated toggles.
	 */
	public function add_twentytwenty_modals() {
		$modals = $this->xpath->query( "//*[ @class and contains( concat( ' ', normalize-space( @class ), ' ' ), ' cover-modal ' ) ]" );

		if ( false === $modals || 0 === $modals->count() ) {
			return;
		}

		foreach ( $modals as $modal ) {
			/**
			 * Modal element to transform.
			 *
			 * @var DOMElement $modal
			 */

			if ( ! $modal->hasAttribute( 'data-modal-target-string' ) ) {
				return;
			}

			$modal_target = $modal->getAttribute( 'data-modal-target-string' );
			$toggles      = $this->xpath->query( "//*[ @data-toggle-target = '{$modal_target}' ]" );

			$open_button_xpaths  = [];
			$close_button_xpaths = [];
			foreach ( $toggles as $toggle ) {
				/**
				 * Toggle element to transform.
				 *
				 * @var $toggle DOMElement
				 */

				$within_modal = false;
				$parent       = $toggle->parentNode;
				while ( $parent ) {
					if ( $parent === $modal ) {
						$within_modal = true;
						break;
					}
					$parent = $parent->parentNode;
				}

				if ( $within_modal ) {
					$close_button_xpaths[] = $toggle->getNodePath();
				} else {
					$open_button_xpaths[] = $toggle->getNodePath();
				}
			}

			$modal_id = AMP_DOM_Utils::get_element_id( $modal );

			// Add the lightbox itself as a close button xpath as well.
			// With twentytwenty compat, the lightbox fills the entire screen, and only an inner wrapper will contain
			// the actionable elements in the modal. Therefore, the lightbox represents the "background".
			$close_button_xpaths[] = "//*[ @id = '{$modal_id}' ]";
			$modal->setAttribute( 'data-toggle-target', "#{$modal_id}" );

			$this->wrap_modal_in_lightbox(
				[
					'modal_id'             => $modal_id,
					'modal_content_xpath'  => $modal->getNodePath(),
					'open_button_xpath'    => $open_button_xpaths,
					'close_button_xpath'   => $close_button_xpaths,
					'strip_wrapper_levels' => 1,
				]
			);
		}
	}

	/**
	 * Add generic toggle interactivity compat for the Twentytwenty theme.
	 *
	 * Toggles implemented in JS will be transformed into <amp-bind> equivalents,
	 * with <amp-state> components storing the CSS classes to set.
	 */
	public function add_twentytwenty_toggles() {
		$toggles = $this->xpath->query( '//*[ @data-toggle-target ]' );
		$body_id = AMP_DOM_Utils::get_element_id( $this->get_body_node(), 'body' );

		if ( false === $toggles || 0 === $toggles->count() ) {
			return;
		}

		foreach ( $toggles as $toggle ) {
			/**
			 * Toggle element to transform.
			 *
			 * @var $toggle DOMElement
			 */

			$toggle_target = $toggle->getAttribute( 'data-toggle-target' );
			$toggle_id     = AMP_DOM_Utils::get_element_id( $toggle );

			if ( 'next' === $toggle_target ) {
				$target_node = $toggle->nextSibling;
			} else {
				$target_xpath = $this->xpath_from_css_selector( $toggle_target );
				if ( null === $target_xpath ) {
					continue;
				}

				$target_nodes = $this->xpath->query( $target_xpath, $toggle );
				if ( false === $target_nodes || 0 === count( $target_nodes ) ) {
					continue;
				}
				$target_node = $target_nodes->item( 0 );
			}

			if ( ! $target_node ) {
				continue;
			}

			// Get the class to toggle, if specified.
			$toggle_class = $toggle->hasAttribute( 'data-class-to-toggle' ) ? $toggle->getAttribute( 'data-class-to-toggle' ) : 'active';

			$is_sub_menu     = AMP_DOM_Utils::has_class( $target_node, 'sub-menu' );
			$new_target_node = $is_sub_menu ? $this->get_closest_submenu( $toggle ) : $target_node;
			$new_target_id   = AMP_DOM_Utils::get_element_id( $new_target_node );

			$state_string = str_replace( '-', '_', $new_target_id );

			// Toggle the target of the clicked toggle.
			AMP_DOM_Utils::add_amp_action( $toggle, 'tap', "{$new_target_id}.toggleClass(class='{$toggle_class}')" );
			// Set the central state of the toggle's target.
			AMP_DOM_Utils::add_amp_action( $toggle, 'tap', "AMP.setState({{$state_string}: !{$state_string}})" );
			// Adapt the aria-expanded attribute according to the central state.
			$toggle->setAttribute( 'data-amp-bind-aria-expanded', "{$state_string} ? 'true' : 'false'" );

			// If the toggle target is 'next' ir a sub-menu, only give the clicked toggle the active class.
			if ( 'next' === $toggle_target || AMP_DOM_Utils::has_class( $target_node, 'sub-menu' ) ) {
				AMP_DOM_Utils::add_amp_action( $toggle, 'tap', "{$toggle_id}.toggleClass(class='active')" );
			} else {
				// If not, toggle all toggles with this toggle target.
				$target_toggles = $this->xpath->query( "//*[ @data-toggle-target = '{$toggle_target}' ]" );
				foreach ( $target_toggles as $target_toggle ) {
					if ( AMP_DOM_Utils::has_class( $target_toggle, 'close-nav-toggle' ) ) {
						// Skip adding the 'active' class on the "Close" button in the primary nav menu.
						continue;
					}
					$target_toggle_id = AMP_DOM_Utils::get_element_id( $target_toggle );
					AMP_DOM_Utils::add_amp_action( $toggle, 'tap', "{$target_toggle_id}.toggleClass(class='active')" );
				}
			}

			// Toggle body class.
			if ( $toggle->hasAttribute( 'data-toggle-body-class' ) ) {
				$body_class = $toggle->getAttribute( 'data-toggle-body-class' );
				AMP_DOM_Utils::add_amp_action( $toggle, 'tap', "{$body_id}.toggleClass(class='{$body_class}')" );
			}

			if ( $toggle->hasAttribute( 'data-set-focus' ) ) {
				$focus_selector = $toggle->getAttribute( 'data-set-focus' );

				if ( ! empty( $focus_selector ) ) {
					$focus_xpath   = $this->xpath_from_css_selector( $focus_selector );
					$focus_element = $this->xpath->query( $focus_xpath )->item( 0 );

					if ( $focus_element instanceof DOMElement ) {
						$focus_element_id = AMP_DOM_Utils::get_element_id( $focus_element );
						AMP_DOM_Utils::add_amp_action( $toggle, 'tap', "{$focus_element_id}.focus" );
					}
				}
			}
		}
	}

	/**
	 * Get the closest sub-menu within a menu item.
	 *
	 * @param DOMElement $element Element to get the closest sub-menu of.
	 * @return DOMElement Requested sub-menu element, or the starting element
	 *                    if none found.
	 */
	protected function get_closest_submenu( DOMElement $element ) {
		$menu_item = $element;

		while ( ! AMP_DOM_Utils::has_class( $menu_item, 'menu-item' ) ) {
			$menu_item = $menu_item->parentNode;
			if ( ! $menu_item ) {
				return $element;
			}
		}

		$sub_menu = $this->xpath->query( ".//*[ @class and contains( concat( ' ', normalize-space( @class ), ' ' ), ' sub-menu ' ) ]", $menu_item )->item( 0 );

		if ( ! $sub_menu instanceof DOMElement ) {
			return $element;
		}

		return $sub_menu;
	}

	/**
	 * Automatically open the submenus related to the current page in the menu modal.
	 */
	public function add_twentytwenty_current_page_awareness() {
		$page_ancestors = $this->xpath->query( "//li[ @class and contains( concat( ' ', normalize-space( @class ), ' ' ), ' current_page_ancestor ' ) ]" );
		foreach ( $page_ancestors as $page_ancestor ) {
			$toggle   = $this->xpath->query( "./div/button[ @class and contains( concat( ' ', normalize-space( @class ), ' ' ), ' sub-menu-toggle ' ) ]", $page_ancestor )->item( 0 );
			$children = $this->xpath->query( "./ul[ @class and contains( concat( ' ', normalize-space( @class ), ' ' ), ' children ' ) ]", $page_ancestor )->item( 0 );
			foreach ( [ $toggle, $children ] as $element ) {
				if ( ! $element instanceof DOMElement ) {
					continue;
				}

				$classes   = $element->hasAttribute( 'class' ) ? explode( ' ', $element->getAttribute( 'class' ) ) : [];
				$classes[] = 'active';
				$element->setAttribute( 'class', implode( ' ', array_unique( $classes ) ) );
			}
		}
	}

	/**
	 * Provides a "best guess" as to what XPath would mirror a given CSS
	 * selector.
	 *
	 * This is a very simplistic conversion and will only work for very basic
	 * CSS selectors.
	 *
	 * @param string $css_selector CSS selector to convert.
	 * @return string|null XPath that closely mirrors the provided CSS selector,
	 *                             or null if an error occurred.
	 * @since 1.4.0
	 */
	protected function xpath_from_css_selector( $css_selector ) {
		// Start with basic clean-up.
		$css_selector = trim( $css_selector );
		$css_selector = preg_replace( '/\s+/', ' ', $css_selector );

		$xpath             = '';
		$direct_descendant = false;
		$token             = strtok( $css_selector, ' ' );

		while ( false !== $token ) {
			$matches = [];

			// Direct descendant.
			if ( preg_match( '/^>$/', $token, $matches ) ) {
				$direct_descendant = true;
				$token             = strtok( ' ' );
				continue;
			}

			// Single ID.
			if ( preg_match( '/^#(?<id>[a-zA-Z0-9-_]*)$/', $token, $matches ) ) {
				$descendant        = $direct_descendant ? '/' : '//';
				$xpath            .= "{$descendant}*[ @id = '{$matches['id']}' ]";
				$direct_descendant = false;
				$token             = strtok( ' ' );
				continue;
			}

			// Single class.
			if ( preg_match( '/^\.(?<class>[a-zA-Z0-9-_]*)$/', $token, $matches ) ) {
				$descendant        = $direct_descendant ? '/' : '//';
				$xpath            .= "{$descendant}*[ @class and contains( concat( ' ', normalize-space( @class ), ' ' ), ' {$matches['class']} ' ) ]";
				$direct_descendant = false;
				$token             = strtok( ' ' );
				continue;
			}

			// Element.
			if ( preg_match( '/^(?<element>[^.][a-zA-Z0-9-_]*)$/', $token, $matches ) ) {
				$descendant        = $direct_descendant ? '/' : '//';
				$xpath            .= "{$descendant}{$matches['element']}";
				$direct_descendant = false;
				$token             = strtok( ' ' );
				continue;
			}

			$token = strtok( ' ' );
		}

		return $xpath;
	}

	/**
	 * Try to guess the role of a modal based on its classes.
	 *
	 * @param DOMElement $modal Modal to guess the role for.
	 * @return string Role that was guessed.
	 */
	protected function guess_modal_role( DOMElement $modal ) {
		// No classes to base our guess on, so keep it generic.
		if ( ! $modal->hasAttribute( 'class' ) ) {
			return 'dialog';
		}

		$classes = $modal->getAttribute( 'class' );

		foreach ( [ 'navigation', 'menu', 'search', 'alert', 'figure', 'form', 'img', 'toolbar', 'tooltip' ] as $role ) {
			if ( false !== strpos( $classes, $role ) ) {
				return $role;
			}
		}

		// None of the roles we are looking for match any of the classes.
		return 'dialog';
	}
}
