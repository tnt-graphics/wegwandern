<?php /* B2B Login Template */ 

function wegw_b2b_login_callback(){
?>

<div class="loginMenu ">
<div class="login_content_wrapper">

<h3><?php echo esc_html__( 'Account erstellen oder einloggen', 'wegwandern' ); ?></h3>
 
<?php echo do_shortcode( "[frm-login show_labels='0' username_placeholder='E-Mail-Adresse' password_placeholder='Passwort' label_remember='Eingeloggt bleiben' remember='1' label_log_in='Login'  show_lost_password='1' label_lost_password='Passwort vergessen' class_submit='b2b-login-submit' redirect='/b2b-portal/']" );?>

<h3><?php echo esc_html__( 'Noch kein Gratis-Account?', 'wegwandern' ); ?></h3>
<div class="create_account" onclick="openRegPoppup()"><?php echo esc_html__( 'Account erstellen', 'wegwandern' ); ?></div>
<div class="create-account-desc"><p><?php echo esc_html__( 'Privatsphäre und Datenschutz ist uns wichtig. Wir geben keine Informationen weiter.', 'wegwandern' ); ?></p></div>

</div>
</div>
<div class="overlay hide"></div>
<div class="regMenu regWindow hide">
<div class="close_warap"><span class="filter_close" onclick="closeReg()"></span>   </div>
<?php echo do_shortcode( "[formidable id=4]"); ?>
</div>

<div class="resetMenu resetWindow hide">
<div class="close_warap"><span class="filter_close" onclick="closeResetReg()"></span>   </div>
<?php echo do_shortcode( "[frm-reset-password show_labels='0' lostpass_button='Neues Passwort anfordern' resetpass_button='Passwort zurücksetzen' class='reset-frm' ]" );?>
</div>
<?php
}