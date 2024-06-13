<?php
function wegw_main_menu_display() {
    $tourenportal_page = get_field( 'select_tourenportal_page', 'option' ); ?>

    <div class="main-menu mainMenuWindow">
        <div class="menu_title">
            <a href="<?php echo $tourenportal_page; ?>">
                <div class="touren_portal"></div>
                <h3><?php echo esc_html__( 'Zum Tourenportal', 'wegwandern' ); ?></h3>
            </a>
            <div class="close_warap"><span class="menu_close" onclick="closeMainMenu()"></span></div>
        </div>

        <div class="menu_content_wrapper">
            <?php
            wp_nav_menu(
                array(
                    'menu'            => 'main-menu',
                    'container'       => 'ul',
                    'container_class' => 'menu',
                    'menu_class'      => 'menu',
                )
            );

            echo do_shortcode( '[formidable id=2]' );
            get_search_form(); ?>
        </div>
    </div>
<?php } ?>