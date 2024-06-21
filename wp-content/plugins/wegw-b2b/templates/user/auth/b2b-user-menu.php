<?php
/* B2B user menu*/

function b2b_user_menu_callback () {

    wegwb_b2b_check_user_role_access();

if ( have_rows( 'b2b_menu_options', 'option' ) ) :
    /* Loop through rows. */
    global $wp;
    
?>

<div class="c_o_menu_wrapper">
    <div class="c_o_menu_list_wrap">
        <ul class="owl-carousel">
        <?php 
        $b2b_profile_fields = get_b2b_profile_fields();
        while ( have_rows( 'b2b_menu_options', 'option' ) ) :
            the_row();
            $b2b_choose_menu_title = get_sub_field( 'b2b_choose_menu_title' );
            //Hide menu based on profile form
            if( empty( $b2b_profile_fields['gender'] ) || empty( $b2b_profile_fields['firstname'] ) || empty( $b2b_profile_fields['lastname'] ) || empty( $b2b_profile_fields['address'] ) || empty( $b2b_profile_fields['ort'] ) || empty( $b2b_profile_fields['plz'] ) || empty( $b2b_profile_fields['phonenumber'] ) || empty( $b2b_profile_fields['email'] )  ){
                if( in_array( $b2b_choose_menu_title, WEGW_B2B_HIDE_MENUS ) ){
                  continue;
                }
            }
            $b2b_choose_menu_pages = get_sub_field( 'b2b_choose_menu_pages' );
            $current_url = home_url(add_query_arg(array(), $wp->request)).'/';
            $active_class = '';
            if ( $current_url == $b2b_choose_menu_pages ) {
                $active_class = 'active';
            }
            ?>
        <li class="c_o_menu_item <?php echo $active_class; ?>"><a href="<?php echo $b2b_choose_menu_pages; ?>"><?php echo $b2b_choose_menu_title; ?></a></li>
        <?php  endwhile; ?>
			<?php 
            if ( is_user_logged_in() ) { ?>
                <li class="b2bLogoutMenu">
                    <div class="b2b_logout_content_wrapper">
                        <p><a href="<?php echo wp_logout_url( site_url( '/' ) . 'b2b-portal/' ); ?>"><?php echo esc_html__( 'Logout', 'wegwandern' ); ?></a></p>
                    </div>
                </li>
            <?php } ?>
        </ul>
        
    </div>
</div>


<?php endif; 

    } ?>