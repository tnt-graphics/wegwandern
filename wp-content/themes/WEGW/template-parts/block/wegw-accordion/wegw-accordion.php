<?php
/**
 * Template for Accordion
 */
global $post;
$accordion = get_field( 'accordion' );

if ( ! empty( $accordion ) ) {
	?>
	<div class="acc-wrap">
		<?php
		foreach ( $accordion as $acc ) {
			$accordion_title       = get_sub_field( 'accordion_title' );
			$accordion_description = get_sub_field( 'accordion_description' );
			?>
		<div class="accordion"><?php echo $acc['accordion_title']; ?></div>
		<div class="acc-decs panel"><?php echo $acc['accordion_description']; ?></div>
		<?php } ?>
	</div>
	<?php
}
