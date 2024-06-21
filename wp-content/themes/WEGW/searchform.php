<?php
/**
 * Template for displaying search forms in wegwandern
 *
 * @package WordPress
 * @subpackage wegwandern
 * @since 1.0
 * @version 1.0
 */
?>

<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
<h3><?php echo esc_attr_x( 'WegWandern.ch durchsuchen', 'placeholder', 'wegwandern' ); ?></h3>
			<div class="navigation_search search">
<span class="filter_search-icon"></span>
		<input type="text" class="" placeholder="<?php echo esc_attr_x( 'Suche', 'placeholder', 'wegwandern' ); ?>" value="<?php echo get_search_query(); ?>" name="s" />

		<span class="navigation_search_close hide"></span>
</div>
</form>