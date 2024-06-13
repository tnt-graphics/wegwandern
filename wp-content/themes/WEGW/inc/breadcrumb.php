<?php
/**
 * Breadcrumb
 */
function get_breadcrumb() {
	$breadcrumbs = get_field( 'disable_breadcrumbs', get_the_ID() );
	$no_class    = '';
	if ( $breadcrumbs ) {
		$no_class = 'hideCrumb';
	}

	$here_text   = __( 'You are currently here!' );
	$home_link   = home_url( '/' );
	$home_text   = __( 'Home', 'wegwandern' );
	$link_before = '<li typeof="v:Breadcrumb">&nbsp;»&nbsp;';
	$link_after  = '</li>';
	$link_attr   = ' rel="v:url" property="v:title"';
	$link        = $link_before . '<a' . $link_attr . ' href="%1$s">%2$s</a>' . $link_after;
	/* Delimiter between crumbs */
	$delimiter = ' ';
	/* Tag before the current crumb */
	$before = '<li class="current">&nbsp;»&nbsp;';
	/* Tag after the current crumb */
	$after            = '</li>';
	/* Adds the page number if the query is paged */
	$page_addon       = '';
	$breadcrumb_trail = '';
	$category_links   = '';

	/**
	 * Set our own $wp_the_query variable. Do not use the global variable version due to
	 * reliability
	 */
	$wp_the_query   = $GLOBALS['wp_the_query'];
	$queried_object = $wp_the_query->get_queried_object();

	/* Handle single post requests which includes single pages, posts and attatchments */
	if ( is_singular() ) {
		/**
		 * Set our own $post variable. Do not use the global variable version due to
		 * reliability. We will set $post_object variable to $GLOBALS['wp_the_query']
		 */
		$post_object = sanitize_post( $queried_object );

		/* Set variables */
		$title          = apply_filters( 'the_title', $post_object->post_title );
		$parent         = $post_object->post_parent;
		$post_type      = $post_object->post_type;
		$post_id        = $post_object->ID;
		$post_link      = $before . $title . $after;
		$parent_string  = '';
		$post_type_link = '';

		if ( 'post' === $post_type ) {

			/* Get the post categories */
			$categories = get_the_category( $post_id );
			if ( $categories ) {
				/* Lets grab the first category */
				$category       = $categories[0];
				$category_links = get_category_parents( $category, true, $delimiter );
				$category_links = str_replace( '<a', $link_before . '<a' . $link_attr, $category_links );
				$category_links = str_replace( '</a>', '</a>' . $link_after, $category_links );
			}

		}

		if ( ! in_array( $post_type, array( 'post', 'page', 'attachment' ) ) ) {
			$post_type_object = get_post_type_object( $post_type );
			$archive_link     = esc_url( get_post_type_archive_link( $post_type ) );
			$post_type_link   = sprintf( $link, $archive_link, $post_type_object->labels->singular_name );
		}

		/* Get post parents if $parent !== 0 */
		if ( 0 !== $parent ) {
			$parent_links = array();
			while ( $parent ) {
				$post_parent    = get_post( $parent );
				$parent_links[] = sprintf( $link, esc_url( get_permalink( $post_parent->ID ) ), get_the_title( $post_parent->ID ) );
				$parent         = $post_parent->post_parent;
			}

			$parent_links  = array_reverse( $parent_links );
			$parent_string = implode( $delimiter, $parent_links );
		}

		/* Lets build the breadcrumb trail */
		if ( $parent_string ) {
			$breadcrumb_trail = $parent_string . $delimiter . $post_link;
		} else {
			$breadcrumb_trail = $post_link;
		}

		if ( $post_type_link ) {
			$breadcrumb_trail = $post_type_link . $delimiter . $breadcrumb_trail;
		}

		if ( $category_links ) {
			$breadcrumb_trail = $category_links . $breadcrumb_trail;
		}
	}
	
	/* Handle the search page */
	//$breadcrumb_trail = __( 'Search query for: ' ) . $before . get_search_query() . $after;
	if ( is_search() ) {
		
		$breadcrumb_trail = $before . __('Suche');
	}

	/* Handle 404's */
	if ( is_404() ) {
		$breadcrumb_trail = $before . __( 'Error 404' ) . $after;
	}

	/* Handle paged pages */
	if ( is_paged() ) {
		$current_page = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : get_query_var( 'page' );
		$page_addon   = $before . sprintf( __( ' ( Page %s )' ), number_format_i18n( $current_page ) ) . $after;
	}

	$breadcrumb_output_link  = '';
	$breadcrumb_output_link .= '<ul class="breadcrumb ' . $no_class . '">';

	if ( is_home() || is_front_page() ) {
		/* Do not show breadcrumbs on page one of home and frontpage */
		if ( is_paged() ) {
			$breadcrumb_output_link .= '<li><a href="' . $home_link . '">' . $home_text . '</a></li>';
			$breadcrumb_output_link .= $page_addon;
		}
		if ( is_home() ) { 
			$breadcrumb_output_link .= '<li><a href="' . $home_link . '">' . $home_text . '</a></li>';
			$post_object = sanitize_post( $queried_object );
			$title          = apply_filters( 'the_title', $post_object->post_title );
			$page_addon   = $before . $title . $after;
			$breadcrumb_output_link .= $page_addon;
		}
	} else { 
		$breadcrumb_output_link .= '<li><a href="' . $home_link . '" rel="v:url" property="v:title">' . $home_text . '</a></li>';
		if ( is_archive() ) {
			$post_object = sanitize_post( $queried_object );
			if ( isset( $post_object->rewrite['slug'] ) ) {
				$title = ucfirst( $post_object->rewrite['slug'] );
			} else {
				$title = $post_object->name;
			}
			$page_addon  = $before . $title . $after;
		}
		$breadcrumb_output_link .= $delimiter;
		$breadcrumb_output_link .= $breadcrumb_trail;
		$breadcrumb_output_link .= $page_addon;
	}

	$breadcrumb_output_link .= '</ul><!-- .breadcrumbs -->';
	return $breadcrumb_output_link;
}
