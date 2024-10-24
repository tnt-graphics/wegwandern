function frmRegBackEnd(){

	/**
	 * Create an admin email, on click
	 *
	 * @since 2.0
	 */
	function createAdminEmail() {
		addEmailAction( 'admin' );
	}

	/**
	 * Create a user email, on click
	 *
	 * @since 2.0
	 */
	function createUserEmail() {
		addEmailAction( 'user' );
	}

	/**
	 * Create an email notification, on click
	 *
	 * @since 2.0
	 * @param {string} emailType
	 */
	function addEmailAction( emailType ){

		// Get number of last action
		var len = 0;
		var lastAction = jQuery('.frm_form_action_settings:last');
		if ( lastAction.length ) {
			len = lastAction.attr('id').replace('frm_form_action_', '');
		}

		var currentFormId = document.getElementById('form_id').value;

		jQuery.ajax({
			type:'POST',
			url:ajaxurl,
			data:{
				action:'frm_add_form_action',
				type:'email',
				list_id:(parseInt(len)+1),
				form_id:currentFormId,
				reg_email_type:emailType,
				nonce:frmRegGlobal.nonce
			},
			success:function(html){
				jQuery('#frm_notification_settings').append(html);
				jQuery('.frm_form_action_settings').fadeIn('slow');
				jQuery('#frm_form_action_' + (parseInt(len)+1) + ' .widget-inside').css('display','block');
				jQuery('#frm_form_action_' + (parseInt(len)+1) + ' .frm_multiselect' ).hide().each( frmDom.bootstrap.multiselect.init );
			}
		});
	}

	/**
	 * Hide the user meta Add button
	 *
	 * @since 2.0
	 */
	function hideUserMetaAdd() {
		var addTable = document.getElementById( 'frm_user_meta_add' );
		addTable.style.display = 'none';
	}

	/**
	 * Show the user meta table
	 *
	 * @since 2.0
	 */
	function showUserMetaTable() {
		var table = document.getElementById( 'frm_user_meta_table' );
		table.style.display = 'block';
	}


	/**
	 * Add a new row of user meta
	 *
	 * @since 2.0
	 */
	function addUserMetaRow(){
		var formId = document.getElementById('form_id').value;
		var actionKey = jQuery('.frm_single_register_settings').data('actionkey');

		var rowNumber = 0;
		var userMetaRows = document.querySelectorAll( '#frm_user_meta_rows .frm_user_meta_row' );
		if ( userMetaRows.length > 0 ) {
			var lastItem = userMetaRows[ userMetaRows.length - 1 ];
			rowNumber = 1 + parseInt( lastItem.id.replace('frm_user_meta_', '') );
		}

		jQuery.ajax({
			type:"POST",
			url:ajaxurl,
			data:{
				action:'frm_add_user_meta_row',
				form_id:formId,
				action_key:actionKey,
				meta_name:rowNumber
			},
			success:function(html){

				var $userMetaTable = jQuery('#frm_user_meta_rows');
				$userMetaTable.append(html);

				showUserMetaTable();
				hideUserMetaAdd();
			}
		});
	}

	/**
	 * Hide and show the redirect option in Register User action
	 *
	 * @since 2.0
	 */
	function displayRedirectOption( e ) {
		const confirmationSettingContainer = e.target.closest( 'td' ).nextElementSibling;
		confirmationSettingContainer.style.display = this.checked ? 'block' : 'none';
	}

	/**
	 * Hide and show the auto login option in Register User action
	 * if password is set to automatically generate
	 *
	 * @since 2.0
	 */
	function hideAutoLoginOption() {
		var autoLoginRow = document.getElementById( 'reg_auto_login_row' );

		if ( this !== null && this.value === '' ) {
			autoLoginRow.style.display = 'none';
			uncheckAutoLogin();
			showHideAutoLoginWarning( 'table-row' );

		} else {
			autoLoginRow.style.display = 'table-row';
			showHideAutoLoginWarning( 'none' );
		}
	}

	/**
	 * Uncheck the auto login option
	 *
	 * @since 2.0
	 */
	function uncheckAutoLogin() {
		var autoLoginCheckbox = document.getElementById( 'reg_auto_login' );

		if ( autoLoginCheckbox !== null && autoLoginCheckbox.checked ) {
			autoLoginCheckbox.checked = false;
		}
	}

	/**
	 * Show the auto login warning
	 *
	 * @since 2.0
	 */
	function showHideAutoLoginWarning( display ) {
		var autoLoginWarning = document.getElementById( 'reg_auto_login_msg' );

		if ( autoLoginWarning !== null ) {
			autoLoginWarning.style.display = display;
		}
	}

	/**
	 * Hide and show the User Moderation section in Register User action
	 * if password is set to automatically generate
	 *
	 * @since 2.02
	 */
	function hideUserModerationSection() {
		const userModerationSection = document.getElementById( 'reg_user_moderation_section' );

		if ( this !== null && this.value === '' ) {
			userModerationSection.style.display = 'none';
			uncheckEmailConfirmation();
			showHideUserModerationWarning( 'block' );
			return;
		}

		userModerationSection.style.display = 'table';
		showHideUserModerationWarning( 'none' );
	}

	/**
	 * Show or hide the user moderation warning
	 *
	 * @since 2.02
	 */
	function showHideUserModerationWarning( display ) {
		var userModerationWarning = document.getElementById( 'reg_user_moderation_msg' );

		if ( userModerationWarning !== null ) {
			userModerationWarning.style.display = display;
		}
	}

	/**
	 * Uncheck the email confirmation option
	 *
	 * @since 2.02
	 */
	function uncheckEmailConfirmation() {
		var emailConfirmationCheckbox = document.getElementById( 'reg_moderate_email' );

		if ( emailConfirmationCheckbox !== null && emailConfirmationCheckbox.checked ) {
			emailConfirmationCheckbox.checked = false;
		}
	}


	/**
	 * Hide and show the multi-site options in Register user action
	 *
	 * @since 2.0
	 */
	function displayMultiSiteOptions() {
		var i;
		var l;
		var multiSiteOptions = document.getElementsByClassName( 'reg_multisite_options' );

		if ( this.checked ) {
			for ( i = 0, l = multiSiteOptions.length; i < l; i++ ) {
				multiSiteOptions[i].style.display = 'block';
			}
		} else {
			for ( i = 0, l = multiSiteOptions.length; i < l; i++ ) {
				multiSiteOptions[i].style.display = 'none';
			}
		}
	}

	/**
	 * Hide and show the permission options in Register user action
	 *
	 * @since 2.0
	 */
	function displayPermissionOptions() {
		var permissionOptions = document.getElementById( 'reg_create_role_tr' );

		if ( this.checked ) {
			permissionOptions.style.display = 'table-row';
		} else {
			permissionOptions.style.display = 'none';
		}
	}

	function initOnSubmitAction() {
		const onChangeType = event => {
			if ( ! event.target.checked ) {
				return;
			}

			const actionEl = event.target.closest( '.frm_form_action_settings' );
			actionEl.querySelectorAll( '.frm_on_email_confirmation_dependent_setting:not(.frm_hidden)' ).forEach( el => {
				el.classList.add( 'frm_hidden' );
			});

			const activeEls = actionEl.querySelectorAll( '.frm_on_email_confirmation_dependent_setting[data-show-if-' + event.target.value + ']' );
			activeEls.forEach( activeEl => {
				activeEl.classList.remove( 'frm_hidden' );
			});
		};

		frmDom.util.documentOn( 'change', '.frm_on_email_confirmation_type input[type="radio"]', onChangeType );
	}

	return{
		init: function(){
			if ( document.getElementById('frm_notification_settings') !== null ) {
				// Bind event handlers for form Settings page
				frmRegBackEndJS.formActionsInit();
			}
		},

		formActionsInit: function(){

			var $formActions = jQuery(document.getElementById('frm_notification_settings'));

			$formActions.on( 'click', '.frmreg_admin_email', createAdminEmail );
			$formActions.on( 'click', '.frmreg_user_email', createUserEmail );
			$formActions.on( 'click', '.reg_user_meta_add_button', addUserMetaRow );
			$formActions.on( 'click', '.reg_add_user_meta_row', addUserMetaRow );
			$formActions.on( 'change', '#reg_moderate_email', displayRedirectOption );
			$formActions.on( 'change', '#reg_password', hideAutoLoginOption );
			$formActions.on( 'change', '#reg_password', hideUserModerationSection );
			$formActions.on( 'change', '#reg_create_subsite', displayMultiSiteOptions );
			$formActions.on( 'change', '#reg_create_users', displayPermissionOptions );
			initOnSubmitAction();
		}
	};
}

var frmRegBackEndJS = frmRegBackEnd();
jQuery(document).ready(function($){
	frmRegBackEndJS.init();
});
