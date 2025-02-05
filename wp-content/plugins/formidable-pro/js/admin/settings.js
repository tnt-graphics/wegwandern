( function () {
	function addEventListeners() {
		document.addEventListener( 'change', handleChangeEvent );
		document.addEventListener( 'keydown', handleKeyDownEvent );

		if ( document.getElementById( 'frm_single_entry_type' ) ) {
			// This script will also load in the form builder if a toggle field is added.
			// So avoid this listener if we're on another page.
			jQuery( document ).on(
				'frm-multiselect-changed',
				function( _, option ) {
					toggleSingleEntrySettings( option.value );
				}
			);
		}
	}

	function handleDisabledRolesClick() {
		document.querySelectorAll( '.frm_permissions_settings_settings .multiselect-container' ).forEach( msContainer => {
			const msOptions = Array.from( msContainer.children );
			msContainer.querySelectorAll( '.frm_disabled_option' ).forEach( disabledOption => {
				disabledOption.querySelector( 'input[type="checkbox"]' ).setAttribute('disabled', true);
				disabledOption.addEventListener( 'click', e => {
					e.stopPropagation();
					const index = msOptions.indexOf( disabledOption );
					// Click on the respective option element from the original 'select' element.
					msContainer.parentElement.parentElement.querySelector(`select option:nth-child(${index+1})`).click();
				});
			});
		});
	}

	function handleDomReady() {
		// Remove the event listener in Lite that toggles the Cookie expiration JS.
		setTimeout(
			function() {
				jQuery( document.getElementById( 'single_entry' ) ).off( 'change' );
				jQuery( document.getElementById( 'frm_single_entry_type' ) ).off( 'change' );
				handleDisabledRolesClick();
			},
			0
		);
	}

	/**
	 * @param {Event} event
	 * @return {void}
	 */
	function handleChangeEvent( event ) {
		if (
			'INPUT' === event.target.nodeName &&
			'checkbox' === event.target.type &&
			event.target.parentNode.classList.contains( 'frm_switch_block' )
		) {
			handleToggleChangeEvent( event );
			return;
		}

		switch( event.target.id ) {
			case 'single_entry':
				handleSingleEntry( event );
				break;
			case 'frm_single_entry_type':
				handleSingleEntryType();
				break;
		}

	}

	/**
	 * @param {Event} e
	 * @return {void}
	 */
	function handleKeyDownEvent( e ) {
		switch ( e.key ) {
			case ' ':
				handleSpaceDownEvent( e );
				break;
		}
	}

	/**
	 * @param {Event} e
	 * @return {void}
	 */
	function handleToggleChangeEvent( e ) {
		e.target.nextElementSibling.setAttribute( 'aria-checked', e.target.checked ? 'true' : 'false' );
	}

	/**
	 * @param {Event} e
	 * @return {void}
	 */
	function handleSpaceDownEvent( e ) {
		if ( e.target.classList.contains( 'frm_switch' ) ) {
			e.target.click();
		}
	}

	/**
	 * @param {Event} e
	 * @return {void}
	 */
	function handleSingleEntry( e ) {
		if ( e.target.checked ) {
			showElementsWithClassName( 'frm-single-entry-setting', 'frm_invisible' );
			handleSingleEntryType();
			return;
		}

		hideElementsWithClassName( 'frm-single-entry-setting', 'frm_invisible' );
		hideElementsWithClassName( 'frm-single-entry-type-email-setting' );
		hideElementsWithClassName( 'frm-single-entry-type-cookie-setting' );
	}

	/**
	 * @param {Event} e
	 * @return {void}
	 */
	function handleSingleEntryType() {
		toggleSingleEntrySettings( 'email' );
		toggleSingleEntrySettings( 'cookie' );
	}

	/**
	 * @return {void}
	 */
	function toggleSingleEntrySettings( type ) {
		const className = 'frm-single-entry-type-' + type + '-setting';
		if ( singleEntryTypeSettingIsSelected( type ) ) {
			showElementsWithClassName( className );
		} else {
			hideElementsWithClassName( className );
		}
	}

	/**
	 * @param {string} setting
	 * @return {bool}
	 */
	function singleEntryTypeSettingIsSelected( setting ) {
		const input = document.getElementById( 'frm_single_entry_type' );
		const types = jQuery( input ).val();
		return -1 !== types.indexOf( setting );
	}

	/**
	 * @param {string} className
	 * @return {Array}
	 */
	function getArrayWithClassName( className ) {
		return Array.from( document.getElementsByClassName( className ) );
	}

	/**
	 * @param {string} className
	 * @return {void}
	 */
	function showElementsWithClassName( className, classNameToRemove = 'frm_hidden' ) {
		getArrayWithClassName( className ).forEach( element => element.classList.remove( classNameToRemove ) );
	}

	/**
	 * @param {string} className
	 * @return {void}
	 */
	function hideElementsWithClassName( className, classNameToAdd = 'frm_hidden' ) {
		getArrayWithClassName( className ).forEach( element => element.classList.add( classNameToAdd ) );
	}

	addEventListeners();

	document.addEventListener( 'DOMContentLoaded', handleDomReady );
} )();
