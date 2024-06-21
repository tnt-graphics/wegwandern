<?php
/**
 * The template for displaying search results pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#search-result
 *
 * @package Wegwandern
 */

get_header();

global $query_string; 
global $wp_query;

$posts_per_page = 12;

wp_parse_str( $query_string, $search_query );
$post_type = '';
$search_result = '';
$offset = 12;

if( isset($_POST['s']) ){
    $post_type = 'wanderung';
    $search_query['post_type'] = $post_type;
}
if( isset($_GET['s']) ){
    $searchable_post_types = get_post_types( array( 'exclude_from_search' => false ) );
    $search_query['post_type'] =  $searchable_post_types;
}

$search_query['posts_per_page'] = -1;
$total_posts = get_posts( $search_query );
$total_results = count($total_posts);

if( isset( $_REQUEST['s'] ) && ! empty( $_REQUEST['s'] ) ){  
   $search_query['posts_per_page'] = $posts_per_page;
   $posts = get_posts( $search_query );
}
?>

<div <?php post_class("container"); ?> >
          
        <?php echo get_breadcrumb(); ?>

        <h1 class="page-title"> <?php _e( 'Suche', 'wegwandern' ); ?><?php //echo get_the_title(); ?> </h1>
        <div class="searchResultContainer">
            <div class="searchResultSection">
                 <div class="searchinputFieldWrapper">
                    <form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
                        <div class="searchinputField">
                            <span class="searchResult_filter_search-icon"></span>
                            <input type="text" class="" placeholder="<?php echo esc_attr_x( 'Suche', 'placeholder', 'wegwandern' ); ?>" value="<?php echo get_search_query(); ?>" name="s" />
                            <span class="searchResult_search_close hide"></span>
                        </div>
                    </form>
                </div>
                <div class="searchResult_list"><?php
                    $count = 1;
                    if( !empty($posts) ){
                        global $post;
                        foreach($posts as $post){
                            setup_postdata($post);
                            $id = $post->ID;
                        
                            /**
                             * Run the loop for the search to output the results.
                             * If you want to overload this in a child theme then include a file
                             * called content-search.php and that will be used instead.
                             */
                            get_template_part( 'template-parts/content', 'search', array( 'count' => $count, 'id' => $id ) );
                            $count++;
                        }
                        wp_reset_postdata();
                       
                    }else{
                        ?><h2 class="noWanderung"> <?php _e( 'Keine Blogs gefunden', 'wegwandern' ); ?></h2><?php
                    }?>

                </div>
            </div>
            <div class="searchResultAdContainer single-hike-right">
                <?php wegwandern_ad_section_display('right', true, false, false ); ?>
            </div>
        </div>
        <?php if( !empty( $posts ) && ( $total_results > $posts_per_page ) ) {?>
        <div class="LoadMore" id="search-loadmore" data-count = "<?php echo $total_results; ?>" data-query="<?php echo $search_query['s'];?>" data-offset = "<?php echo $offset;?>" data-nonce="<?php echo wp_create_nonce('search_nonce');?>" data-postType="<?php echo $post_type; ?>" data-searchIds="<?php echo $search_ids; ?>" >

            <input type="hidden" name="search_ids" value="">

            <span class="LoadMoreIcon"></span>

            <span class="LoadMoreText" id='searchLoadMoreText'><?php echo __( 'Weitere Suchergebnisse', 'wegwandern' ); ?> </span>

        </div>
		<div id="loader-icon" class="hide"></div>
        <div class="noWanderungSearchPost" style="display:none;" >
            <h2 class="noWanderung"> <?php _e( 'Keine Einträge gefunden.', 'wegwandern' ); ?></h2>
        </div>
        <?php } ?>
	 
</div>

<?php
// get_sidebar();
get_footer();
