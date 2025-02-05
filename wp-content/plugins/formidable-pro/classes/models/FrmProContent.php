<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * Class FrmProContent
 */
class FrmProContent {

	public static function replace_shortcodes( $content, $entry, $shortcodes, $display = false, $show = 'one', $odd = '', $args = array() ) {
		$args['odd']  = $odd;
		$args['show'] = $show;

		// track the count of shortcoe types that are replaced in "replace_single_shortcode"
		$shortcode_types_count = array(
			'date_field' => 0,
		);
		foreach ( $shortcodes[0] as $short_key => $tag ) {
			$previous_content = $content;
			self::replace_single_shortcode( $shortcodes, $short_key, $tag, $entry, $display, $args, $content, $shortcode_types_count );

			$has_run = ( $content !== $previous_content );
			if ( $has_run ) {
				$shortcodes[0][ $short_key ] = '';
			}
			unset( $previous_content );
		}

		if ( ! empty( $shortcodes[0] ) ) {
			$content = FrmFieldsHelper::replace_content_shortcodes( $content, $entry, $shortcodes );
		}

		return $content;
	}

	/**
	 * @param array  $shortcodes
	 * @param int    $short_key
	 * @param string $tag
	 * @param array  $args
	 * @param string $content
	 * @return void
	 */
	public static function replace_single_shortcode( $shortcodes, $short_key, $tag, $entry, $display, $args, &$content, &$shortcode_types_count = array() ) {
		$conditional = preg_match( '/^\[if/s', $shortcodes[0][ $short_key ] ) ? true : false;
		$foreach     = preg_match( '/^\[foreach/s', $shortcodes[0][ $short_key ] ) ? true : false;
		$atts        = FrmShortcodeHelper::get_shortcode_attribute_array( $shortcodes[3][ $short_key ] );

		$tag = FrmShortcodeHelper::get_shortcode_tag( $shortcodes, $short_key, compact( 'conditional', 'foreach' ) );

		self::maybe_replace_dash( $tag );

		$no_field_id = array( 'key', 'ip', 'siteurl', 'sitename', 'admin_email' );
		if ( in_array( $tag, $no_field_id, true ) ) {
			// don't check for a field for default values not covered below
			return;
		}

		$tags = array(
			'created_at',
			'created_by',
			'deletelink',
			'detaillink',
			'editlink',
			'entry_count',
			'entry_position',
			'evenodd',
			'event_date',
			'end_event_date',
			'get',
			'id',
			'is_draft',
			'parent_id',
			'post_id',
			'updated_at',
			'updated_by',
		);

		$class = array();
		/**
		 * Allows modification of the class mapping for shortcode processing.
		 *
		 * @since 6.16.3
		 *
		 * @param array $class Array of tag => class_name pairs.
		 * @return array
		 */
		$class = apply_filters( 'frm_single_shortcode_processing_class', $class );

		if ( in_array( $tag, $tags, true ) ) {
			$args['entry']       = $entry;
			$args['tag']         = $tag;
			$args['conditional'] = $conditional;
			$function            = 'do_shortcode_' . $tag;
			$class               = ! empty( $class[ $tag ] ) ? $class[ $tag ] : 'FrmProContent';

			/** @var class-string $class */
			if ( class_exists( $class ) && is_callable( array( $class, $function ) ) ) {
				$class::$function( $content, $atts, $shortcodes, $short_key, $args, $display );
			}

			return;
		}

		$field = FrmField::getOne( $tag );
		if ( ! $field ) {
			return;
		}

		$is_parent_value = $field->form_id != $entry->form_id && isset( $args['foreach_loop'] ) && $args['foreach_loop'];
		if ( $is_parent_value ) {
			return;
		}

		if ( ! $foreach && ! $conditional && isset( $atts['show'] ) && ( $atts['show'] === 'field_label' || $atts['show'] === 'description' ) ) {
			// get the field label or description and return before any other checking
			$field        = apply_filters( 'frm_field_object_for_shortcode', $field );
			$replace_with = $atts['show'] === 'field_label' ? $field->name : $field->description;
			$content      = str_replace( $shortcodes[0][ $short_key ], $replace_with, $content );
			return;
		}

		$sep = isset( $atts['sep'] ) ? $atts['sep'] : ', ';

		if ( $field->form_id == $entry->form_id ) {
			$replace_with = FrmProEntryMetaHelper::get_post_or_meta_value( $entry, $field, $atts );
			// track number of date field types used as shortcodes
			if ( 'date' === $field->type ) {
				++$shortcode_types_count['date_field'];
			}
		} elseif ( ! empty( $entry->parent_entry ) && intval( $field->form_id ) === intval( $entry->parent_entry->form_id ) ) {
			// If current entry is a repeater entry, and we want to access the parent entry meta.
			$replace_with = FrmProEntryMetaHelper::get_post_or_meta_value( $entry->parent_entry, $field, $atts );
		} else {
			if ( ! empty( $entry->parent_entry ) ) {
				// If current entry is a repeater entry, and we want to access another repeater entry meta.
				$parent_entry = $entry->parent_entry;
			} else {
				// If current entry is a parent entry, and we want to access child entry meta.
				$parent_entry = $entry;
			}

			// get entry ids linked through repeat field or embedded form
			$child_entries = FrmProEntry::get_sub_entries( $parent_entry->id, true );
			$replace_with  = FrmProEntryMetaHelper::get_sub_meta_values( $child_entries, $field, $atts );
			$replace_with  = FrmAppHelper::array_flatten( $replace_with );
		}

		if ( $field->type === 'address' && ! isset( $atts['blank'] ) ) {
			$atts['blank'] = 1;
		}

		$atts['entry_id']  = $entry->id;
		$atts['entry_key'] = $entry->item_key;
		$atts['post_id']   = $entry->post_id;

		self::maybe_get_show_from_array( $replace_with, $atts );
		self::maybe_add_show_value_option_to_shortcode( $atts, $field, $conditional );

		/**
		 * @param string   $replace_with
		 * @param string   $tag
		 * @param array    $atts
		 * @param stdClass $field
		 */
		$replace_with       = apply_filters( 'frmpro_fields_replace_shortcodes', $replace_with, $tag, $atts, $field, compact( 'args', 'shortcode_types_count' ) );
		$value_was_imploded = false;

		if ( isset( $atts['show'] ) && $atts['show'] === 'count' ) {
			$replace_with = is_array( $replace_with ) ? count( $replace_with ) : ! empty( $replace_with );
		} elseif ( is_array( $replace_with ) && ! $foreach ) {
			$keep_array = apply_filters( 'frm_keep_value_array', false, compact( 'field', 'replace_with' ) );
			$keep_array = apply_filters( 'frm_keep_' . $field->type . '_value_array', $keep_array, compact( 'field', 'replace_with' ) );

			if ( ! $keep_array && $field->type !== 'file' ) {
				$replace_with = FrmAppHelper::array_flatten( $replace_with );
				$replace_with = array_filter( $replace_with, array( 'FrmProContent', 'is_not_empty' ) );

				if ( ! isset( $atts['sep'] ) && FrmProImages::has_images_options_in_html( $replace_with ) ) {
					$sep = '';
				}

				$value_was_imploded = true;
				$replace_with       = implode( $sep, $replace_with );
			} elseif ( ! $replace_with ) {
				$replace_with = '';
			}
		}

		if ( $foreach ) {
			$atts['short_key'] = $shortcodes[0][ $short_key ];
			$args['display']   = $display;
			self::check_conditional_shortcode( $content, $replace_with, $atts, $tag, 'foreach', $args );
		} elseif ( $conditional ) {
			$atts['short_key'] = $shortcodes[0][ $short_key ];
			self::check_conditional_shortcode( $content, $replace_with, $atts, $tag, 'if', array( 'field' => $field ) );
		} else {
			if ( empty( $replace_with ) && $replace_with != '0' ) {
				if ( isset( $atts['default'] ) ) {
					$replace_with = $atts['default'];
				} else {
					$replace_with = '';
				}
			} else {
				if ( $value_was_imploded && ! empty( $sep ) ) {
					$allow_separator_tags_filter = self::get_allow_separator_tags_filter( $sep );
					if ( $allow_separator_tags_filter ) {
						add_filter( 'frm_allowed_form_input_html', $allow_separator_tags_filter );
					}
				}

				$display_atts = $atts;
				if ( ! isset( $display_atts['entry'] ) ) {
					/**
					 * Pass the entry so FrmFieldType::should_strip_most_html_before_preparing_display_value
					 * can avoid the call to FrmEntry::getOne, saving on db queries.
					 */
					$display_atts['entry'] = $entry;
				}

				$replace_with = FrmFieldsHelper::get_display_value( $replace_with, $field, $display_atts );
				if ( ! empty( $allow_separator_tags_filter ) ) {
					remove_filter( 'frm_allowed_form_input_html', $allow_separator_tags_filter );
				}
			}

			self::trigger_shortcode_atts( $atts, $display, $args, $replace_with );
			if ( is_callable( 'FrmFieldsHelper::sanitize_embedded_shortcodes' ) ) {
				FrmFieldsHelper::sanitize_embedded_shortcodes( compact( 'entry' ), $replace_with );
			}

			if ( is_null( $replace_with ) ) {
				$replace_with = '';
			}

			$content = str_replace( $shortcodes[0][ $short_key ], $replace_with, $content );
		}
	}

	/**
	 * When get_display_value is called, a lot of HTML is stripped from entries submitted by users
	 * who cannot edit entries. Since the implode happens earlier in self::replace_single_shortcode
	 * we need to allow for whatever tags amy be in the separator.
	 *
	 * @since 6.8
	 *
	 * @param string $sep Separator used to implode array values.
	 * @return Closure|false False when there are no tags to add to the filter.
	 */
	private static function get_allow_separator_tags_filter( $sep ) {
		$tags = self::get_tags_used_in_string( $sep );
		if ( ! $tags ) {
			// If there are no tags there is no need to add this filter.
			return false;
		}

		return function ( $allowed_html ) use ( $tags ) {
			foreach ( $tags as $tag ) {
				if ( ! isset( $allowed_html[ $tag ] ) ) {
					$allowed_html[ $tag ] = array();
				}
			}
			return $allowed_html;
		};
	}

	/**
	 * Check for HTML tags used in a string.
	 *
	 * @since 6.8
	 *
	 * @param string $string
	 * @return array
	 */
	private static function get_tags_used_in_string( $string ) {
		$tag_pattern = '/<\s*([a-zA-Z]+)[^>]*>|<\/\s*([a-zA-Z]+)\s*>/';
		preg_match_all( $tag_pattern, $string, $matches );

		$tags        = array_filter( array_merge( $matches[1], $matches[2] ) );
		$unique_tags = array_unique( $tags );

		return array_values( $unique_tags );
	}

	/**
	 * Maybe insert the show="value" option to an [if x] shortcode if the field has options
	 * that are displayed as images.
	 * This allows you to match [if x equals="Option 1"] even though the output/default compare value is HTML.
	 *
	 * @since 6.7
	 *
	 * @param array    $atts
	 * @param stdClass $field
	 * @param bool     $conditional
	 * @return void
	 */
	private static function maybe_add_show_value_option_to_shortcode( &$atts, $field, $conditional ) {
		if ( ! $conditional ) {
			// Only apply this to [if x] shortcodes. A regular [x] shortcode should output an image.
			return;
		}

		if ( empty( $field->options ) || empty( $field->field_options['image_options'] ) ) {
			// This field does not have image options, so exit early.
			return;
		}

		if ( isset( $atts['show'] ) || ! isset( $atts['equals'] ) ) {
			// Do not overwrite any show attributes if one is already set, and only do this for equals conditions.
			return;
		}

		if ( false !== strpos( $atts['equals'], 'frm_image_option_container' ) && false !== strpos( $atts['equals'], '<img' ) ) {
			// If the equals conditions looks like it is trying to match the HTML output leave it alone.
			return;
		}

		$atts['show'] = 'value';
	}

	/**
	 * Accept some tags with dash or underscore
	 *
	 * @since 3.01
	 *
	 * @param string $tag
	 * @return void
	 */
	private static function maybe_replace_dash( &$tag ) {
		if ( strpos( $tag, '-' ) ) {
			$switch_tags = array(
				'post-id',
				'created-at',
				'updated-at',
				'created-by',
				'updated-by',
				'parent-id',
				'is-draft',
			);

			if ( in_array( $tag, $switch_tags ) ) {
				$tag = str_replace( '-', '_', $tag );
			}
		}
	}

	/**
	 * @since 3.0.04
	 */
	public static function is_not_empty( $val ) {
		return $val !== '';
	}

	/**
	 * Filter out entry_number shortcode when we have the entry position in the view
	 *
	 * @since 4.03.01
	 *
	 * @param array  $entry_args
	 * @param array  $args
	 * @param string $content
	 * @return void
	 */
	public static function replace_entry_position_shortcode( $entry_args, $args, &$content ) {
		preg_match_all( "/\[(if )?(entry_position)\b(.*?)(?:(\/))?\](?:(.+?)\[\/\2\])?/s", $content, $shortcodes, PREG_PATTERN_ORDER );
		foreach ( $shortcodes[0] as $short_key => $tag ) {
			self::replace_single_shortcode( $shortcodes, $short_key, $tag, $entry_args['entry'], $entry_args['view'], $args, $content );
		}
	}

	/**
	 * Replace the calendar date shortcode with the event date.
	 *
	 * @param string $content
	 * @param string $date
	 * @return string
	 */
	public static function replace_calendar_date_shortcode( $content, $date ) {
		preg_match_all( "/\[(calendar_date|calendar_end_date)\b(.*?)(?:(\/))?\]/s", $content, $matches, PREG_PATTERN_ORDER );
		if ( empty( $matches[0] ) ) {
			return $content;
		}

		foreach ( $matches[0] as $short_key => $tag ) {
			$atts = FrmShortcodeHelper::get_shortcode_attribute_array( $matches[2][ $short_key ] );
			self::do_shortcode_event_date( $content, $atts, $matches, $short_key, array( 'event_date' => $date ) );
			if ( is_callable( 'FrmViewsCalendarHelper::do_shortcode_end_event_date' ) ) {
				FrmViewsCalendarHelper::do_shortcode_end_event_date( $content, $atts, $matches, $short_key, array( 'event_date' => $date ) );
			}
		}
		return $content;
	}

	/**
	 * Replace the calendar date shortcode with the event date.
	 * Function used to render the shortcode for event date in FrmProContent::replace_single_shortcode
	 *
	 * @param string $content
	 * @param array  $atts
	 * @param array  $shortcodes
	 * @param int    $short_key
	 * @param array  $args Passed in FrmProContent::replace_single_shortcode when called.
	 *
	 * @return void
	 */
	public static function do_shortcode_event_date( &$content, $atts, $shortcodes, $short_key, $args ) {
		$event_date_get_param = FrmAppHelper::get_param( 'frmev-start', '', 'get', 'sanitize_text_field' ); // Modern Calendar.
		$format               = isset( $atts['format'] ) ? $atts['format'] : get_option( 'date_format' );
		$event_date           = isset( $args['event_date'] ) ? $args['event_date'] : $event_date_get_param; // Check for $args['event_date'] first to support Legacy Calendar.

		if ( ! empty( $event_date ) ) {
			$event_date = FrmProFieldsHelper::get_date( $event_date, $format );
		}
		$content = str_replace( $shortcodes[0][ $short_key ], $event_date, $content );
	}

	public static function do_shortcode_entry_count( &$content, $atts, $shortcodes, $short_key, $args ) {
		$content = str_replace( $shortcodes[0][ $short_key ], ( isset( $args['record_count'] ) ? $args['record_count'] : '' ), $content );
	}

	public static function do_shortcode_detaillink( &$content, $atts, $shortcodes, $short_key, $args, $display ) {
		if ( $display ) {
			$detail_link = self::get_detail_link( $args, $display );
			$content     = str_replace(
				'<a href="[detaillink]"',
				'<a href="[detaillink]" class="frm-detail-link"',
				$content
			);
			$content     = str_replace( $shortcodes[0][ $short_key ], $detail_link, $content );
		}
	}

	private static function get_detail_link( $args, $display ) {
		if ( isset( $args['entry_key'] ) ) {
			$entry = $args;
		} else {
			$entry              = (array) $args['entry'];
			$entry['entry_id']  = $entry['id'];
			$entry['entry_key'] = $entry['item_key'];
		}

		if ( $entry['post_id'] ) {
			$detail_link = get_permalink( $entry['post_id'] );
		} else {
			$param_value = $display->frm_type === 'id' ? $entry['entry_id'] : $entry['entry_key'];
			$param       = ! empty( $display->frm_param ) ? $display->frm_param : 'entry';
			$detail_link = self::get_pretty_url( compact( 'param', 'param_value' ) );
		}

		return apply_filters( 'frmpro_detaillink_shortcode', $detail_link, $args );
	}

	/**
	 * Make the view urls pretty
	 *
	 * @param array $atts
	 * @return string
	 */
	public static function get_pretty_url( $atts ) {
		global $post;
		$base_url = untrailingslashit( $post ? get_permalink( $post->ID ) : FrmAppHelper::get_server_value( 'REQUEST_URI' ) );
		if ( ! is_front_page() && self::rewriting_on() && strpos( $base_url, '?' ) === false ) {
			$url = $base_url . '/' . $atts['param'] . '/' . $atts['param_value'];
		} else {
			$url = esc_url_raw( add_query_arg( $atts['param'], $atts['param_value'], $base_url ) );
		}

		return $url;
	}

	private static function rewriting_on() {
		$permalink_structure = get_option( 'permalink_structure' );
		return ! empty( $permalink_structure );
	}

	public static function add_rewrite_endpoint() {
		$rewrite_params = self::get_rewrite_params();
		if ( ! empty( $rewrite_params ) ) {
			foreach ( $rewrite_params as $param ) {
				add_rewrite_endpoint( $param, EP_PERMALINK | EP_PAGES );
			}
			add_action( 'request', 'FrmProContent::fix_home_page_query' );
		}
	}


	/**
	 * This is a workaround for a bug in WordPress Core
	 * https://core.trac.wordpress.org/ticket/23867
	 *
	 * @since 2.2.10
	 */
	public static function fix_home_page_query( $query ) {
		$rewrite_params  = self::get_rewrite_params();
		$included_params = array_intersect( $rewrite_params, array_keys( $query ) );
		if ( ! empty( $included_params ) ) {
			foreach ( $included_params as $key ) {
				$_GET[ $key ] = $query[ $key ];
				unset( $query[ $key ] );
			}
		}

		return $query;
	}

	/*
	 * Get the detail link parameter names from every view
	 * @since 2.2.8
	 */
	private static function get_rewrite_params() {
		global $wpdb;
		$params = FrmDb::get_col( $wpdb->postmeta, array( 'meta_key' => 'frm_param' ), 'meta_value' );
		$params = self::remove_reserved_words( $params );
		return array_filter( array_unique( $params ) );
	}

	/**
	 * Removes reserved words from a list of params.
	 *
	 * @param array $params A list of params.
	 *
	 * @return array The list of params with reserved words removed.
	 */
	private static function remove_reserved_words( $params ) {
		if ( ! is_callable( 'FrmFormsHelper::reserved_words' ) ) {
			return $params;
		}

		$reserved_words = FrmFormsHelper::reserved_words();

		return array_diff( $params, $reserved_words );
	}

	/**
	 * @param string $content
	 * @param array  $atts
	 * @param array  $shortcodes
	 * @param int    $short_key
	 * @param array  $args
	 * @return void
	 */
	public static function do_shortcode_editlink( &$content, $atts, $shortcodes, $short_key, $args ) {
		global $post;

		$replace_with = '';
		$link_text    = isset( $atts['label'] ) ? $atts['label'] : false;
		if ( ! $link_text ) {
			$link_text = isset( $atts['link_text'] ) ? $atts['link_text'] : __( 'Edit' );
		}

		$class   = isset( $atts['class'] ) ? $atts['class'] : '';
		$page_id = isset( $atts['page_id'] ) ? $atts['page_id'] : ( $post ? $post->ID : 0 );

		if ( ( isset( $atts['location'] ) && $atts['location'] === 'front' ) || ! empty( $atts['prefix'] ) || ! empty( $atts['page_id'] ) ) {
			$edit_atts            = $atts;
			$edit_atts['id']      = isset( $args['foreach_loop'] ) ? $args['entry']->parent_item_id : $args['entry']->id;
			$edit_atts['page_id'] = $page_id;

			$replace_with = FrmProEntriesController::entry_edit_link( $edit_atts );
		} else {
			if ( $args['entry']->post_id ) {
				$replace_with = get_edit_post_link( $args['entry']->post_id );
			} elseif ( current_user_can( 'frm_edit_entries' ) ) {
				$replace_with = FrmProEntry::admin_edit_link( $args['entry']->id );
			}

			if ( ! empty( $replace_with ) ) {
				$replace_with = '<a href="' . esc_url( $replace_with ) . '" class="frm_edit_link ' . esc_attr( $class ) . '">' . $link_text . '</a>';
			}
		}

		$content = str_replace( $shortcodes[0][ $short_key ], $replace_with, $content );
	}

	/**
	 * @param string $content
	 * @param array  $atts
	 * @param array  $shortcodes
	 * @param int    $short_key
	 * @param array  $args
	 * @return void
	 */
	public static function do_shortcode_deletelink( &$content, $atts, $shortcodes, $short_key, $args ) {
		global $post;

		$page_id = isset( $atts['page_id'] ) ? $atts['page_id'] : ( $post ? $post->ID : 0 );

		if ( ! isset( $atts['label'] ) ) {
			$atts['label'] = false;
		}
		$delete_atts            = $atts;
		$delete_atts['id']      = $args['entry']->id;
		$delete_atts['page_id'] = $page_id;

		$replace_with = FrmProEntriesController::entry_delete_link( $delete_atts );

		$content = str_replace( $shortcodes[0][ $short_key ], $replace_with, $content );
	}

	public static function do_shortcode_evenodd( &$content, $atts, $shortcodes, $short_key, $args ) {
		$content = str_replace( $shortcodes[0][ $short_key ], $args['odd'], $content );
	}

	public static function do_shortcode_post_id( &$content, $atts, $shortcodes, $short_key, $args ) {
		$content = str_replace( $shortcodes[0][ $short_key ], $args['entry']->post_id, $content );
	}

	public static function do_shortcode_parent_id( &$content, $atts, $shortcodes, $short_key, $args ) {
		$content = str_replace( $shortcodes[0][ $short_key ], $args['entry']->parent_item_id, $content );
	}

	public static function do_shortcode_id( &$content, $atts, $shortcodes, $short_key, $args ) {
		$content = str_replace( $shortcodes[0][ $short_key ], $args['entry']->id, $content );
	}

	/**
	 * Process a [created-at] shortcode.
	 *
	 * @param string $content
	 * @param array  $atts
	 * @param array  $shortcodes
	 * @param array  $args
	 * @return void
	 */
	public static function do_shortcode_created_at( &$content, $atts, $shortcodes, $short_key, $args ) {
		if ( isset( $atts['format'] ) ) {
			$time_format = ' ';
		} else {
			$atts['format'] = get_option( 'date_format' );
			$time_format    = '';
		}

		if ( $args['conditional'] ) {
			if ( in_array( $args['tag'], array( 'created_at', 'updated_at' ), true ) ) {
				$atts = self::replace_magic_timestamp_shortcode_values( $atts, $args['tag'], $args['entry'] );
			}
			$atts['short_key'] = $shortcodes[0][ $short_key ];
			self::check_conditional_shortcode( $content, $args['entry']->{$args['tag']}, $atts, $args['tag'] );
		} else {
			if ( isset( $atts['time_ago'] ) ) {
				// $time_ago values can include 1-7, as well as d,w,h,s and their extended day,week,hour,second names as well.
				$time_ago = is_numeric( $atts['time_ago'] ) ? absint( $atts['time_ago'] ) : sanitize_key( $atts['time_ago'] );
				$date     = FrmAppHelper::human_time_diff( strtotime( $args['entry']->{$args['tag']} ), '', $time_ago );
			} else {
				$date = FrmAppHelper::get_formatted_time( $args['entry']->{$args['tag']}, $atts['format'], $time_format );
			}

			$content = str_replace( $shortcodes[0][ $short_key ], $date, $content );
		}
	}

	/**
	 * Convert "created_at" used for [if updated_at] tag conditions.
	 * Also converts "updated_at" used for [if created_at] tag conditions.
	 * This way you can use a condition like [if updated_at greater_than="created_at"][/if updated_at].
	 * And [if created_at less_than="updated_at"][/if created_at].
	 *
	 * @since 6.7
	 *
	 * @param array    $atts
	 * @param string   $tag
	 * @param stdClass $entry
	 * @return array
	 */
	private static function replace_magic_timestamp_shortcode_values( $atts, $tag, $entry ) {
		$value_to_replace = 'created_at' === $tag ? 'updated_at' : 'created_at';
		$conditions       = array_intersect( array_keys( $atts ), self::get_conditions() );
		foreach ( $conditions as $att_key ) {
			if ( $value_to_replace === $atts[ $att_key ] ) {
				$atts[ $att_key ] = FrmAppHelper::get_localized_date( 'Y-m-d H:i:s', $entry->$value_to_replace );
			}
		}
		return $atts;
	}

	public static function do_shortcode_get( &$content, $atts, $shortcodes, $short_key, $args ) {

		$replace_with = FrmFieldsHelper::process_get_shortcode( $atts );

		if ( $args['conditional'] ) {
			$atts['short_key'] = $shortcodes[0][ $short_key ];
			self::check_conditional_shortcode( $content, $replace_with, $atts, $args['tag'], 'if' );
		} else {
			$content = str_replace( $shortcodes[0][ $short_key ], $replace_with, $content );
		}
	}

	public static function do_shortcode_updated_at( &$content, $atts, $shortcodes, $short_key, $args ) {
		self::do_shortcode_created_at( $content, $atts, $shortcodes, $short_key, $args );
	}

	public static function do_shortcode_created_by( &$content, $atts, $shortcodes, $short_key, $args ) {
		$replace_with = FrmFieldsHelper::get_display_value( $args['entry']->{$args['tag']}, (object) array( 'type' => 'user_id' ), $atts );

		if ( $args['conditional'] ) {
			$atts['short_key'] = $shortcodes[0][ $short_key ];
			self::check_conditional_shortcode( $content, $args['entry']->{$args['tag']}, $atts, $args['tag'] );
		} else {
			$content = str_replace( $shortcodes[0][ $short_key ], $replace_with, $content );
		}
	}

	public static function do_shortcode_updated_by( &$content, $atts, $shortcodes, $short_key, $args ) {
		self::do_shortcode_created_by( $content, $atts, $shortcodes, $short_key, $args );
	}

	/**
	 * Process the is_draft shortcode
	 *
	 * @since 2.0.22
	 *
	 * @param string $content
	 * @param array  $atts
	 * @param array  $shortcodes
	 * @param int    $short_key
	 * @param array  $args
	 * @return void
	 */
	public static function do_shortcode_is_draft( &$content, $atts, $shortcodes, $short_key, $args ) {
		if ( $args['conditional'] ) {
			if ( empty( $atts ) ) {
				$atts['equals'] = 1;
			}
			$atts['short_key'] = $shortcodes[0][ $short_key ];

			self::check_conditional_shortcode( $content, $args['entry']->is_draft, $atts, 'is_draft' );
		} else {
			$content = str_replace( $shortcodes[0][ $short_key ], $args['entry']->is_draft, $content );
		}
	}

	/**
	 * @since 3.0
	 */
	public static function do_shortcode_entry_position( &$content, $atts, $shortcodes, $short_key, $args ) {
		if ( ! isset( $args['count'] ) ) {
			$args['count'] = 1;
		}

		if ( $args['conditional'] ) {
			$atts['short_key'] = $shortcodes[0][ $short_key ];

			self::check_conditional_shortcode( $content, $args['count'], $atts, $args['tag'] );
		} else {
			$content = str_replace( $shortcodes[0][ $short_key ], $args['count'], $content );
		}
	}

	/**
	 * When a value is saved as an array, allow show=something to return a specified value from the array
	 *
	 * @since 2.0.23
	 *
	 * @param mixed $replace_with
	 * @param array $atts
	 * @return void
	 */
	private static function maybe_get_show_from_array( &$replace_with, $atts ) {
		if ( ! is_array( $replace_with ) || ! isset( $atts['show'] ) ) {
			return;
		}

		if ( $atts['show'] === 'country_code' && ! empty( $replace_with['country'] ) ) {
			$replace_with = FrmProAddressesController::get_country_code( $replace_with['country'] );
			return;
		}

		if ( isset( $replace_with[ $atts['show'] ] ) ) {
			$replace_with = $replace_with[ $atts['show'] ];
		} elseif ( ! empty( $atts['blank'] ) ) {
			$replace_with = '';
		}
	}

	/**
	 * @param string $content
	 * @param string $replace_with
	 * @param array  $atts
	 * @param string $tag
	 * @param string $condition
	 * @param array  $args
	 * @return void
	 */
	public static function check_conditional_shortcode( &$content, $replace_with, $atts, $tag, $condition = 'if', $args = array() ) {
		$defaults = array( 'field' => false );
		$args     = wp_parse_args( $args, $defaults );

		$checking_if_condition = 'if' === $condition;

		if ( $checking_if_condition ) {
			$replace_with = self::conditional_replace_with_value( $replace_with, $atts, $args['field'], $tag );
			$replace_with = apply_filters( 'frm_conditional_value', $replace_with, $atts, $args['field'], $tag );
		}

		$start_pos = strpos( $content, $atts['short_key'] );

		// Replace identical conditional and foreach shortcodes in this loop
		while ( $start_pos !== false ) {

			$start_pos_len = strlen( $atts['short_key'] );
			$end_pos       = strpos( $content, '[/' . $condition . ' ' . $tag . ']', $start_pos );
			$end_pos_len   = strlen( '[/' . $condition . ' ' . $tag . ']' );

			if ( $end_pos === false ) {
				$end_pos     = strpos( $content, '[/' . $condition . ']', $start_pos );
				$end_pos_len = strlen( '[/' . $condition . ']' );

				if ( $end_pos === false ) {
					return;
				}
			}

			$total_len      = $end_pos + $end_pos_len - $start_pos;
			$is_empty       = ( $replace_with === '' || is_null( $replace_with ) || false === $replace_with );
			$substring_args = compact( 'content', 'start_pos', 'start_pos_len', 'end_pos' );

			if ( $is_empty ) {
				$replacement = '';
				if ( $checking_if_condition ) {
					$substring = self::get_conditional_substring( $substring_args );
					if ( self::conditional_substring_contains_else( $substring ) ) {
						$replacement = self::get_conditional_substring_half( $substring, true );
					}
				}
				$content = substr_replace( $content, $replacement, $start_pos, $total_len );
			} elseif ( 'foreach' === $condition ) {
				$content_len    = $end_pos - ( $start_pos + $start_pos_len );
				$repeat_content = substr( $content, $start_pos + $start_pos_len, $content_len );
				self::foreach_shortcode( $replace_with, $args, $repeat_content, $atts );
				$content = substr_replace( $content, $repeat_content, $start_pos, $total_len );
			} else {
				$substring = self::get_conditional_substring( $substring_args );
				if ( self::conditional_substring_contains_else( $substring ) ) {
					$replacement = self::get_conditional_substring_half( $substring, false );
					$content     = substr_replace( $content, $replacement, $start_pos, $total_len );
				} else {
					$content = substr_replace( $content, '', $end_pos, $end_pos_len );
					$content = substr_replace( $content, '', $start_pos, $start_pos_len );
				}
			}

			$start_pos = strpos( $content, $atts['short_key'] );
		}
	}

	/**
	 * Get either the left or the right substring for if conditions containing an [else] shortcode.
	 *
	 * @since 5.0.14
	 *
	 * @param string $substring
	 * @param bool   $else if true, the second half of the explode will be returned. if false, the first half is returned.
	 * @return string
	 */
	private static function get_conditional_substring_half( $substring, $else = false ) {
		$split = explode( '[else]', $substring );
		return $split[ $else ? 1 : 0 ];
	}

	/**
	 * Get the text inside of an if condition shortcode.
	 *
	 * @since 5.0.14
	 *
	 * @param array $args expects keys 'content', 'start_pos', 'start_pos_len', 'end_pos'.
	 * @return string
	 */
	private static function get_conditional_substring( $args ) {
		$start  = $args['start_pos'] + $args['start_pos_len'];
		$length = $args['end_pos'] - $start;
		return substr( $args['content'], $start, $length );
	}

	/**
	 * Check if content has an else shortcode.
	 *
	 * @since 5.0.14
	 *
	 * @param string $substring
	 * @return bool
	 */
	private static function conditional_substring_contains_else( $substring ) {
		return false !== strpos( $substring, '[else]' );
	}

	/**
	 * Loop through each entry linked through a repeating field when using [foreach].
	 *
	 * @param array|string $replace_with
	 * @param array        $args
	 * @param string       $repeat_content
	 * @param array        $atts
	 */
	public static function foreach_shortcode( $replace_with, $args, &$repeat_content, $atts = array() ) {
		$foreach_content = '';
		$sub_entries     = is_array( $replace_with ) ? $replace_with : explode( ',', $replace_with );

		if ( ! empty( $atts['order'] ) && 'desc' === $atts['order'] ) {
			$sub_entries = array_reverse( $sub_entries );
		}

		foreach ( $sub_entries as $sub_entry ) {
			$sub_entry = trim( $sub_entry );
			if ( ! is_numeric( $sub_entry ) ) {
				continue;
			}

			$entry = FrmEntry::getOne( $sub_entry );
			if ( ! $entry ) {
				continue;
			}

			$args['foreach_loop'] = true;

			$shortcodes        = FrmProDisplaysHelper::get_shortcodes( $repeat_content, $entry->form_id );
			$repeating_content = $repeat_content;
			foreach ( $shortcodes[0] as $short_key => $tag ) {
				self::replace_single_shortcode( $shortcodes, $short_key, $tag, $entry, $args['display'], $args, $repeating_content );
			}

			if ( ( $foreach_content || '0' === $foreach_content ) && isset( $atts['sep'] ) ) {
				$foreach_content .= wp_kses_post( $atts['sep'] );
			}

			$foreach_content .= $repeating_content;
		}

		$repeat_content = $foreach_content;
	}

	/**
	 * Returns a list of conditions used in Conditionals.
	 *
	 * @return array
	 */
	public static function get_conditions() {
		return array(
			'equals',
			'not_equal',
			'not_equals',
			'like',
			'not_like',
			'contains', // This is an alias of 'like'.
			'does_not_contain', // This is an alias of 'not_like'.
			'less_than',
			'less_than_or_equal_to',
			'greater_than',
			'greater_than_or_equal_to',
			'starts_with',
			'ends_with',
		);
	}

	/**
	 * @param mixed  $replace_with
	 * @param array  $atts
	 * @param mixed  $field
	 * @param string $tag
	 * @return mixed
	 */
	public static function conditional_replace_with_value( $replace_with, $atts, $field, $tag ) {
		$conditions = self::get_conditions();

		if ( $field && $field->type === 'data' ) {
			$show_id = is_numeric( $replace_with ) && ! empty( $atts['show'] ) && 'id' === $atts['show'];
			// $replace_with is already in the expected format if show="id" is included, so skip all of this display value logic.
			if ( ! $show_id ) {
				$old_replace_with = $replace_with;

				// Only get the displayed value if it hasn't been set yet.
				if ( is_numeric( $replace_with ) || ( ! is_null( $replace_with ) && is_numeric( str_replace( array( ',', ' ' ), array( '', '' ), $replace_with ) ) ) || is_array( $replace_with ) ) {
					$replace_with = FrmFieldsHelper::get_display_value( $replace_with, $field, $atts );
					if ( $old_replace_with == $replace_with ) {
						$replace_with = '';
					}
				}

				// Get the linked field to properly evaluate conditions.
				if ( $replace_with !== '' && ! empty( $atts['show'] ) ) {
					$show_field = FrmField::getOne( $atts['show'] );
					if ( $show_field && in_array( $show_field->type, array( 'time', 'date', 'user_id' ), true ) ) {
						$field = $show_field;
						unset( $atts['show'] );
					}
				}
			}
		}

		if ( ( $field && $field->type === 'user_id' ) || in_array( $tag, array( 'updated_by', 'created_by' ), true ) ) {
			// Check if conditional is for current user.
			if ( isset( $atts['equals'] ) && $atts['equals'] === 'current' ) {
				$atts['equals'] = get_current_user_id();
			}

			if ( isset( $atts['not_equal'] ) && $atts['not_equal'] === 'current' ) {
				$atts['not_equal'] = get_current_user_id();
			}

			if ( isset( $atts['show'] ) ) {
				$atts['blank'] = isset( $atts['blank'] ) ? $atts['blank'] : 1;
				$replace_with  = FrmFieldsHelper::get_display_value( $replace_with, $field, $atts );
			}
		} elseif ( self::is_timestamp_tag( $tag ) || ( $field && $field->type === 'date' ) ) {
			self::prepare_date_for_eval( $conditions, $tag, $atts );
		} elseif ( $field && $field->type === 'time' ) {
			$formatted_time = false;
			foreach ( $conditions as $att_name ) {
				if ( isset( $atts[ $att_name ] ) && $atts[ $att_name ] != '' ) {
					if ( strtolower( $atts[ $att_name ] ) === 'now' ) {
						$atts[ $att_name ] = FrmProAppHelper::get_date( 'H:i' );
					} else {
						$atts[ $att_name ] = gmdate( 'H:i', strtotime( $atts[ $att_name ] ) );
					}

					if ( ! $formatted_time ) {
						$replace_with   = FrmProAppHelper::format_time( $replace_with, 'H:i' );
						$formatted_time = true;
					}
				}
			}
		} elseif ( $field && 'file' === $field->type && is_numeric( $replace_with ) ) {
			$replace_with = FrmFieldsHelper::get_display_value( $replace_with, $field, $atts );
		} elseif ( is_callable( 'FrmAppHelper::decode_specialchars' ) ) {
			// Compare properly with &.
			FrmAppHelper::decode_specialchars( $replace_with );
		}

		self::eval_conditions( $conditions, $atts, $replace_with, $field );

		return $replace_with;
	}

	/**
	 * @param string $tag
	 * @return false|int
	 */
	private static function is_timestamp_tag( $tag ) {
		return preg_match( '/^(created[-|_]at|updated[-|_]at)$/', $tag );
	}

	/**
	 * @param array  $conditions
	 * @param string $tag
	 * @param array  $atts
	 * @return void
	 */
	private static function prepare_date_for_eval( $conditions, $tag, &$atts ) {
		foreach ( $conditions as $att_name ) {
			if ( isset( $atts[ $att_name ] ) && $atts[ $att_name ] != '' && ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', trim( $atts[ $att_name ] ) ) ) {
				if ( self::is_timestamp_tag( $tag ) ) {
					self::get_gmt_for_filter( $att_name, $atts[ $att_name ] );
				} elseif ( strtolower( $atts[ $att_name ] ) === 'now' ) {
					$atts[ $att_name ] = FrmProAppHelper::get_date( 'Y-m-d' );
				} else {
					$atts[ $att_name ] = gmdate( 'Y-m-d', strtotime( $atts[ $att_name ] ) );
				}
			}
			unset( $att_name );
		}
	}

	/**
	 * @param string $compare
	 * @param string $where_val
	 * @return void
	 */
	public static function get_gmt_for_filter( $compare, &$where_val ) {
		$original_value = $where_val;

		if ( $where_val === 'NOW' ) {
			$where_val = current_time( 'mysql', 1 );
		}

		$compare = strtolower( $compare );
		if ( strpos( $compare, 'like' ) === false ) {
			$where_val = gmdate( 'Y-m-d H:i:s', strtotime( $where_val ) );

			// If using less than or equal to, set the time to the end of the day.
			if ( $compare === '<=' || $compare === 'less_than' ) {
				$where_val = str_replace( '00:00:00', '23:59:59', $where_val );
			}

			// Convert date to GMT since that is the format in the DB.
			if ( self::should_convert_to_gmt( $original_value ) ) {
				$where_val = get_gmt_from_date( $where_val );
			}
		}
	}

	/**
	 * Avoid converting the value twice if the compare value was a relative value like
	 * '-1 hour', '-10 minutes', or '-30 seconds'.
	 *
	 * @since 6.7.1
	 *
	 * @param string $value The original value being passed as a shortcode compare value.
	 * @return bool
	 */
	private static function should_convert_to_gmt( $value ) {
		$substrings = array( 'hour', 'minute', 'second' );
		foreach ( $substrings as $substring ) {
			if ( false !== stripos( $value, $substring ) ) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Run all of the eval functions beginning with eval_ and ending with _condition.
	 *
	 * @param array    $conditions
	 * @param array    $atts
	 * @param string   $replace_with
	 * @param stdClass $field
	 * @return void
	 */
	private static function eval_conditions( $conditions, $atts, &$replace_with, $field ) {
		foreach ( $conditions as $condition ) {
			if ( ! isset( $atts[ $condition ] ) ) {
				continue;
			}

			self::maybe_swap_condition_alias( $condition, $atts );

			if ( 'param' === $atts[ $condition ] && isset( $atts['param'] ) ) {
				$atts[ $condition ] = FrmFieldsHelper::process_get_shortcode( $atts );
			}

			$function = 'eval_' . $condition . '_condition';
			self::$function( $atts, $replace_with, $field );
		}
	}

	/**
	 * Map 'contains' to 'like' and 'does_not_contain' to 'not_like'.
	 *
	 * @since 6.8
	 *
	 * @param string $condition
	 * @param array  $atts
	 * @return void
	 */
	private static function maybe_swap_condition_alias( &$condition, &$atts ) {
		if ( 'contains' === $condition ) {
			$condition    = 'like';
			$atts['like'] = $atts['contains'];
			unset( $atts['contains'] );
			return;
		}

		if ( 'does_not_contain' === $condition ) {
			$condition        = 'not_like';
			$atts['not_like'] = $atts['does_not_contain'];
			unset( $atts['does_not_contain'] );
		}
	}

	/**
	 * @param array    $atts
	 * @param string   $replace_with
	 * @param stdClass $field
	 * @return void
	 */
	private static function eval_equals_condition( $atts, &$replace_with, $field ) {
		if ( $replace_with != $atts['equals'] ) {
			if ( $field && ( in_array( $field->type, array( 'data', 'quiz_score' ), true ) ) ) {
				$replace_with = FrmFieldsHelper::get_display_value( $replace_with, $field, $atts );
				if ( $replace_with != $atts['equals'] ) {
					$replace_with = '';
				}
			} elseif ( isset( $field->field_options['post_field'] ) && $field->field_options['post_field'] === 'post_category' ) {
				$cats         = explode( ', ', $replace_with );
				$replace_with = '';
				foreach ( $cats as $cat ) {
					if ( $atts['equals'] == strip_tags( $cat ) ) {
						$replace_with = true;
						return;
					}
				}
			} else {
				$replace_with = '';
			}
		} elseif ( $atts['equals'] == '' && $replace_with == '' ) {
			//if the field is blank, give it a value
			$replace_with = true;
		}
	}

	/**
	 * Not equals term is exactly dose what not equal and it's added for convention only.
	 *
	 * @since 5.4.3.
	 * @param array    $atts condition attributes.
	 * @param string   $replace_with string to check condition against.
	 * @param stdClass $field field.
	 *
	 * @return void
	 */
	private static function eval_not_equals_condition( $atts, &$replace_with, $field ) {
		$atts['not_equal'] = $atts['not_equals'];
		unset( $atts['not_equals'] );
		self::eval_not_equal_condition( $atts, $replace_with, $field );
	}

	/**
	 * @param array    $atts
	 * @param string   $replace_with
	 * @param stdClass $field
	 * @return void
	 */
	private static function eval_not_equal_condition( $atts, &$replace_with, $field ) {
		if ( $field && 'quiz_score' === $field->type ) {
			$replace_with = FrmFieldsHelper::get_display_value( $replace_with, $field, $atts );
		}

		if ( $replace_with == $atts['not_equal'] ) {
			$replace_with = '';
		} elseif ( $replace_with == '' && $atts['not_equal'] !== '' ) {
			$replace_with = true;
		} elseif ( ! empty( $replace_with ) && isset( $field->field_options['post_field'] ) && $field->field_options['post_field'] === 'post_category' ) {
			$cats = explode( ', ', $replace_with );
			foreach ( $cats as $cat ) {
				if ( $atts['not_equal'] == strip_tags( $cat ) ) {
					$replace_with = '';
					return;
				}

				unset( $cat );
			}
		}
	}

	/**
	 * @param array       $atts
	 * @param string|null $replace_with
	 * @return void
	 */
	private static function eval_like_condition( $atts, &$replace_with ) {
		if ( $atts['like'] == '' ) {
			return;
		}

		if ( ! is_string( $replace_with ) || stripos( $replace_with, $atts['like'] ) === false ) {
			$replace_with = '';
		}
	}

	/**
	 * @param array       $atts
	 * @param string|null $replace_with
	 * @return void
	 */
	private static function eval_not_like_condition( $atts, &$replace_with ) {
		if ( $atts['not_like'] == '' ) {
			return;
		}

		if ( $replace_with == '' ) {
			$replace_with = true;
		} elseif ( stripos( $replace_with, $atts['not_like'] ) !== false ) {
			$replace_with = '';
		}
	}

	private static function eval_less_than_condition( $atts, &$field_value ) {
		if ( $field_value >= $atts['less_than'] ) {
			// Condition is false
			$field_value = '';
		}
	}

	private static function eval_less_than_or_equal_to_condition( $atts, &$field_value ) {
		$condition_is_true = $field_value <= $atts['less_than_or_equal_to'];
		if ( ! $condition_is_true ) {
			$field_value = '';
		}
	}

	private static function eval_greater_than_condition( $atts, &$field_value ) {
		if ( $field_value <= $atts['greater_than'] ) {
			// Condition is false
			$field_value = '';
		}
	}

	private static function eval_greater_than_or_equal_to_condition( $atts, &$field_value ) {
		$condition_is_true = $field_value >= $atts['greater_than_or_equal_to'];
		if ( ! $condition_is_true ) {
			$field_value = '';
		}
	}

	/**
	 * Perform a case-insensitive starts with check.
	 *
	 * @since 6.8
	 *
	 * @param array        $atts
	 * @param array|string $replace_with
	 * @return void
	 */
	private static function eval_starts_with_condition( $atts, &$replace_with ) {
		if ( ! is_string( $replace_with ) ) {
			$replace_with = '';
			return;
		}

		$haystack = $replace_with;
		$needle   = $atts['starts_with'];
		if ( $needle && stripos( $haystack, $needle ) !== 0 ) {
			$replace_with = '';
		}
	}

	/**
	 * Perform a case-insensitive ends with check.
	 *
	 * @since 6.8
	 *
	 * @param array        $atts
	 * @param array|string $replace_with
	 * @return void
	 */
	private static function eval_ends_with_condition( $atts, &$replace_with ) {
		if ( ! is_string( $replace_with ) ) {
			$replace_with = '';
			return;
		}

		$haystack = strtolower( $replace_with );
		$needle   = strtolower( $atts['ends_with'] );

		if ( $needle && substr( $haystack, -strlen( $needle ) ) !== $needle ) {
			$replace_with = '';
		}
	}

	/**
	 * @param array $atts
	 * @param false $display
	 * @param array $args
	 * @return void
	 */
	public static function trigger_shortcode_atts( $atts, $display, $args, &$replace_with ) {
		$frm_atts = array(
			'remove_accents',
			'sanitize',
			'sanitize_url',
			'truncate',
			'clickable',
		);

		$included_atts = array_intersect( $frm_atts, array_keys( $atts ) );

		foreach ( $included_atts as $included_att ) {
			if ( '0' === $atts[ $included_att ] ) {
				// Skip any option that uses 0 so sanitize_url=0 does not encode.
				continue;
			}
			$function     = 'atts_' . $included_att;
			$replace_with = self::$function( $replace_with, $atts, $display, $args );
		}
	}

	/**
	 * Converts all accent characters to ASCII characters.
	 *
	 * @since 6.3.1
	 *
	 * @param string $replace_with The text to remove accents from.
	 *
	 * @return string
	 */
	public static function atts_remove_accents( $replace_with ) {
		return remove_accents( $replace_with );
	}

	public static function atts_sanitize( $replace_with ) {
		return sanitize_title_with_dashes( $replace_with );
	}

	public static function atts_sanitize_url( $replace_with ) {
		return urlencode( $replace_with );
	}

	public static function atts_truncate( $replace_with, $atts, $display, $args ) {
		if ( isset( $atts['more_text'] ) ) {
			$more_link_text = $atts['more_text'];
		} else {
			$more_link_text = isset( $atts['more_link_text'] ) ? $atts['more_link_text'] : '. . .';
		}

		if ( ! empty( $atts['no_link'] ) ) {
			return FrmAppHelper::truncate( $replace_with, (int) $atts['truncate'], 3, $more_link_text );
		}

		// If we're on the listing page of a Dynamic View, use detail link for truncate link
		if ( $display && $display->frm_show_count === 'dynamic' && $args['show'] === 'all' ) {
			$detail_link    = self::get_detail_link( $atts, $display );
			$more_link_text = ' <a href="' . esc_url( $detail_link ) . '">' . $more_link_text . '</a>';
			return FrmAppHelper::truncate( $replace_with, (int) $atts['truncate'], 3, $more_link_text );
		}

		if ( ! $replace_with ) {
			return $replace_with;
		}

		$clean_text = trim( wp_strip_all_tags( $replace_with ) );
		if ( ! $clean_text ) {
			return '';
		}

		$part_one = FrmAppHelper::truncate( $clean_text, (int) $atts['truncate'], 3, '' );
		$pos      = strpos( $clean_text, $part_one );
		// Only replace the first occurrence of the string.
		$part_two = substr_replace( $clean_text, '', $pos, strlen( $part_one ) );

		if ( ! empty( $part_two ) ) {
			$replace_with = $part_one . '<a href="#" onclick="jQuery(this).next().css(\'display\', \'inline\');jQuery(this).css(\'display\', \'none\');return false;" class="frm_text_exposed_show"> ' . $more_link_text . '</a><span style="display:none;">' . $part_two . '</span>';
		}

		return $replace_with;
	}

	public static function atts_clickable( $replace_with ) {
		return make_clickable( $replace_with );
	}
}
