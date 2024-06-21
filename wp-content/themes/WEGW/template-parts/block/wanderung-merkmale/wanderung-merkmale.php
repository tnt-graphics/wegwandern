<?php
/**
 * Wanderung merkmale.
 */
global $post;

$wanderung_id = $post->ID;
$merkmale     = array();
$merkmales    = array();
$merks        = '';
$html         = '';

$aktivitat      = get_the_terms( $wanderung_id, 'aktivitat' );
$wanderregionen = get_the_terms( $wanderung_id, 'wanderregionen' );
$angebot        = get_the_terms( $wanderung_id, 'angebot' );
$routenverlauf  = get_the_terms( $wanderung_id, 'routenverlauf' );
$thema          = get_the_terms( $wanderung_id, 'thema' );

if ( ! empty( $aktivitat ) ) {
	foreach ( $aktivitat as $aktivitat ) {
			$merkmales[] = array(
				'name' => $aktivitat->name,
				'url'  => get_term_link( $aktivitat ),
			);
	}
}

if ( ! empty( $wanderregionen ) ) {
	foreach ( $wanderregionen as $wanderregionen ) {
		$merkmales[] = array(
			'name' => $wanderregionen->name,
			'url'  => get_term_link( $wanderregionen ),
		);
	}
}

if ( ! empty( $angebot ) ) {
	foreach ( $angebot as $angebot ) {
		$merkmales[] = array(
			'name' => $angebot->name,
			'url'  => get_term_link( $angebot ),
		);
	}
}

if ( ! empty( $routenverlauf ) ) {
	foreach ( $routenverlauf as $routenverlauf ) {
		$merkmales[] = array(
			'name' => $routenverlauf->name,
			'url'  => get_term_link( $routenverlauf ),
		);
	}
}

if ( ! empty( $thema ) ) {
	foreach ( $thema as $thema ) {
		$merkmales[] = array(
			'name' => $thema->name,
			'url'  => get_term_link( $thema ),
		);
	}
}

if ( ! empty( $merkmales ) ) {
	foreach ( $merkmales as $merkmale ) {
		$merks .= '<li><a href="' . $merkmale['url'] . '">' . $merkmale['name'] . '</a></li>';
	}
}

if ( ! empty( $merkmale ) ) {
	$html = '<div class="merkmale-outer">
            <h3>' . __( 'Merkmale', 'wegwandern' ) . '</h3>
            <ul>
            ' . $merks . '
            </ul>
         </div>';
}
echo $html;
