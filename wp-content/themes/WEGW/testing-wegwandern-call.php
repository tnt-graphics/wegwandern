<?php
/*
Template Name: testing speed
*/

//get_header(); // Include header template

// Your custom HTML/PHP content for the page
get_header();

$args = array(
	'post_type'      => 'wanderung', // Replace 'wanderung' with your custom post type name
	'posts_per_page' => -1, // Retrieve 20 posts
);

$query = new WP_Query($args);

if ($query->have_posts()) {
	while ($query->have_posts()) { ?>
		<div style="display: block; clear: both">
			<?php
		$query->the_post();
		// Display the featured image
		the_title();
	
		if (has_post_thumbnail()) {
			the_post_thumbnail('thumbnail'); // Display the featured image
		} else {
			// You can add a default image or placeholder here if no featured image is set
			echo 'No featured image available';
		}
		?>
		</div>
		<?php
	}
	wp_reset_postdata(); // Reset post data
} else {
	// No posts found
}

get_footer();


?> 
