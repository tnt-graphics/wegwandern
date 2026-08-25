<?php
/**
 * Template for Accordion
 */
if ( ! empty( $is_preview ) ) {
	$accordion = get_field( 'accordion' );
	$titles    = array();
	if ( is_array( $accordion ) ) {
		foreach ( $accordion as $acc ) {
			if ( ! empty( $acc['accordion_title'] ) ) {
				$titles[] = $acc['accordion_title'];
			}
		}
	}
	$lines = $titles ? array( implode( ', ', $titles ) ) : array();
	wegw_acf_block_editor_card( __( 'Accordion', 'wegwandern' ), $lines );
	return;
}
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
