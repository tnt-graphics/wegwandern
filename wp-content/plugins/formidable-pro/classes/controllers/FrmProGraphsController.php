<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmProGraphsController {

	/**
	 * Do the frm-graph shortcode
	 *
	 * @param array $atts
	 * @return string
	 */
	public static function graph_shortcode( $atts ) {
		self::convert_old_atts_to_new_atts( $atts );

		self::combine_defaults_and_user_defined_attributes( $atts );

		self::format_atts( $atts );

		if ( empty( $atts['fields'] ) && empty( $atts['form'] ) ) {
			return __( 'You must include a field id or key in your graph shortcode.', 'formidable-pro' );
		}

		$graph_data = self::generate_google_graph_data( $atts );

		$html = self::get_graph_html( $graph_data, $atts );

		$html = self::maybe_add_title_to_table( $html, $atts );

		return $html;
	}

	/**
	 * Google Tables do not support titles by default. This function adds the title.
	 *
	 * @since 6.8
	 *
	 * @param string $html
	 * @param array  $atts
	 * @return string
	 */
	private static function maybe_add_title_to_table( $html, $atts ) {
		if ( empty( $atts['type'] ) || 'table' !== $atts['type'] || empty( $atts['title'] ) ) {
			return $html;
		}

		$params = array();
		$style  = self::get_styles_for_table_title( $atts );
		if ( $style ) {
			$params['style'] = $style;
		}

		$title = '<div ' . FrmAppHelper::array_to_html_params( $params ) . '>' . $atts['title'] . '<div>';

		return $title . $html;
	}

	/**
	 * @since 6.8
	 *
	 * @param array $atts
	 * @return string
	 */
	private static function get_styles_for_table_title( $atts ) {
		$style = '';
		if ( ! empty( $atts['title_size'] ) && is_numeric( $atts['title_size'] ) ) {
			$style .= 'font-size: ' . absint( $atts['title_size'] ) . 'px;';
		}
		if ( ! empty( $atts['title_bold'] ) ) {
			$style .= 'font-weight: bold;';
		}
		if ( ! empty( $atts['title_italic'] ) ) {
			$style .= 'font-style: italic;';
		}
		if ( ! empty( $atts['title_color'] ) ) {
			$style .= 'color: ' . $atts['title_color'] . ';';
		}
		if ( ! empty( $atts['title_font'] ) ) {
			$style .= 'font-family: "' . $atts['title_font'] . '";';
		}
		return $style;
	}

	/**
	 * Convert old, deprecated attributes to the new attributes to maintain reverse compatibility
	 *
	 * @since 2.02.05
	 * @param array $atts
	 */
	private static function convert_old_atts_to_new_atts( &$atts ) {
		self::convert_id_parameter_to_fields_parameter( $atts );

		if ( isset( $atts['min'] ) ) {
			$atts['y_min'] = $atts['min'];
			unset( $atts['min'] );
		}

		if ( isset( $atts['max'] ) ) {
			$atts['y_max'] = $atts['max'];
			unset( $atts['max'] );
		}

		self::adjust_deprecated_date_parameters( $atts );

		if ( isset( $atts['entry_id'] ) ) {
			$atts['entry'] = $atts['entry_id'];
			unset( $atts['entry_id'] );
		}

		if ( isset( $atts['x_order'] ) && ! $atts['x_order'] ) {
			$atts['x_order'] = 'field_opts';
		}

		if ( isset( $atts['include_js'] ) ) {
			unset( $atts['include_js'] );
		}

		if ( isset( $atts['truncate_label'] ) ) {
			unset( $atts['truncate_label'] );
		}

		if ( isset( $atts['response_count'] ) ) {
			unset( $atts['response_count'] );
		}
	}

	/**
	 * Convert the old id and ids parameters to the new fields parameter
	 *
	 * @since 2.02.05
	 * @param array $atts
	 */
	private static function convert_id_parameter_to_fields_parameter( &$atts ) {
		if ( ! is_array( $atts ) ) {
			return;
		}

		$id = '';
		if ( ! empty( $atts['id'] ) ) {
			$id_array = explode( ',', $atts['id'] );

			if ( count( $id_array ) > 1 ) {
				esc_html_e( 'Using multiple values in the id graph parameter has been removed as of version 2.02.04', 'formidable-pro' );
			}

			$id = reset( $id_array ) . ',';
			unset( $atts['id'] );
		}

		$ids = '';
		if ( isset( $atts['ids'] ) ) {
			if ( $atts['ids'] ) {
				$ids = $atts['ids'];
			}
			unset( $atts['ids'] );
		}

		if ( ! isset( $atts['fields'] ) ) {
			$atts['fields'] = $id;
			if ( ! empty( $ids ) ) {
				$atts['fields'] .= $ids;
			}
		}
	}

	/**
	 * Convert the deprecated x_start, x_end, start_date, and end_date parameters to the new
	 * x_greater_than, x_less_than, created_at_greater_than, and created_at_less_than parameters
	 *
	 * @since 2.02.05
	 * @param array $atts
	 */
	private static function adjust_deprecated_date_parameters( &$atts ) {
		if ( isset( $atts['x_start'] ) ) {
			$atts['start_date'] = $atts['x_start'];
			unset( $atts['x_start'] );
		}

		if ( isset( $atts['x_end'] ) ) {
			$atts['end_date'] = $atts['x_end'];
			unset( $atts['x_end'] );
		}

		if ( ! isset( $atts['start_date'] ) && ! isset( $atts['end_date'] ) ) {
			return;
		}

		if ( isset( $atts['x_axis'] ) ) {
			$x_axis_field = self::get_x_axis_field( $atts['x_axis'] );
		} else {
			$x_axis_field = '';
		}

		self::convert_old_date_parameters_to_new_parameters( 'start_date', $x_axis_field, $atts );
		self::convert_old_date_parameters_to_new_parameters( 'end_date', $x_axis_field, $atts );
	}

	/**
	 * Convert start_date and end_date to new parameters
	 *
	 * @since 2.02.05
	 * @param string $old_key
	 * @param object|string $x_axis_field
	 * @param array $atts
	 */
	private static function convert_old_date_parameters_to_new_parameters( $old_key, $x_axis_field, &$atts ) {
		if ( isset( $atts[ $old_key ] ) ) {
			if ( $old_key === 'start_date' ) {
				$operator_text = '_greater_than';
			} elseif ( $old_key === 'end_date' ) {
				$operator_text = '_less_than';
			} else {
				return;
			}

			if ( self::is_date_field( $x_axis_field ) ) {
				$atts[ $x_axis_field->id . $operator_text ] = $atts[ $old_key ];
			} else {
				$atts[ 'created_at' . $operator_text ] = $atts[ $old_key ];
			}

			unset( $atts[ $old_key ] );
		}
	}

	/**
	 * Combine the graph defaults with the user-defined attributes
	 * Removes defaults with a blank value
	 *
	 * @since 2.02.05
	 * @param array $atts
	 */
	private static function combine_defaults_and_user_defined_attributes( &$atts ) {
		$defaults = self::get_graph_defaults();
		if ( isset( $atts['type'] ) && $atts['type'] === 'table' && ! isset( $atts['height'] ) ) {
			$defaults['height'] = 'auto';
		}

		$combined_atts = array();
		foreach ( $defaults as $k => $value ) {
			if ( isset( $atts[ $k ] ) ) {
				$combined_atts[ $k ] = $atts[ $k ];
				unset( $atts[ $k ] );
			} elseif ( $value !== '' || in_array( $k, array( 'fields', 'form' ) ) ) {
				$combined_atts[ $k ] = $value;
			}
		}

		$combined_atts['filters'] = $atts;

		$atts = $combined_atts;
	}

	/**
	 * Get the default graph attributes
	 *
	 * @since 2.02.05
	 * @since 6.7.1 This method is public.
	 * @return array
	 */
	public static function get_graph_defaults() {

		$defaults = array(
			'fields'                  => '',
			'form'                    => '',
			'type'                    => 'column',
			'data_type'               => 'count',
			'date_format'             => '',
			'limit'                   => '',
			'include_zero'            => false,
			'created_at_greater_than' => '',
			'created_at_less_than'    => '',
			'group_by'                => '',
			'user_id'                 => '',
			'entry'                   => '',
			'drafts'                  => '',
			'title'                   => '',
			'title_size'              => 14,
			'title_font'              => '',
			'title_bold'              => false,
			'title_italic'            => false,
			'title_color'             => '#666',
			'truncate'                => 40,
			'tooltip_label'           => '',
			'show_key'                => false,
			'legend_size'             => '',
			'legend_position'         => '',
			'x_axis'                  => '',
			'x_title'                 => '',
			'x_title_size'            => 13,
			'x_title_color'           => '#666',
			'x_labels_size'           => '',
			'x_slanted_text'          => true,
			'x_text_angle'            => 20,
			'x_min'                   => '',
			'x_max'                   => '',
			'x_order'                 => 'default',
			'x_show_text_every'       => '',
			'y_title'                 => '',
			'y_title_size'            => 13,
			'y_title_color'           => '#666',
			'y_labels_size'           => '',
			'y_min'                   => '',
			'y_max'                   => '',
			'pagesize'                => 0,
			'sort_column'             => -1,
			'sort_ascending'          => true,
			'colors'                  => self::get_default_colors(),
			'grid_color'              => '#CCC',
			'x_grid_color'            => '#CCC',
			'bg_color'                => '#FFFFFF',
			'is3d'                    => false,
			'height'                  => 400,
			'width'                   => 400,
			'chart_area'              => '',
			'is_stacked'              => false,
			'pie_hole'                => '',
			'curve_type'              => '',
			'no_data'                 => __( 'No data', 'formidable-pro' ),
		);

		return $defaults;
	}

	/**
	 * Get the default graph colors
	 *
	 * @since 2.0
	 *
	 * @return string
	 */
	private static function get_default_colors() {
		$colors = '#00bbde,#fe6672,#eeb058,#8a8ad6,#ff855c,#00cfbb,#5a9eed,#73d483,#c879bb,#0099b6';
		$colors = (string) apply_filters( 'frm_graph_default_colors', $colors );

		return $colors;
	}

	/**
	 * Gets data type options.
	 *
	 * @since 6.8.3
	 *
	 * @return array
	 */
	public static function get_data_type_options() {
		$options = array(
			'count'   => __( 'The number of entries', 'formidable-pro' ),
			'total'   => __( 'Add the field values together', 'formidable-pro' ),
			'average' => __( 'Average the totaled field values', 'formidable-pro' ),
			'maximum' => __( 'The highest of a set of field values', 'formidable-pro' ),
			'minimum' => __( 'The lowest of a set of field values', 'formidable-pro' ),
		);

		/**
		 * Filters the graph data type options.
		 *
		 * @since 6.8.3
		 *
		 * @param array $options Data type options, an array with keys are data types and values are labels.
		 */
		return apply_filters( 'frm_pro_graph_data_type_options', $options );
	}

	/**
	 * Format the user-defined attributes
	 *
	 * @since 2.02.05
	 * @param array $atts
	 */
	private static function format_atts( &$atts ) {
		self::convert_field_keys_to_ids( $atts );

		if ( ! empty( $atts['fields'] ) ) {
			$atts['fields'] = self::get_fields_in_defined_order( $atts['fields'] );
		} elseif ( ! empty( $atts['form'] ) && $atts['form'] === 'all' ) {
			$atts['form_id'] = 'all';
		} elseif ( ! empty( $atts['form'] ) ) {
			$atts['form'] = FrmForm::getOne( $atts['form'] );
			if ( ! $atts['form'] ) {
				return;
			}

			$atts['form_id'] = $atts['form']->id;
		} else {
			return;
		}

		if ( isset( $atts['created_at_greater_than'] ) ) {
			$atts['created_at_greater_than'] = FrmAppHelper::replace_quotes( $atts['created_at_greater_than'] );
		}

		if ( isset( $atts['created_at_less_than'] ) ) {
			$atts['created_at_less_than'] = FrmAppHelper::replace_quotes( $atts['created_at_less_than'] );
		}

		// If limit is set, get only the top results
		if ( isset( $atts['limit'] ) ) {
			$atts['x_order'] = 'desc';
		}

		if ( isset( $atts['user_id'] ) ) {
			$atts['user_id'] = FrmAppHelper::get_user_id_param( $atts['user_id'] );
		}

		if ( isset( $atts['drafts'] ) ) {
			$atts['is_draft'] = $atts['drafts'];
			unset( $atts['drafts'] );
		}

		self::convert_entry_keys_to_ids( $atts );
	}

	/**
	 * Get fields but in the same order that they were defined.
	 * For example, if array( 10, 2 ) is passed, we expect field ID 10 to be the first item.
	 *
	 * @since 6.8
	 *
	 * @param array $field_ids
	 * @return array
	 */
	private static function get_fields_in_defined_order( $field_ids ) {
		$fields = FrmField::getAll( array( 'fi.id' => $field_ids ) );
		if ( 1 === count( $fields ) ) {
			// If there is only a single field, there is no need to re-order.
			return $fields;
		}

		return array_reduce(
			$field_ids,
			function ( $total, $field_id ) use ( $fields ) {
				foreach ( $fields as $field ) {
					if ( is_array( $total ) && (int) $field->id === (int) $field_id ) {
						$total[] = $field;
						break;
					}
				}
				return $total;
			},
			array()
		);
	}

	/**
	 * Convert field keys to IDs
	 *
	 * @since 2.02.05
	 * @param array $atts
	 */
	private static function convert_field_keys_to_ids( &$atts ) {
		$atts['fields'] = self::convert_keys_to_ids( $atts['fields'], 'field' );
	}

	/**
	 * Convert entry keys to IDs
	 *
	 * @since 2.02.05
	 * @param array $atts
	 */
	private static function convert_entry_keys_to_ids( &$atts ) {
		if ( isset( $atts['entry'] ) ) {
			$atts['entry_ids'] = self::convert_keys_to_ids( $atts['entry'], 'entry' );
			unset( $atts['entry'] );
		}
	}

	/**
	 * Convert field or entry keys to ids
	 *
	 * @since 2.02.05
	 * @param array $keys
	 * @param string $type
	 * @return array
	 */
	private static function convert_keys_to_ids( $keys, $type ) {
		if ( $keys ) {
			$keys = explode( ',', $keys );

			foreach ( $keys as $i => $k ) {
				if ( ! is_numeric( $k ) ) {
					if ( 'entry' === $type ) {
						$id = FrmEntry::get_id_by_key( $k );
					} else {
						$id = FrmField::get_id_by_key( $k );
					}
					if ( $id ) {
						$keys[ $i ] = $id;
					} else {
						unset( $keys[ $i ] );
					}
				}
			}
		}

		return $keys;
	}

	/**
	 * Get the HTML for a graph
	 *
	 * @since 2.02.05
	 * @param array $graph_data
	 * @param array $atts
	 * @return string
	 */
	private static function get_graph_html( $graph_data, $atts ) {
		if ( empty( $graph_data['data'] ) ) {
			$html = apply_filters( 'frm_no_data_graph', '<div class="frm_no_data_graph">' . $atts['no_data'] . '</div>' );
		} else {
			$html  = '<div id="chart_' . $graph_data['graph_id'] . '" style="height:' . $atts['height'] . ';';
			$html .= 'width:' . $atts['width'] . '"></div>';
		}

		return $html;
	}

	/**
	 * Generate the data, options, package, etc. for a google graph
	 *
	 * @since 2.02.05
	 * @param array $atts
	 * @return array
	 */
	private static function generate_google_graph_data( $atts ) {
		global $frm_vars;

		self::prepare_frm_vars( $frm_vars );

		$type          = self::get_graph_type( $atts );
		$data          = self::get_graph_data( $atts );
		$options       = self::get_graph_options( $type, $atts );
		$graph_package = self::get_graph_package( $type );

		$graph_auto_id = count( $frm_vars['google_graphs']['graphs'] ) + 1;
		$graph_id      = '_frm_' . strtolower( $type ) . $graph_auto_id;

		/**
		 * Filter the ID of the graph.
		 *
		 * @since 5.4
		 *
		 * @param string $graph_id
		 * @param array  $args {
		 *     @type int    $graph_auto_id Graph Auto ID.
		 *     @type string $type          Graph type.
		 * }
		 */
		$graph_id   = apply_filters( 'frm_graph_id', $graph_id, compact( 'graph_auto_id', 'type' ) );
		$graph_data = array(
			'type'     => $type,
			'data'     => $data,
			'options'  => $options,
			'package'  => $graph_package,
			'graph_id' => $graph_id,
		);

		$frm_vars['google_graphs']['graphs'][] = $graph_data;

		return $graph_data;
	}

	/**
	 * Prepare frm_vars for the graphs to be loaded
	 *
	 * @since 2.02.05
	 * @param array $frm_vars
	 */
	private static function prepare_frm_vars( &$frm_vars ) {
		$frm_vars['forms_loaded'][] = true;

		if ( ! isset( $frm_vars['google_graphs'] ) ) {
			$frm_vars['google_graphs'] = array();
		}

		if ( ! isset( $frm_vars['google_graphs']['graphs'] ) ) {
			$frm_vars['google_graphs']['graphs'] = array();
		}
	}

	/**
	 * Get the graph type
	 *
	 * @since 2.02.05
	 * @param array $atts
	 * @return string
	 */
	private static function get_graph_type( $atts ) {
		$type = strtolower( $atts['type'] );

		if ( 'bar' === $type ) {
			$type = 'column';
		} elseif ( 'hbar' === $type ) {
			$type = 'bar';
		} elseif ( $type === 'stepped_area' || $type === 'steppedarea' ) {
			$type = 'steppedArea';
		}

		$allowed_types = array(
			'pie',
			'line',
			'column',
			'area',
			'steppedArea',
			'geo',
			'bar',
			'scatter',
			'histogram',
			'table',
		);

		if ( ! in_array( $type, $allowed_types ) ) {
			$type = 'column';
		}

		return $type;
	}

	/**
	 * Get the row and column data for a google graph
	 *
	 * @since 2.02.05
	 * @param array $atts
	 * @return array
	 */
	private static function get_graph_data( $atts ) {

		if ( $atts['form'] ) {
			$data = self::get_data_for_form_graph( $atts );
		} elseif ( isset( $atts['x_axis'] ) ) {
			$data = self::get_data_for_x_axis_graph( $atts );
		} elseif ( ! empty( $atts['fields'] ) && count( $atts['fields'] ) > 1 ) {
			$data = self::get_data_for_multi_field_graph( $atts );
		} else {
			$data = self::get_data_for_single_field_graph( $atts );
		}

		self::filter_graph_data_to_match_graph( $data, $atts );

		self::apply_deprecated_filters();
		$data = apply_filters( 'frm_graph_data', $data, $atts );

		return $data;
	}

	/**
	 * Filter data sent to frontend using the shortcode parameters.
	 *
	 * @param array $data Data to be filtered.
	 * @param array $atts The shortcode attributes.
	 */
	private static function filter_graph_data_to_match_graph( &$data, $atts ) {
		if ( $atts['type'] === 'histogram' || $atts['type'] === 'pie' || $atts['type'] === 'geo' ) {
			return;
		}
		$limits = array();
		$axes   = array( 'x', 'y' );

		foreach ( $axes as $axis ) {
			if ( isset( $atts[ $axis . '_min' ] ) ) {
				$limits[] = 'min';
			}
			if ( isset( $atts[ $axis . '_max' ] ) ) {
				$limits[] = 'max';
			}
			if ( $limits ) {
				self::filter_graph_data_to_match_axis( $data, $atts, $axis, $limits );
			}
			$limits = array();
		}
	}

	/**
	 * Filter graph data along a single axis.
	 *
	 * @param array $data
	 * @param array $atts
	 * @param string $axis
	 * @param array $limits
	 */
	private static function filter_graph_data_to_match_axis( &$data, $atts, $axis, $limits ) {
		$index = $axis === 'x' ? 0 : 1;

		if ( $atts['type'] === 'hbar' ) {
			$index = $index === 0 ? 1 : 0;
		}

		foreach ( $limits as $min_or_max ) {
			$limit = $atts [ $axis . '_' . $min_or_max ];
			$data  = array_values(
				array_filter(
					$data,
					function ( $row ) use ( $index, $limit, $min_or_max ) {
						if ( ! is_numeric( $row[ $index ] ) ) {
							return true;
						}

						return $min_or_max === 'min' ? floatval( $row[ $index ] ) >= $limit : floatval( $row[ $index ] ) <= $limit;
					}
				)
			);
		}
	}

	/**
	 * Get the options for a google graph
	 *
	 * @param string $type
	 * @param array $atts
	 * @return array
	 */
	private static function get_graph_options( $type, $atts ) {
		$options = array();

		self::add_title_options( $atts, $options );

		self::add_legend_options( $atts, $options );

		self::add_tooltip_options( $options );

		self::add_size_options( $atts, $options );

		self::add_color_options( $atts, $options );

		self::add_pie_graph_options( $type, $atts, $options );

		self::add_line_graph_options( $type, $atts, $options );
		self::add_table_options( $type, $atts, $options );

		if ( $type !== 'pie' && $type !== 'geo' ) {

			if ( isset( $atts['is_stacked'] ) && $atts['is_stacked'] ) {
				$options['isStacked'] = true;
			}

			self::add_axis_options( $atts, $options );
		}

		$options = apply_filters(
			'frm_google_chart',
			$options,
			array(
				'atts' => $atts,
				'type' => $type,
			) 
		);

		return $options;
	}

	/**
	 * Get the package type for a given graph type
	 *
	 * @since 2.02.05
	 * @param string $type
	 * @return string
	 */
	private static function get_graph_package( $type ) {
		if ( 'geo' == $type ) {
			$graph_package = 'geochart';
		} elseif ( 'table' == $type ) {
			$graph_package = 'table';
		} else {
			$graph_package = 'corechart';
		}

		return $graph_package;
	}

	/**
	 * Set up the title for a google graph
	 *
	 * @since 2.02.05
	 * @param array $atts
	 * @param array $options
	 */
	private static function add_title_options( $atts, &$options ) {
		// title
		$options['title'] = self::get_graph_title( $atts );

		$options['titleTextStyle'] = array();

		// bold title
		self::convert_shortcode_att_to_bool_google_att( 'title_bold', 'bold', $atts, $options['titleTextStyle'] );

		// Italic title.
		self::convert_shortcode_att_to_bool_google_att( 'title_italic', 'italic', $atts, $options['titleTextStyle'] );

		// title size
		self::convert_shortcode_att_to_google_att( 'title_size', 'fontSize', $atts, $options['titleTextStyle'] );

		// title font
		self::convert_shortcode_att_to_google_att( 'title_font', 'fontName', $atts, $options['titleTextStyle'] );

		// title color
		self::convert_shortcode_att_to_google_att( 'title_color', 'color', $atts, $options['titleTextStyle'] );
	}

	/**
	 * Get the graph title
	 *
	 * @since 2.02.05
	 * @param array $atts
	 * @return string
	 */
	private static function get_graph_title( $atts ) {
		if ( isset( $atts['title'] ) ) {
			// Title defined by user
			$title = $atts['title'];

		} elseif ( isset( $atts['form'] ) && is_object( $atts['form'] ) ) {
			// Title is form name for form graphs
			$title = preg_replace( '/&#?[a-z0-9]{2,8};/i', '', FrmAppHelper::truncate( $atts['form']->name, $atts['truncate'], 0 ) );

		} elseif ( ! empty( $atts['fields'] ) ) {
			// Title is field name if single field, otherwise set to "Submissions"
			if ( count( $atts['fields'] ) > 1 ) {
				$title = __( 'Submissions', 'formidable-pro' );
			} else {
				$first_field = reset( $atts['fields'] );
				$title       = preg_replace( '/&#?[a-z0-9]{2,8};/i', '', FrmAppHelper::truncate( $first_field->name, $atts['truncate'], 0 ) );
			}
		} else {
			// Default to blank
			$title = '';
		}

		return html_entity_decode( $title );
	}

	/**
	 * Convert graph shortcode attributes to google options
	 *
	 * @since 2.02.05
	 * @param string $shortcode_att
	 * @param string $google_att
	 * @param array $atts
	 * @param array $options
	 */
	private static function convert_shortcode_att_to_google_att( $shortcode_att, $google_att, $atts, &$options ) {
		if ( isset( $atts[ $shortcode_att ] ) ) {
			$options[ $google_att ] = $atts[ $shortcode_att ];
		}
	}

	/**
	 * Convert graph shortcode attributes to boolean google options
	 *
	 * @since 2.02.05
	 * @param string $shortcode_att
	 * @param string $google_att
	 * @param array $atts
	 * @param array $options
	 */
	private static function convert_shortcode_att_to_bool_google_att( $shortcode_att, $google_att, $atts, &$options ) {
		if ( isset( $atts[ $shortcode_att ] ) ) {
			if ( $atts[ $shortcode_att ] ) {
				$options[ $google_att ] = true;
			} else {
				$options[ $google_att ] = false;
			}
		}
	}

	/**
	 * Add width, height, and chart area options
	 *
	 * @since 2.02.05
	 * @param array $atts
	 * @param array $options
	 */
	private static function add_size_options( $atts, &$options ) {
		$options['width']  = $atts['width'];
		$options['height'] = $atts['height'];

		if ( isset( $atts['chart_area'] ) ) {
			$chart_area_parts = explode( ',', $atts['chart_area'] );
			foreach ( $chart_area_parts as $chart_area ) {
				$single_item = explode( ':', $chart_area );
				if ( count( $single_item ) !== 2 ) {
					continue;
				}
				$key                          = trim( $single_item[0] );
				$value                        = trim( $single_item[1] );
				$options['chartArea'][ $key ] = $value;
			}
		}
	}

	/**
	 * Add legend options
	 *
	 * @since 2.02.05
	 * @param array $atts
	 * @param array $options
	 */
	private static function add_legend_options( $atts, &$options ) {
		if ( $atts['show_key'] ) {
			$options['legend'] = array();

			// legend size
			if ( isset( $atts['legend_size'] ) ) {
				$options['legend']['textStyle'] = array( 'fontSize' => $atts['legend_size'] );
			} elseif ( is_numeric( $atts['show_key'] ) && $atts['show_key'] >= 10 ) {
				// reverse compatibility with show_key=fontSize
				$options['legend']['textStyle'] = array( 'fontSize' => $atts['show_key'] );
			}

			// legend position
			if ( isset( $atts['legend_position'] ) ) {
				$options['legend']['position'] = $atts['legend_position'];
			} else {
				$options['legend']['position'] = 'right';
			}
		} elseif ( '0' === $atts['show_key'] ) {
			$options['legend'] = 'none';
		} else {
			$options['legend'] = array( 'position' => 'none' );
		}
	}

	/**
	 * Add tooltip options
	 *
	 * @since 2.02.05
	 * @param array $options
	 */
	private static function add_tooltip_options( &$options ) {
		$options['tooltip'] = array( 'isHtml' => true );
	}

	/**
	 * Add color options
	 *
	 * @since 2.02.05
	 * @param array $atts
	 * @param array $options
	 */
	private static function add_color_options( $atts, &$options ) {
		if ( $atts['colors'] ) {
			$options['colors'] = explode( ',', $atts['colors'] );
			$options['colors'] = array_map( 'trim', $options['colors'] );
		}

		$options['backgroundColor'] = $atts['bg_color'];

		// is3D
		self::convert_shortcode_att_to_bool_google_att( 'is3d', 'is3D', $atts, $options );
	}

	/**
	 * Add pie-specific options
	 *
	 * @since 2.02.05
	 * @param string $type
	 * @param array $atts
	 * @param array $options
	 */
	private static function add_pie_graph_options( $type, $atts, &$options ) {
		if ( $type === 'pie' ) {
			self::convert_shortcode_att_to_google_att( 'pie_hole', 'pieHole', $atts, $options );
		}
	}

	/**
	 * Add line graph-specific options
	 *
	 * @since 2.02.05
	 * @param string $type
	 * @param array $atts
	 * @param array $options
	 */
	private static function add_line_graph_options( $type, $atts, &$options ) {
		if ( $type === 'line' ) {
			self::convert_shortcode_att_to_google_att( 'curve_type', 'curveType', $atts, $options );
		}
	}

	/**
	 * @since 3.06.06
	 * @param string $type
	 * @param array $atts
	 * @param array $options
	 */
	private static function add_table_options( $type, $atts, &$options ) {
		if ( $type !== 'table' ) {
			return;
		}

		if ( $atts['pagesize'] ) {
			$options['page']     = 'enable';
			$options['pageSize'] = (int) $atts['pagesize'];
		}

		if ( $atts['sort_column'] >= 0 ) {
			$options['sortColumn'] = (int) $atts['sort_column'];
			if ( isset( $atts['sort_ascending'] ) ) {
				$options['sortAscending'] = (bool) $atts['sort_ascending'];
			}
		}
	}

	/**
	 * Add axis options
	 *
	 * @since 2.02.05
	 * @param array $atts
	 * @param array $options
	 */
	private static function add_axis_options( $atts, &$options ) {
		$options['hAxis'] = self::set_up_x_axis_options( $atts );
		$options['vAxis'] = self::set_up_y_axis_options( $atts );
	}

	/**
	 * Set up the x-axis options
	 *
	 * @since 2.02.05
	 * @param array $atts
	 * @return array
	 */
	private static function set_up_x_axis_options( $atts ) {
		$x_axis = array(
			'titleTextStyle' => array( 'italic' => false ),
			'textStyle'      => array(),
			'gridlines'      => array( 'color' => $atts['x_grid_color'] ),
		);

		// x min and max
		if ( isset( $atts['x_min'] ) || isset( $atts['x_max'] ) ) {
			$x_axis['viewWindow'] = self::add_axis_max_or_min( 'x_min', 'x_max', $atts );
		}

		// x title
		self::convert_shortcode_att_to_google_att( 'x_title', 'title', $atts, $x_axis );

		// showTextEvery
		self::convert_shortcode_att_to_google_att( 'x_show_text_every', 'showTextEvery', $atts, $x_axis );

		// slantedTextAngle
		self::convert_shortcode_att_to_google_att( 'x_text_angle', 'slantedTextAngle', $atts, $x_axis );

		// slantedText
		$x_axis['slantedText'] = (bool) $atts['x_slanted_text'];

		// x-axis title size
		self::convert_shortcode_att_to_google_att( 'x_title_size', 'fontSize', $atts, $x_axis['titleTextStyle'] );

		// x-axis color
		self::convert_shortcode_att_to_google_att( 'x_title_color', 'color', $atts, $x_axis['titleTextStyle'] );

		// x axis labels size
		self::convert_shortcode_att_to_google_att( 'x_labels_size', 'fontSize', $atts, $x_axis['textStyle'] );

		return $x_axis;
	}

	/**
	 * Set up the y-axis options
	 *
	 * @since 2.02.05
	 * @param array $atts
	 * @return array
	 */
	private static function set_up_y_axis_options( $atts ) {
		$y_axis = array(
			'titleTextStyle' => array( 'italic' => false ),
			'gridlines'      => array( 'color' => $atts['grid_color'] ),
			'textStyle'      => array(),
		);

		// y min and max
		if ( isset( $atts['y_min'] ) || isset( $atts['y_max'] ) ) {
			$y_axis['viewWindow'] = self::add_axis_max_or_min( 'y_min', 'y_max', $atts );
		}

		// y-axis title
		self::convert_shortcode_att_to_google_att( 'y_title', 'title', $atts, $y_axis );

		// y-axis title size
		self::convert_shortcode_att_to_google_att( 'y_title_size', 'fontSize', $atts, $y_axis['titleTextStyle'] );

		// y-axis color
		self::convert_shortcode_att_to_google_att( 'y_title_color', 'color', $atts, $y_axis['titleTextStyle'] );

		// y axis labels size
		self::convert_shortcode_att_to_google_att( 'y_labels_size', 'fontSize', $atts, $y_axis['textStyle'] );

		return $y_axis;
	}

	/**
	 * Add min and/or max axis limit
	 *
	 * @since 2.02.05
	 * @param string $min_key
	 * @param string $max_key
	 * @param array $atts
	 * @return array
	 */
	private static function add_axis_max_or_min( $min_key, $max_key, $atts ) {
		$viewWindow = array();

		// add axis minimum
		self::convert_shortcode_att_to_google_att( $min_key, 'min', $atts, $viewWindow );

		// add axis maximum
		self::convert_shortcode_att_to_google_att( $max_key, 'max', $atts, $viewWindow );

		return $viewWindow;
	}

	/**
	 * Get the data for a single field graph
	 *
	 * @since 2.02.05
	 * @param array $atts
	 * @return array
	 */
	private static function get_data_for_single_field_graph( $atts ) {
		$field = reset( $atts['fields'] );

		$field_values = self::get_meta_values_for_single_field( $field, $atts );

		$graph_data = self::format_meta_values_for_single_field( $field, $field_values, $atts );

		if ( ! empty( $graph_data ) ) {
			$graph_label = $field->name;
			$first_row   = array( $graph_label );
			if ( count( reset( $graph_data ) ) > 1 ) {
				$tooltip_text = self::get_tooltip_text( $atts );
				$first_row[]  = $tooltip_text;
			}

			array_unshift( $graph_data, $first_row );

			self::add_user_defined_column_colors( $atts, $graph_data );
		}

		return $graph_data;
	}

	/**
	 * Get the meta values for a single field
	 *
	 * @since 2.02.05
	 * @since 5.0 Make this method public.
	 *
	 * @param object $field
	 * @param array $atts
	 * @return array
	 */
	public static function get_meta_values_for_single_field( $field, $atts ) {
		$atts['form_id'] = $field->form_id;
		self::check_field_filters( $atts );

		// If there are field filters and entry IDs is empty, stop now
		if ( ! empty( $atts['filters'] ) && empty( $atts['entry_ids'] ) ) {
			return array();
		}

		$meta_args = self::package_filtering_arguments_for_query( $atts );

		$field_values = FrmProEntryMeta::get_all_metas_for_field( $field, $meta_args );

		self::clean_field_values( $field, $atts, $field_values );

		return $field_values;
	}

	/**
	 * Package specific filtering arguments in an array
	 *
	 * @since 2.02.05
	 * @param array $atts
	 * @return array
	 */
	private static function package_filtering_arguments_for_query( $atts ) {
		$pass_args = array(
			'entry_ids'               => 'entry_ids',
			'user_id'                 => 'user_id',
			'created_at_greater_than' => 'start_date',
			'created_at_less_than'    => 'end_date',
			'is_draft'                => 'is_draft',
			'form_id'                 => 'form_id',
		);

		$meta_args = array();
		foreach ( $pass_args as $atts_key => $arg_key ) {
			if ( isset( $atts[ $atts_key ] ) ) {
				$meta_args[ $arg_key ] = $atts[ $atts_key ];
			}
		}

		return $meta_args;
	}

	/**
	 * Check all field, created_at, and updated_at filters
	 *
	 * @since 2.02.05
	 * @param array $atts
	 */
	private static function check_field_filters( &$atts ) {
		if ( ! empty( $atts['filters'] ) ) {

			if ( ! isset( $atts['entry_ids'] ) ) {
				$atts['entry_ids']   = array();
				$atts['after_where'] = false;
			} else {
				$atts['after_where'] = true;
			}

			foreach ( $atts['filters'] as $key => $value ) {
				$atts['entry_ids']   = self::get_entry_ids_for_field_filter( $key, $value, $atts );
				$atts['after_where'] = true;

				if ( ! $atts['entry_ids'] ) {
					return;
				}
			}
		}
	}

	/**
	 * Get the entry IDs for a field filter.
	 *
	 * @since 2.02.05
	 * @param string $key
	 * @param string $value
	 * @param array $args
	 * @return array
	 */
	private static function get_entry_ids_for_field_filter( $key, $value, $args ) {
		$pass_args = array(
			'orig_f'      => $key,
			'val'         => $value,
			'entry_ids'   => $args['entry_ids'],
			'after_where' => $args['after_where'],
			'drafts'      => isset( $args['is_draft'] ) ? $args['is_draft'] : 0,
			'form_id'     => $args['form_id'],
			'form_posts'  => self::get_form_posts( $args ),
		);

		return FrmProStatisticsController::get_field_matches( $pass_args );
	}

	/**
	 * Get the entry and attached post ID for all entries that have posts attached
	 *
	 * @since 2.02.05
	 * @param array $args
	 * @return array
	 */
	private static function get_form_posts( $args ) {
		$where_post = array(
			'form_id'   => $args['form_id'],
			'post_id >' => 1,
		);

		if ( isset( $args['is_draft'] ) && $args['is_draft'] !== 'both' ) {
			$where_post['is_draft'] = $args['is_draft'];
		}

		if ( isset( $args['user_id'] ) ) {
			$where_post['user_id'] = $args['user_id'];
		}

		return FrmDb::get_results( 'frm_items', $where_post, 'id,post_id' );
	}

	/**
	 * Format a single field's meta values
	 *
	 * @since 2.02.05
	 * @param object $field
	 * @param array $meta_values
	 * @param array $atts
	 * @return array
	 */
	private static function format_meta_values_for_single_field( $field, $meta_values, $atts ) {
		if ( ! $meta_values ) {
			return array();
		}

		$count_values = array();
		foreach ( $meta_values as $meta ) {
			$meta = self::get_displayed_value( $field, $meta );

			if ( isset( $count_values[ $meta ] ) ) {
				++$count_values[ $meta ];
			} else {
				$count_values[ $meta ] = 1;
			}
		}

		self::order_values_for_single_field_graph( $field, $atts, $count_values );

		// Get slice of array
		if ( isset( $atts['limit'] ) && is_numeric( $atts['limit'] ) ) {
			$count_values = array_slice( $count_values, 0, $atts['limit'] );
		}

		$graph_data      = array();
		$is_numeric_hist = false;
		foreach ( $count_values as $meta_value => $count ) {
			if ( $meta_value === '' ) {
				continue;
			}

			if ( $atts['type'] === 'pie' ) {
				$meta_value = (string) $meta_value;
			} elseif ( $atts['type'] === 'histogram' && is_numeric( $meta_value ) && ( empty( $graph_data ) || $is_numeric_hist ) ) {
				$is_numeric_hist = true;
				$meta_value      = (float) $meta_value;
				for ( $i = 1; $i <= $count; $i++ ) {
					$graph_data[] = array( $meta_value );
				}
				continue;
			}

			$graph_data[] = array( $meta_value, $count );
		}

		return $graph_data;
	}

	/**
	 * Order the values for a single field graph
	 *
	 * @since 2.02.05
	 * @param object $field
	 * @param array $atts
	 * @param array $count_values
	 */
	private static function order_values_for_single_field_graph( $field, $atts, &$count_values ) {
		$order_opts = $atts['x_order'] === 'field_opts' || ( $atts['x_order'] === 'default' && $atts['include_zero'] );
		if ( $order_opts && in_array( $field->type, array( 'radio', 'checkbox', 'select', 'data' ), true ) ) {
			// Sort values by order of field options
			self::sort_data_by_field_options( $field, $atts, $count_values );
		} elseif ( $atts['x_order'] === 'desc' ) {
			// Sort by descending count
			arsort( $count_values );
		} else {
			// Sort alphabetically by default
			ksort( $count_values );
		}
	}

	/**
	 * Get the tooltip text
	 *
	 * @since 2.02.05
	 *
	 * @param array $atts
	 * @return string
	 */
	private static function get_tooltip_text( $atts ) {
		if ( isset( $atts['tooltip_label'] ) ) {
			return $atts['tooltip_label'];
		}

		switch ( $atts['data_type'] ) {
			case 'total':
				return __( 'Total', 'formidable-pro' );
			case 'average':
				return __( 'Average', 'formidable-pro' );
			case 'maximum':
				return __( 'Maximum', 'formidable-pro' );
			case 'minimum':
				return __( 'Minimum', 'formidable-pro' );
		}

		return __( 'Submissions', 'formidable-pro' );
	}

	/**
	 * Get the data for a multi-field graph without an x-axis
	 *
	 * @since 2.02.05
	 * @param array $atts
	 * @return array
	 */
	private static function get_data_for_multi_field_graph( $atts ) {
		$tooltip_text = self::get_tooltip_text( $atts );
		$graph_data   = array( array( __( 'Fields', 'formidable-pro' ), $tooltip_text ) );

		foreach ( $atts['fields'] as $field ) {
			$meta_values = self::get_meta_values_for_single_field( $field, $atts );

			switch ( $atts['data_type'] ) {
				case 'total':
					// Get total.
					$y_value = array_sum( $meta_values );
					break;
				case 'average':
					if ( ! $meta_values ) {
						// Avoid division by zero.
						$y_value = 0;
					} else {
						// Get average.
						$y_value = array_sum( $meta_values ) / count( $meta_values );
					}
					break;
				case 'maximum':
					$y_value = max( $meta_values );
					break;
				case 'minimum':
					$y_value = min( $meta_values );
					break;
				default:
					// Get count.
					$y_value = count( $meta_values );
					break;
			}

			$graph_data[] = array(
				$field->name,
				$y_value,
			);
		}

		self::apply_column_colors( $atts, $graph_data );

		return $graph_data;
	}

	/**
	 * Get the data for a form graph
	 *
	 * @since 2.02.05
	 * @param array $atts
	 * @return array
	 */
	private static function get_data_for_form_graph( $atts ) {
		self::prepare_atts_for_form_graph( $atts );

		if ( ! self::can_continue_with_form_graph( $atts ) ) {
			return array();
		}

		$x_axis_data = self::get_associative_values_for_x_axis( $atts['x_axis_field'], $atts );
		$y_axis_data = $x_axis_data;
		self::order_x_axis_values( $atts, $x_axis_data );

		$graph_data = self::combine_data_by_id( $x_axis_data, array( $y_axis_data ), $atts );

		self::maybe_add_zero_value_dates( $atts, $graph_data );

		self::add_first_row_to_graph_data( $atts, $graph_data );

		return $graph_data;
	}

	/**
	 * Prepare a few items in the $atts array for a form graph
	 *
	 * @since 2.02.05
	 * @param array $atts
	 */
	private static function prepare_atts_for_form_graph( &$atts ) {
		$atts['x_axis']       = 'created_at';
		$atts['x_axis_field'] = $atts['x_axis'];

		if ( ! $atts['include_zero'] ) {
			$atts['include_zero'] = true;
		}

		self::get_default_start_date_for_form_graph( $atts );

		self::get_default_end_date_for_form_graph( $atts );

		self::check_field_filters( $atts );
	}

	/**
	 * Check if the necessary atts are present to continue with a form graph
	 *
	 * @since 2.02.05
	 * @param array $atts
	 * @return bool
	 */
	private static function can_continue_with_form_graph( $atts ) {
		$continue = true;

		if ( ! isset( $atts['form_id'] ) ) {
			// If there is no form
			$continue = false;
		} elseif ( ! empty( $atts['filters'] ) && empty( $atts['entry_ids'] ) ) {
			// If there are field filters and entry IDs is empty, stop now
			$continue = false;
		}

		return $continue;
	}

	/**
	 * Get the default start date for a form graph
	 *
	 * @since 2.02.05
	 * @param array $atts
	 */
	private static function get_default_start_date_for_form_graph( &$atts ) {
		if ( ! isset( $atts['created_at_greater_than'] ) || ! $atts['created_at_greater_than'] ) {
			$group_by = isset( $atts['group_by'] ) ? $atts['group_by'] : '';

			if ( $group_by === 'month' ) {
				$atts['created_at_greater_than'] = '-1 year';
			} elseif ( $group_by === 'quarter' ) {
				$atts['created_at_greater_than'] = '-2 years';
			} elseif ( $group_by === 'year' ) {
				$atts['created_at_greater_than'] = '-10 years';
			} else {
				$atts['created_at_greater_than'] = '-1 month';
			}
		}

		$atts['x_start'] = $atts['created_at_greater_than'];
	}

	/**
	 * Get the default end date for a form graph
	 *
	 * @since 2.02.05
	 * @param array $atts
	 */
	private static function get_default_end_date_for_form_graph( &$atts ) {
		if ( ! isset( $atts['created_at_less_than'] ) || ! $atts['created_at_less_than'] ) {
			$atts['created_at_less_than'] = 'NOW';
		}

		$atts['x_end'] = $atts['created_at_less_than'];
	}

	/**
	 * Add the zero values for all dates between specified start and end date
	 *
	 * @since 2.02.05
	 * @param array $atts
	 * @param array $graph_data
	 */
	private static function maybe_add_zero_value_dates( $atts, &$graph_data ) {
		if ( ! $atts['include_zero'] ) {
			return;
		}

		if ( ! self::is_created_at_or_updated_at( $atts['x_axis_field'] ) && ! self::is_date_field( $atts['x_axis_field'] ) ) {
			return;
		}

		$start_date     = self::get_start_date_for_x_axis_date_include_zero_graph( $atts, $graph_data );
		$end_date       = self::get_end_date_for_x_axis_date_include_zero_graph( $atts, $graph_data );
		$group_by       = isset( $atts['group_by'] ) ? $atts['group_by'] : '';
		$all_dates      = self::get_all_dates_for_period( $start_date, $end_date, $group_by, $atts );
		$new_graph_data = array();
		$count          = 0;

		foreach ( $all_dates as $date_str ) {
			if ( isset( $graph_data[ $count ] ) && $graph_data[ $count ][0] == $date_str ) {
				$new_graph_data[] = $graph_data[ $count ];
				++$count;
			} else {
				$add_row = array( $date_str );
				if ( is_array( $atts['fields'] ) && ! empty( $atts['fields'] ) ) {
					$field_count = count( $atts['fields'] );
					for ( $i = 1; $i <= $field_count; $i++ ) {
						$add_row[] = 0;
					}
				} else {
					$add_row[] = 0;
				}
				$new_graph_data[] = $add_row;
			}
		}

		$graph_data = $new_graph_data;
	}

	/**
	 * Get the start date for a date x-axis when include_zero is set
	 *
	 * @since 2.02.05
	 * @param array $atts
	 * @param array $graph_data
	 * @return string
	 */
	private static function get_start_date_for_x_axis_date_include_zero_graph( $atts, $graph_data ) {
		if ( isset( $atts['x_start'] ) && $atts['x_start'] ) {
			$start_date = $atts['x_start'];
		} else {
			$first_row  = reset( $graph_data );
			$start_date = $first_row[0];
			$start_date = self::convert_formatted_date_to_y_m_d( $start_date );
		}

		return $start_date;
	}

	/**
	 * Get the end date for a date x-axis when include_zero is set
	 *
	 * @param array $atts
	 * @param array $graph_data
	 * @return mixed
	 */
	private static function get_end_date_for_x_axis_date_include_zero_graph( $atts, $graph_data ) {
		if ( isset( $atts['x_end'] ) && $atts['x_end'] ) {
			$end_date = $atts['x_end'];
		} else {
			$final_row = end( $graph_data );
			$end_date  = $final_row[0];
			$end_date  = self::convert_formatted_date_to_y_m_d( $end_date );
		}

		return $end_date;
	}

	/**
	 * Get all dates for a given start and end date
	 *
	 * @since 2.02.05
	 * @param string $start_date
	 * @param string $end_date
	 * @param string $group_by
	 * @param array  $atts
	 *
	 * @return array
	 */
	private static function get_all_dates_for_period( $start_date, $end_date, $group_by, $atts ) {
		$start_timestamp = strtotime( $start_date );
		$end_timestamp   = strtotime( $end_date );
		if ( $end_date === 'NOW' ) {
			// add a day to make sure the data for current date are considered for graph not grouped
			$end_timestamp += 86399;
		}
		$all_dates = array();

		if ( $group_by === 'month' ) {
			for ( $d = $start_timestamp; $d <= $end_timestamp; $d += 60 * 60 * 24 * 25 ) {
				self::add_formatted_date_to_array( 'F Y', $d, $all_dates );
			}
		} elseif ( $group_by === 'quarter' ) {
			for ( $d = $start_timestamp; $d <= $end_timestamp; $d += 60 * 60 * 24 * 80 ) {
				self::add_formatted_date_to_array( 'quarter', $d, $all_dates );
			}
		} elseif ( $group_by === 'year' ) {
			for ( $d = $start_timestamp; $d <= $end_timestamp; $d += 60 * 60 * 24 * 364 ) {
				self::add_formatted_date_to_array( 'Y', $d, $all_dates );
			}
		} else {
			$date_format     = empty( $atts['date_format'] ) ? get_option( 'date_format' ) : $atts['date_format'];
			$start_timestamp = current_time( $start_timestamp );
			for ( $d = $start_timestamp; $d <= $end_timestamp; $d += 60 * 60 * 24 ) {
				$all_dates[] = date_i18n( $date_format, $d, true );
			}
		}

		return $all_dates;
	}

	/**
	 * Add a formatted date to an array
	 *
	 * @since 2.02.05
	 * @param string $format
	 * @param string $date
	 * @param array $all_dates
	 */
	private static function add_formatted_date_to_array( $format, $date, &$all_dates ) {
		if ( 'quarter' === $format ) {
			$date = self::convert_date_to_quarter( $date );
		} else {
			$date = date_i18n( $format, $date, true );
		}

		if ( ! in_array( $date, $all_dates, true ) ) {
			$all_dates[] = $date;
		}
	}

	/**
	 * Get the data for an x-axis graph
	 *
	 * @since 2.02.05
	 * @param array $atts
	 * @return array
	 */
	private static function get_data_for_x_axis_graph( $atts ) {
		self::prepare_atts_for_x_axis_graph( $atts );

		if ( ! self::can_continue_with_x_axis_graph( $atts ) ) {
			return array();
		}

		$x_axis_data = self::get_associative_values_for_x_axis( $atts['x_axis_field'], $atts );

		if ( ! $x_axis_data ) {
			return array();
		}

		self::order_x_axis_values( $atts, $x_axis_data );

		$field_data = self::get_associative_values_for_fields( $atts );

		$graph_data = self::combine_data_by_id( $x_axis_data, $field_data, $atts );

		self::maybe_add_zero_value_dates( $atts, $graph_data );

		self::add_first_row_to_graph_data( $atts, $graph_data );

		self::add_user_defined_column_colors( $atts, $graph_data );

		return $graph_data;
	}

	/**
	 * Prepare the $atts array for an x-axis graph
	 *
	 * @since 2.02.05
	 * @param array $atts
	 */
	private static function prepare_atts_for_x_axis_graph( &$atts ) {
		$atts['x_axis_field'] = self::get_x_axis_field( $atts['x_axis'] );
		if ( ! $atts['x_axis_field'] ) {
			return;
		}

		$form_ids        = wp_list_pluck( $atts['fields'], 'form_id' );
		$atts['form_id'] = $form_ids;
		self::maybe_add_x_start_and_x_end( $atts );

		self::check_field_filters( $atts );
	}

	/**
	 * Add x_start and x_end if start_date and end_date are set
	 *
	 * @since 2.02.05
	 * @param array $atts
	 */
	private static function maybe_add_x_start_and_x_end( &$atts ) {
		if ( self::is_date_field( $atts['x_axis_field'] ) && ! empty( $atts['filters'] ) ) {
			// copy date field filters to x_start and x_end
			foreach ( $atts['filters'] as $filter_key => $filter_value ) {
				if ( strpos( $filter_key, $atts['x_axis_field']->id . '_greater_than' ) !== false ||
					strpos( $filter_key, $atts['x_axis_field']->field_key . '_greater_than' ) !== false
				) {
					$atts['x_start'] = $filter_value;
				} elseif ( strpos( $filter_key, $atts['x_axis_field']->id . '_less_than' ) !== false ||
					strpos( $filter_key, $atts['x_axis_field']->field_key . '_less_than' ) !== false
				) {
					$atts['x_end'] = $filter_value;
				}
			}
		} elseif ( self::is_created_at_or_updated_at( $atts['x_axis_field'] ) ) {
			// copy created_at filters to x_start and x_end
			if ( isset( $atts['created_at_greater_than'] ) ) {
				$atts['x_start'] = $atts['created_at_greater_than'];
			}

			if ( isset( $atts['created_at_less_than'] ) ) {
				$atts['x_end'] = $atts['created_at_less_than'];
			}
		}
	}


	/**
	 * Check if the necessary atts are present to continue with an x-axis graph
	 *
	 * @since 2.02.05
	 * @param array $atts
	 * @return bool
	 */
	private static function can_continue_with_x_axis_graph( $atts ) {
		$continue = true;

		if ( ! $atts['x_axis_field'] ) {
			// If no x-axis field
			$continue = false;
		} elseif ( ! empty( $atts['filters'] ) && empty( $atts['entry_ids'] ) ) {
			// If there are field filters and entry IDs is empty, stop now
			$continue = false;
		}

		return $continue;
	}

	/**
	 * Order x-axis values
	 *
	 * @since 2.02.05
	 * @param array $atts
	 * @param array $x_axis_data
	 */
	private static function order_x_axis_values( $atts, &$x_axis_data ) {
		if ( self::is_created_at_or_updated_at( $atts['x_axis'] ) || self::is_date_field( $atts['x_axis_field'] ) ) {
			usort( $x_axis_data, array( 'FrmProGraphsController', 'date_compare' ) );
		} elseif ( is_object( $atts['x_axis_field'] ) && $atts['x_axis_field']->type === 'number' ) {
			usort( $x_axis_data, array( 'FrmProGraphsController', 'number_compare' ) );
		} elseif ( ! empty( $atts['x_axis_field']->options ) && 'field_opts' === $atts['x_order'] ) {
			$x_axis_data = self::sort_x_axis_data_by_field_options( $atts['x_axis_field'], $x_axis_data );
		} elseif ( 'default' === $atts['x_order'] ) {
			usort( $x_axis_data, array( 'FrmProGraphsController', 'string_compare' ) );
		}
	}

	/**
	 * @param stdClass $field
	 * @param array    $x_axis_data
	 * @return array
	 */
	private static function sort_x_axis_data_by_field_options( $field, $x_axis_data ) {
		if ( empty( $field->options ) ) {
			return;
		}

		$labels       = self::get_field_option_labels( $field );
		$index_by_opt = array_flip( $labels );
		$compare      = function ( $a, $b ) use ( $index_by_opt ) {
			$index_a = $index_by_opt[ $a->meta_value ];
			$index_b = $index_by_opt[ $b->meta_value ];
			return $index_a - $index_b;
		};

		usort( $x_axis_data, $compare );

		return $x_axis_data;
	}

	/**
	 * Compare two dates
	 *
	 * @since 2.02.05
	 * @param stdClass $a
	 * @param stdClass $b
	 * @return int
	 */
	private static function date_compare( $a, $b ) {
		$t1 = strtotime( $a->meta_value );
		$t2 = strtotime( $b->meta_value );
		return $t1 - $t2;
	}

	/**
	 * Compare two numbers as floats
	 *
	 * @since 2.02.05
	 * @param stdClass $a
	 * @param stdClass $b
	 * @return float
	 */
	private static function number_compare( $a, $b ) {
		$n1 = (float) $a->meta_value;
		$n2 = (float) $b->meta_value;
		return $n1 - $n2;
	}

	/**
	 * Compare two strings
	 *
	 * @since 5.0.07
	 * @param stdClass $a
	 * @param stdClass $b
	 * @return int
	 */
	private static function string_compare( $a, $b ) {
		return strcasecmp( $a->meta_value, $b->meta_value );
	}

	/**
	 * Check if string value is created_at or updated_at
	 *
	 * @since 2.02.05
	 * @param object|string $value
	 * @return bool
	 */
	private static function is_created_at_or_updated_at( $value ) {
		return ( is_string( $value ) && in_array( $value, array( 'created_at', 'updated_at' ) ) );
	}

	/**
	 * Check if a variable is an object and has a type of date
	 *
	 * @since 2.02.05
	 * @param mixed $value
	 * @return bool
	 */
	private static function is_date_field( $value ) {
		return ( is_object( $value ) && $value->type === 'date' );
	}

	/**
	 * Get the x axis field object
	 *
	 * @since 2.02.05
	 * @param string $x_axis
	 * @return mixed
	 */
	private static function get_x_axis_field( $x_axis ) {
		if ( self::is_created_at_or_updated_at( $x_axis ) ) {
			$x_axis_field = $x_axis;
		} else {
			$x_axis_field = FrmField::getOne( $x_axis );
		}

		return $x_axis_field;
	}

	/**
	 * Get associative array values for a specific field
	 *
	 * @since 2.02.05
	 * @param array $atts
	 * @return array
	 */
	private static function get_associative_values_for_fields( $atts ) {
		$field_data = array();
		foreach ( $atts['fields'] as $field ) {
			$field_data[] = FrmProEntryMeta::get_associative_array_values_for_field( $field, $atts );
		}

		return $field_data;
	}

	/**
	 * Combine x and y axis data by ID
	 *
	 * @since 2.02.05
	 * @param array $x_axis_data
	 * @param array $field_data
	 * @param array $atts
	 * @return array
	 */
	private static function combine_data_by_id( $x_axis_data, $field_data, $atts ) {
		$graph_data  = array();
		$data_counts = array();

		foreach ( $x_axis_data as $x_data ) {
			$entry_id = $x_data->id;
			$x_value  = self::get_x_axis_displayed_value( $x_data->meta_value, $atts );

			if ( $x_value === '' ) {
				continue;
			}

			if ( isset( $graph_data[ $x_value ] ) ) {
				++$data_counts[ $x_value ];
				self::update_existing_row_of_graph_data( $entry_id, $field_data, $graph_data[ $x_value ], $data_counts[ $x_value ], $atts );
			} else {
				$data_counts[ $x_value ] = 1;
				$graph_data[ $x_value ]  = self::generate_new_row_of_graph_data( $entry_id, $x_value, $field_data, $atts );
			}
		}

		$graph_data = array_values( $graph_data );

		return $graph_data;
	}

	/**
	 * Add the first row of data to a graph
	 *
	 * @since 2.02.05
	 * @param array $atts
	 * @param array $graph_data
	 */
	private static function add_first_row_to_graph_data( $atts, &$graph_data ) {
		if ( $atts['form'] ) {
			$first_row = self::get_first_row_labels_for_form_graph();
			array_unshift( $graph_data, $first_row );
		} elseif ( $atts['x_axis_field'] && ! empty( $atts['fields'] ) ) {
			$first_row = self::get_first_row_labels_for_x_axis_graph( $atts );
			array_unshift( $graph_data, $first_row );
		}
	}

	/**
	 * Get the first row of labels for x-axis graph
	 *
	 * @since 2.02.05
	 * @param array $atts
	 * @return array
	 */
	private static function get_first_row_labels_for_x_axis_graph( $atts ) {
		if ( 'created_at' === $atts['x_axis_field'] ) {
			$x_label = __( 'Creation Date', 'formidable-pro' );
		} elseif ( 'updated_at' === $atts['x_axis_field'] ) {
			$x_label = __( 'Updated At', 'formidable-pro' );
		} elseif ( is_object( $atts['x_axis_field'] ) ) {
			$x_label = $atts['x_axis_field']->name;
		} else {
			$x_label = __( 'Invalid x-axis', 'formidable-pro' );
		}

		$first_row     = array( $x_label );
		$count         = 0;
		$tooltip_label = isset( $atts['tooltip_label'] ) ? explode( ',', $atts['tooltip_label'] ) : array();
		foreach ( $atts['fields'] as $field ) {
			if ( ! empty( $tooltip_label[ $count ] ) ) {
				$first_row[] = $tooltip_label[ $count ];
			} else {
				$first_row[] = $field->name;
			}

			++$count;
		}

		return $first_row;
	}

	/**
	 * Get the first row of labels for a form graph
	 *
	 * @since 2.02.05
	 * @return array
	 */
	private static function get_first_row_labels_for_form_graph() {
		$x_label = __( 'Creation Date', 'formidable-pro' );
		return array( $x_label, __( 'Submissions', 'formidable-pro' ) );
	}

	/**
	 * Create a new row of graph data
	 *
	 * @since 2.02.05
	 * @param int $entry_id
	 * @param string $x_value
	 * @param array $all_field_data
	 * @param array $atts
	 * @return array
	 */
	private static function generate_new_row_of_graph_data( $entry_id, $x_value, $all_field_data, $atts ) {
		self::adjust_x_axis_value_type( $atts, $x_value );

		$new_row = array( $x_value );

		foreach ( $all_field_data as $single_field_data ) {

			if ( isset( $single_field_data[ $entry_id ] ) ) {
				if ( in_array( $atts['data_type'], array( 'total', 'average', 'maximum', 'minimum' ), true ) ) {
					if ( is_numeric( $single_field_data[ $entry_id ]->meta_value ) ) {
						$new_row[] = (float) $single_field_data[ $entry_id ]->meta_value;
					} else {
						$new_row[] = 0;
					}
				} else {
					$new_row[] = 1;
				}
			} else {
				$new_row[] = 0;
			}
		}

		return $new_row;
	}

	/**
	 * Convert number field values to float for x-axis
	 *
	 * @since 2.02.05
	 * @param array $atts
	 * @param string $x_value
	 */
	private static function adjust_x_axis_value_type( $atts, &$x_value ) {
		if ( is_object( $atts['x_axis_field'] ) && $atts['x_axis_field']->type === 'number' ) {
			$x_value = (float) $x_value;
		}
	}


	/**
	 * Update an existing row of graph data
	 *
	 * @since 2.02.05
	 * @param int $entry_id
	 * @param array $all_field_data
	 * @param array $current_data
	 * @param array $data_count
	 * @param array $atts
	 */
	private static function update_existing_row_of_graph_data( $entry_id, $all_field_data, &$current_data, $data_count, $atts ) {
		$count = 0;
		foreach ( $all_field_data as $single_field_data ) {
			++$count;

			if ( isset( $single_field_data[ $entry_id ] ) ) {
				$value      = $single_field_data[ $entry_id ]->meta_value;
				$is_numeric = is_numeric( $value );

				switch ( $atts['data_type'] ) {
					case 'total':
						if ( $is_numeric ) {
							$current_data[ $count ] += $value;
						}
						break;
					case 'average':
						if ( $is_numeric ) {
							$current_data[ $count ] = ( ( $current_data[ $count ] * ( $data_count - 1 ) ) + $value ) / $data_count;
						}
						break;
					case 'maximum':
						if ( $is_numeric && $value > $current_data[ $count ] ) {
							$current_data[ $count ] = floatval( $value );
						}
						break;
					case 'minimum':
						if ( $is_numeric && $value < $current_data[ $count ] ) {
							$current_data[ $count ] = floatval( $value );
						}
						break;
					default:
						++$current_data[ $count ];
						break;
				}
			}
		}
	}

	/**
	 * Get the associative values for the x-axis field
	 *
	 * @since 2.02.05
	 * @param object|string $x_axis_field
	 * @param array $atts
	 * @return array
	 */
	private static function get_associative_values_for_x_axis( $x_axis_field, $atts ) {
		if ( ! $x_axis_field ) {
			$x_axis_data = array();
		} elseif ( self::is_created_at_or_updated_at( $x_axis_field ) ) {
			$query_args  = self::package_filtering_arguments_for_query( $atts );
			$x_axis_data = FrmProEntryMeta::get_associative_array_values_for_frm_items_column( $x_axis_field, $query_args );
		} else {
			$query_args  = self::package_filtering_arguments_for_query( $atts );
			$x_axis_data = FrmProEntryMeta::get_associative_array_values_for_field( $x_axis_field, $query_args );
		}

		return $x_axis_data;
	}

	/**
	 * Get the displayed x-axis value
	 *
	 * @since 2.02.05
	 * @param string $x_value
	 * @param array $atts
	 * @return string
	 */
	private static function get_x_axis_displayed_value( $x_value, $atts ) {
		self::convert_db_date_to_localized_date( $atts, $x_value );

		if ( ! empty( $atts['date_format'] ) && self::is_valid_date( $x_value ) ) {
			$x_value = date_i18n( $atts['date_format'], strtotime( $x_value ) );
		} elseif ( isset( $atts['group_by'] ) ) {
			if ( ! self::is_valid_date( $x_value ) ) {
				return '';
			}

			if ( $atts['group_by'] === 'month' ) {
				$x_value = date_i18n( 'F Y', strtotime( $x_value ), true );
			} elseif ( $atts['group_by'] === 'quarter' ) {
				$x_value = self::convert_date_to_quarter( $x_value );
			} elseif ( $atts['group_by'] === 'year' ) {
				$x_value = gmdate( 'Y', strtotime( $x_value ) );
			} else {
				$x_value = self::get_displayed_value( $atts['x_axis_field'], $x_value );
			}
		} else {
			$x_value = self::get_displayed_value( $atts['x_axis_field'], $x_value );
		}

		return $x_value;
	}

	/**
	 * Convert a creation date or update date to the localized date
	 *
	 * @since 2.02.05
	 * @param array $atts
	 * @param string $x_value
	 */
	private static function convert_db_date_to_localized_date( $atts, &$x_value ) {
		if ( self::is_created_at_or_updated_at( $atts['x_axis'] ) ) {
			$x_value = FrmAppHelper::get_localized_date( 'Y-m-d H:i:s', $x_value );
		}
	}

	/**
	 * Check if a value is a valid date
	 *
	 * @since 2.02.05
	 * @param string $value
	 * @return bool
	 */
	private static function is_valid_date( $value ) {
		return ( gmdate( 'Y', strtotime( $value ) ) > 0 );
	}

	/**
	 * Convert a date to the correct quarter string
	 *
	 * @since 2.02.05
	 * @param string $date
	 * @return string
	 */
	private static function convert_date_to_quarter( $date ) {
		$value = gmdate( 'Y-m-d', strtotime( $date ) );
		$y     = gmdate( 'Y', strtotime( $value ) );

		if ( preg_match( '/-(01|02|03)-/', $value ) ) {
			$value = __( 'Q1', 'formidable-pro' ) . ' ' . $y;
		} elseif ( preg_match( '/-(04|05|06)-/', $value ) ) {
			$value = __( 'Q2', 'formidable-pro' ) . ' ' . $y;
		} elseif ( preg_match( '/-(07|08|09)-/', $value ) ) {
			$value = __( 'Q3', 'formidable-pro' ) . ' ' . $y;
		} elseif ( preg_match( '/-(10|11|12)-/', $value ) ) {
			$value = __( 'Q4', 'formidable-pro' ) . ' ' . $y;
		}

		return $value;
	}

	/**
	 * Get the displayed value for a given field and meta value
	 *
	 * @since 2.02.05
	 * @param object|string $field
	 * @param string $value
	 * @return int|string
	 */
	private static function get_displayed_value( $field, $value ) {
		if ( self::is_created_at_or_updated_at( $field ) ) {

			$displayed_value = self::convert_date_for_graph_display( $value );

		} elseif ( is_object( $field ) ) {
			$displayed_value = self::get_displayed_value_for_specific_fields( $value, $field );
		} else {
			$displayed_value = $value;
		}

		$displayed_value = apply_filters( 'frm_graph_value', $displayed_value, $field );

		if ( is_array( $displayed_value ) ) {
			$displayed_value = reset( $displayed_value );
		}

		return $displayed_value;
	}

	/**
	 * Gets displayed value for specific fields.
	 *
	 * @since 5.4.2
	 *
	 * @param mixed  $value Field value.
	 * @param object $field Field object.
	 */
	private static function get_displayed_value_for_specific_fields( $value, $field ) {
		if ( ! is_array( $value ) ) {
			if ( $field->type === 'date' ) {
				$displayed_value = self::convert_date_for_graph_display( $value );
			} elseif ( ! empty( $field->field_options['separate_value'] ) || FrmField::is_option_true( $field, 'other' ) ) {
				$displayed_value = self::get_option_label_for_value( $field, $value );
			} elseif ( $field->type === 'user_id' ) {
				$displayed_value = FrmFieldsHelper::get_user_display_name( $value, 'display_name' );
			} elseif ( $field->type === 'data' && $field->field_options['form_select'] !== 'taxonomy' ) {
				$displayed_value = FrmFieldsHelper::get_unfiltered_display_value( compact( 'value', 'field' ) );
			} elseif ( FrmField::is_option_true_in_array( $field->field_options, 'post_field' ) && $field->field_options['post_field'] === 'post_category' && $field->field_options['taxonomy'] ) {
				$displayed_value = FrmProPost::get_taxonomy_term_name_from_id( $value, $field->field_options['taxonomy'] );
			} elseif ( is_numeric( $value ) ) {
				$displayed_value = $value;
			} elseif ( $field->type === 'textarea' || $field->type === 'rte' ) {
				$displayed_value = strip_tags( $value );
			} else {
				$displayed_value = ucfirst( $value );
			}
		} elseif ( 'name' === $field->type ) {
			$field_obj       = FrmFieldFactory::get_field_object( $field );
			$displayed_value = $field_obj->get_display_value( $value );
		} else {
			$displayed_value = $value;
		}

		return $displayed_value;
	}

	/**
	 * Convert a date to the WordPress format
	 *
	 * @since 2.02.05
	 * @param string $value
	 * @return string
	 */
	private static function convert_date_for_graph_display( $value ) {
		if ( self::is_valid_date( $value ) ) {
			$date_format = get_option( 'date_format' );
			$value       = date_i18n( $date_format, strtotime( $value ), true );
		} else {
			$value = '';
		}

		return $value;
	}

	/**
	 * Convert a date formatted in the WordPress settings date format to Y-m-d
	 *
	 * @since 2.02.11
	 * @param string $date
	 * @return string
	 */
	private static function convert_formatted_date_to_y_m_d( $date ) {
		$date_format = get_option( 'date_format' );
		$date_obj    = DateTime::createFromFormat( $date_format, $date );

		if ( $date_obj ) {
			return $date_obj->format( 'Y-m-d' );
		}

		return $date;
	}

	/**
	 * Get the option label for a given value
	 *
	 * @since 2.02.05
	 *
	 * @param object $field
	 * @param string $value
	 * @return string
	 */
	private static function get_option_label_for_value( $field, $value ) {
		$option_label = $value;

		foreach ( $field->options as $opt_key => $opt ) {
			if ( ! $opt ) {
				continue;
			}

			if ( $value === $opt ) {
				$option_label = $opt;
				break;
			}
			if ( is_array( $opt ) && $value == $opt['value'] ) {
				$option_label = $opt['label'];
				break;
			}
			if ( FrmFieldsHelper::is_other_opt( $opt_key ) ) {
				if ( FrmField::is_field_with_multiple_values( $field ) ) {
					if ( $opt_key == $value ) {
						$option_label = $opt;
						break;
					}
				} else {
					$option_label = $opt;
				}
			}
		}

		return $option_label;
	}

	/**
	 * Strip slashes and get rid of multi-dimensional arrays in inputs
	 *
	 * @since 2.0
	 *
	 * @param object $field
	 * @param array $atts
	 * @param array $field_values
	 */
	private static function clean_field_values( $field, $atts, &$field_values ) {
		if ( ! $field_values ) {
			return;
		}

		// Flatten multi-dimensional array
		if ( count( $atts['fields'] ) === 1 && FrmField::is_field_with_multiple_values( $field ) ) {
			FrmProStatisticsController::flatten_multi_dimensional_arrays_for_stats( $field, true, $field_values );
		}

		$field_values = wp_unslash( $field_values );
	}

	/**
	 * Order values so they match the field options order
	 *
	 * @since 2.0
	 *
	 * @param stdClass $field
	 * @param array    $atts
	 * @param array    $count_values
	 */
	private static function sort_data_by_field_options( $field, $atts, &$count_values ) {
		if ( empty( $field->options ) ) {
			return;
		}

		$lower_case_keys = array();
		foreach ( $count_values as $key => $count ) {
			$lower_case_key                     = strtolower( $key );
			$lower_case_keys[ $lower_case_key ] = $key;
		}

		$ordered_values = array();
		$labels         = self::get_field_option_labels( $field );

		foreach ( $labels as $label ) {
			if ( array_key_exists( strtolower( $label ), $lower_case_keys ) ) {
				$key                    = $lower_case_keys[ strtolower( $label ) ];
				$ordered_values[ $key ] = $count_values[ $key ];
			} elseif ( $atts['include_zero'] ) {
				$ordered_values[ $label ] = 0;
			}
		}

		$count_values = $ordered_values;
	}

	/**
	 * @param stdClass $field
	 * @return array
	 */
	private static function get_field_option_labels( $field ) {
		$labels = array();
		foreach ( $field->options as $opt ) {
			if ( ! $opt ) {
				continue;
			}

			if ( is_array( $opt ) ) {
				if ( empty( $opt['label'] ) ) {
					continue;
				}
				$opt = $opt['label'];
			}

			$labels[] = $opt;
		}
		return $labels;
	}

	/**
	 * Only add column colors if colors is defined by the user
	 *
	 * @since 2.02.05
	 * @param array $atts
	 * @param array $graph_data
	 */
	private static function add_user_defined_column_colors( $atts, &$graph_data ) {
		if ( ! isset( $atts['x_axis'] ) && $atts['colors'] !== self::get_default_colors() ) {
			self::apply_column_colors( $atts, $graph_data );
		}
	}

	/**
	 * If colors is not empty, apply a different color to each bar
	 *
	 * @since 2.02.05
	 * @param array $atts
	 * @param array $graph_data
	 */
	private static function apply_column_colors( $atts, &$graph_data ) {
		if ( ! empty( $atts['colors'] ) && in_array( $atts['type'], array( 'column', 'bar', 'hbar', 'scatter' ) ) ) {

			$colors            = explode( ',', $atts['colors'] );
			$color_upper_limit = count( $colors ) - 1;
			$count             = -1;

			foreach ( $graph_data as $key => $item ) {
				if ( $count < 0 ) {
					$graph_data[ $key ][] = array( 'role' => 'style' );
				} else {
					$graph_data[ $key ][] = $colors[ $count ];
				}

				if ( $count < $color_upper_limit ) {
					++$count;
				} else {
					$count = 0;
				}
			}
		}
	}

	/**
	 * Show the graphs on the form's Reports page
	 *
	 * @since 2.02.05
	 *
	 * @return void
	 */
	public static function show_reports() {
		FrmProReportsHelper::show_reports();
	}

	/**
	 * Get a list of boxes to list with the graph on the reports page.
	 *
	 * @since 5.0.02
	 * @deprecated 6.5.5
	 */
	public static function get_field_boxes( $args ) {
		_deprecated_function( __METHOD__, '6.5.5', 'FrmProReportsHelper::get_field_boxes' );
		return FrmProReportsHelper::get_field_boxes( $args );
	}

	/**
	 * Which field types show a graph by default?
	 *
	 * @since 4.0
	 */
	public static function table_graph_types() {
		/**
		 * Allows modifying table graph types.
		 *
		 * @since 5.0
		 *
		 * @param array $types Table graph types.
		 */
		return apply_filters( 'frm_table_graph_types', array( 'url', 'text', 'textarea', 'rte', 'email' ) );
	}

	/**
	 * Apply deprecated filters
	 *
	 * @since 2.02.05
	 * @codeCoverageIgnore
	 */
	private static function apply_deprecated_filters() {
		$placeholder = array();

		apply_filters( 'frm_graph_values', $placeholder );
		if ( has_filter( 'frm_graph_values' ) ) {
			_deprecated_function( 'The frm_graph_values filter', '2.02.05', 'the frm_graph_data filter' );
		}

		apply_filters( 'frm_graph_labels', $placeholder );
		if ( has_filter( 'frm_graph_labels' ) ) {
			_deprecated_function( 'The frm_graph_labels filter', '2.02.05', 'the frm_graph_data filter' );
		}

		apply_filters( 'frm_final_graph_values', $placeholder );
		if ( has_filter( 'frm_final_graph_values' ) ) {
			_deprecated_function( 'The frm_final_graph_values filter', '2.02.05', 'the frm_graph_data filter' );
		}
	}
}
