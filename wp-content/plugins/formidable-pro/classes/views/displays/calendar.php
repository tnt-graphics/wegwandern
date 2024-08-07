<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

_deprecated_file( basename( __FILE__ ), '4.09', null, 'This file can be found in formidable-views/classes/views/displays/calendar.php' );

for ( $i = $week_begins; $i < ( $maxday + $startday ); $i++ ) {
    $pos = $i % 7;
    $end_tr = false;
	if ( $pos == $week_begins ) {
		echo "<tr>\n";
	}

    $day = $i - $startday + 1;

    //add classes for the day
    $day_class = '';

    //check for today
	if ( isset( $today ) && $day == $today ) {
        $day_class .= ' frmcal-today';
	}

	if ( $pos == 0 || $pos == 6 ) {
        $day_class .= ' frmcal-week-end';
	}

?>
<td <?php echo ( ! empty( $day_class ) ) ? 'class="' . esc_attr( $day_class ) . '"' : ''; ?>><div class="frmcal_date">
		<div class="frmcal_day_name"><?php
			echo isset( $day_names[ $i ] ) ? esc_html( $day_names[ $i ] ) . ' ' : '';
		?></div><?php
	unset($day_class);

	if ( $i >= $startday ) {
        ?><div class="frmcal_num"><?php echo esc_html( $day ); ?></div></div> <div class="frmcal-content">
<?php
		if ( ! empty( $daily_entries[ $i ] ) ) {

			//Set up current entry date for [event_date] shortcode
			$current_entry_date = $year . '-' . $month . '-' . ( $day < 10 ? '0' . $day : $day );

			$pass_atts = array(
				'event_date' => $current_entry_date,
				'day_count'  => count( $daily_entries[ $i ] ),
				'view'       => $view,
			);
			do_action( 'frm_before_day_content', $pass_atts );

			$count = 0;
			foreach ( $daily_entries[ $i ] as $entry ) {
				$count++;

				if ( isset( $used_entries[ $entry->id ] ) ) {
					$this_content = $used_entries[ $entry->id ];
					$this_content = apply_filters( 'frm_display_entry_content', $this_content, $entry, $shortcodes, $view, 'all', '', array(
						'event_date' => $current_entry_date,
					) );
					$this_content = FrmProContent::replace_calendar_date_shortcode( $this_content, $current_entry_date );
					FrmProContent::replace_entry_position_shortcode( compact( 'entry', 'view' ), compact( 'count' ), $this_content );
					echo '<div class="frm_cal_multi_' . esc_attr( $entry->id ) . '">' . $this_content . '</div>';
				} else {
					// switch [event_date] to [calendar_date] so it can be replaced on each individual date instead of each entry
					$new_content = str_replace( array( '[event_date]', '[event_date ' ), array( '[calendar_date]', '[calendar_date ' ), $new_content );
					$this_content = apply_filters( 'frm_display_entry_content', $new_content, $entry, $shortcodes, $view, 'all', '', array(
						'event_date' => $current_entry_date,
					) );

					$used_entries[ $entry->id ] = $this_content;
					FrmProContent::replace_entry_position_shortcode( compact( 'entry', 'view' ), compact( 'count' ), $this_content );
					echo FrmProContent::replace_calendar_date_shortcode( $this_content, $current_entry_date );
				}

				unset( $this_content );
			}

			do_action( 'frm_after_day_content', $pass_atts );
		}
	}
	?></div>
</td>
<?php
	if ( $pos == $week_ends ) {
        $end_tr = true;
        echo "</tr>\n";
    }
}

$pos++;
if ( $pos == 7 ) {
    $pos = 0;
}
if ( $pos != $week_begins ) {
	if ( $pos > $week_begins ) {
		$week_begins = $week_begins + 7;
	}
	for ( $e = $pos; $e < $week_begins; $e++ ) {
		$day_class = '';
		if ( $e == 6 || $e == 7 ) {
			$day_class = ' class="frmcal-week-end"';
		}
		echo '<td' . $day_class . "></td>\n";
	}
}

if ( ! $end_tr ) {
    echo '</tr>';
}
