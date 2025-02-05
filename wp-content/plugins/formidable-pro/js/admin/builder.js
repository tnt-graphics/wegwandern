( function() {

	/** globals wp */

	const hooks = wp.hooks;
	const hookNamespace = 'formidable-pro';

	const STEP_UNIT_SECOND = 'sec';

	const STEP_UNIT_MILLISECOND = 'millisec';

	function addEventListeners() {
		document.addEventListener( 'change', handleChangeEvent );
		document.addEventListener( 'frm_added_field', onFieldAdded );
	}

	hooks.addFilter( 'frm_conditional_logic_field_options', hookNamespace, updateFieldOptions );

	hooks.addAction( 'frm_after_delete_field', 'formidable-pro', function( fieldLi ) {
		const fieldID = getFieldIDFromHTMLID( fieldLi.id );
		document.querySelector( `a[data-code="${fieldID}"]` )?.closest( '.frm-customize-list' )?.remove();
	});

	hooks.addAction( 'frm_before_delete_field_option', hookNamespace, deleteConditionalLogicOptions );

	const onFieldAdded = event => {
		PageBreakField.onAddedField( event );
		maybeAddFieldToFieldShortcodes( event );
	};

	const maybeAddFieldToFieldShortcodes = params => {
		if ( [ 'data', 'divider', 'end_divider', 'captcha', 'break', 'html', 'form', 'summary' ].includes( params.frmType ) ) {
			return;
		}
		const insertCodeID  = createFieldsShortcodeRowLink( 'id', params );
		const insertCodeKey = createFieldsShortcodeRowLink( 'key', params );
		const shortcodeLink = frmDom.tag( 'li', {
			className: 'frm-customize-list dropdown-item show_frm_not_email_to',
			children: [
				insertCodeID,
				insertCodeKey,
			],
		});
		document.querySelector( '#frm-insert-fields-box .frm_code_list' )?.insertAdjacentElement( 'beforeend', shortcodeLink );
	};

	/**
	 * Returns the field id from html id.
	 *
	 * @param {string} htmlID
	 * @returns {string}
	 */
	const getFieldIDFromHTMLID = htmlID => htmlID.replace( 'frm_field_id_', '' );

	/**
	 * Creates <li> elements for the new field to be inserted in the shortcodes popup.
	 *
	 * @param {string} type 
	 * @param {Object} params 
	 * @returns {HTMLElement}
	 */
	const createFieldsShortcodeRowLink = ( type, params ) => {
		const fieldID   = getFieldIDFromHTMLID( params.frmField.id );
		const fieldKey  = document.getElementById( `field_options_field_key_${fieldID}` )?.value;
		const isIDLink  = type === 'id';
		const shortcode = isIDLink ? fieldID : fieldKey;
		const link = frmDom.a({
			className: ( isIDLink ? 'frmids ' : 'frmkeys ' ) + 'frm_insert_code',
			children: [
				document.querySelector( `.frm_t${params.frmType} .frmsvg` ).cloneNode( true ),
				document.getElementById( `frm_name_${fieldID}` ).value,
				frmDom.span( `[${shortcode}]` ),
			]
		});
		const idsTabIsActiveInShortcodesModal = document.querySelector( '#frm-insert-fields-box .subsubsub .frmids' )?.classList.contains( 'current' );
		if ( isIDLink && ! idsTabIsActiveInShortcodesModal || ! isIDLink && idsTabIsActiveInShortcodesModal ) {
			link.classList.add( 'frm_hidden' );
		}

		link.setAttribute( 'data-code', isIDLink ? fieldID : fieldKey );
		return link;
	};

	function updateFieldOptions( fieldOptions, hookArgs ) {
		if ( 'scale' === hookArgs.type ) {
			fieldOptions = getScaleFieldOptions( hookArgs.fieldId );
		}
		return fieldOptions;
	}

	function getScaleFieldOptions( fieldId ) {
		let opts = [];
		const optVals = document.querySelectorAll( 'input[name^="item_meta[' + fieldId + ']"]' );

		optVals.forEach( opt => {
			opts.push( opt.value );
		});

		return opts;
	}

	function updateConditionalLogicsDependentOnThis( target ) {
		setTimeout( function() {
			let fieldId = target.closest( '.frm-single-settings' ).dataset.fid;

			if ( ! fieldId ) {
				return;
			}

			frmAdminBuild.adjustConditionalLogicOptionOrders( fieldId, 'scale' );
		}, 0 );
	}

	function updateShortcodeTriggerLabel( shortcodeTrigger, value ) {
		const textshortcodeTriggerLabel = shortcodeTrigger.querySelector( 'svg.frmsvg' )?.nextSibling;
		if ( ! textshortcodeTriggerLabel || textshortcodeTriggerLabel.nodeType !== Node.TEXT_NODE ) {
			return;
		}
		textshortcodeTriggerLabel.textContent = value;
	}

	function maybeUpdateFieldsShortcodeModal( labelInput ) {
		const fieldId = labelInput.id.replace( 'frm_name_', '' );
		const fieldShortcodeTrigger = document.querySelector( 'a[data-code="' + fieldId + '"]');
		if ( ! fieldShortcodeTrigger ) {
			return;
		}
		updateShortcodeTriggerLabel( fieldShortcodeTrigger, labelInput.value );
		updateShortcodeTriggerLabel( fieldShortcodeTrigger.nextElementSibling, labelInput.value );
	}

	function handleModalDismiss( input ) {
		const modalDismissers = document.querySelectorAll( '#frm_info_modal .dismiss, #frm_info_modal #frm-info-click, .ui-widget-overlay.ui-front' );
		function onModalClose() {
			input.classList.add( 'frm_invalid_field' );
			setTimeout( () => input.focus(), 0 );
			modalDismissers.forEach( el => {
				el.removeEventListener( 'click', onModalClose );
			});
		}

		modalDismissers.forEach( el => {
			el.addEventListener( 'click', onModalClose );
		});
	}

	function validateSizeLimitValue( target ) {
		let validationFailMessage;
		if ( isNaN( target.value.trim() ) ) {
			validationFailMessage = wp.i18n.__( 'Please enter a valid number.', 'formidable-pro' );
		} else {
			const parent  = target.closest( '.frm_grid_container' );
			const minSize = parent.querySelector( '[id^=min_size_]' );
			const maxSize = parent.querySelector( '[id^=size_]' );
			if ( minSize.value && maxSize.value && Number( minSize.value ) > Number( maxSize.value ) ) {
				validationFailMessage = wp.i18n.__( 'Minimum size cannot be greater than maximum size.', 'formidable-pro' );
			} else {
				const otherInput = target.id.startsWith( 'min_size_' ) ? maxSize : minSize;
				if ( ! isNaN( otherInput.value.trim() ) ) {
					otherInput.classList.remove( 'frm_invalid_field' );
				}
			}
		}

		if ( validationFailMessage ) {
			frmAdminBuild.infoModal( validationFailMessage );
			handleModalDismiss( target );
			return;
		}
		if ( target.classList.contains( 'frm_invalid_field' ) ) {
			target.classList.remove( 'frm_invalid_field' );
		}
	}

	function handleChangeEvent( e ) {
		const target = e.target;

		if ( target.id.startsWith( 'min_size' ) || target.id.startsWith( 'size' ) ) {
			validateSizeLimitValue( target );
		}

		if ( target.id.startsWith( 'frm_name_' ) ) {
			maybeUpdateFieldsShortcodeModal( target );
		}

		if ( isRootlineSettingInput( target ) ) {
			Rootline.updateRootline();
			return;
		}

		if ( target.matches( '.frm_page_transition_setting' ) ) {
			PageBreakField.onChangeTransition( e );
			return;
		}

		if ( target.matches( '.frm_scale_opt' ) ) {
			updateConditionalLogicsDependentOnThis( target );
		}

		if ( isACurrencySetting( target ) ) {
			const settingsContainer = target.closest( '.frm-type-range' );
			syncSliderFieldAfterCurrencyChange( settingsContainer.getAttribute( 'data-fid' ) );
			return;
		}

		if ( target.classList.contains( 'radio_maxnum' ) ) {
			setStarValues( target );
			return;
		}

		if ( 'INPUT' === target.nodeName && 'checkbox' === target.type ) {
			handleCheckboxToggleEvent( e );
		} else if ( target.classList.contains( 'frm_scale_opt' ) ) {
			setScaleValues( target );
		} else if ( 0 === target.id.indexOf( 'step_unit_' ) ) {
			onChangeStepUnit( e );
		}

		validateTimeFieldRangeValue( target );
	}

	/**
	 * Checks if the given target is a rootline setting input.
	 *
	 * @param {HTMLElement} target Target element.
	 * @return {Boolean}
	 */
	function isRootlineSettingInput( target ) {
		const rootlineSettingIds = [
			'frm-rootline-type',
			'frm-rootline-titles-on',
			'frm-rootline-numbers-off',
			'frm-rootline-lines-off'
		];

		return rootlineSettingIds.includes( target.id ) || target.matches( '.frm-rootline-title-setting input' );
	}

	function isACurrencySetting( input ) {
		return input.closest( '.frm_custom_currency_options_wrapper' ) && input.closest( '.frm-type-range' );
	}

	function handleCheckboxToggleEvent( e ) {
		const element = e.target;
		const name = element.name;

		if ( nameMatchesCurrencyOption( name ) ) {
			const calcBox = element.closest( '[id^="frm-calc-box-"]' );
			if ( calcBox ) {
				syncCalcBoxSettingVisibility( calcBox );
			} else {
				const settings = element.closest( '.frm-single-settings' );
				if ( null !== settings && settings.classList.contains( 'frm-type-range' ) ) {
					syncSliderFormatSettingVisibility( settings );
				}
			}
		}
	}

	function nameMatchesCurrencyOption( name ) {
		return -1 !== name.indexOf( 'field_options[calc_type_' ) ||
			-1 !== name.indexOf( 'field_options[is_currency_' ) ||
			-1 !== name.indexOf( 'field_options[custom_currency_' );
	}

	function syncCalcBoxSettingVisibility( calcBox ) {
		const typeToggle = calcBox.querySelector( '[name^="field_options[calc_type_"]' );
		const isMathType = ! typeToggle.checked;
		const decimalPlacesWrapper = calcBox.querySelector( '.frm_calc_dec' ).closest( '.frm_form_field' );
		const formatAsCurrencyOption = calcBox.querySelector( '[name^="field_options[is_currency_"]' );
		const isCurrency = formatAsCurrencyOption.checked;

		toggle( decimalPlacesWrapper, isMathType && ! isCurrency );
		syncCustomFormatSettings( calcBox, isMathType );
	}

	function syncSliderFormatSettingVisibility( settingsContainer ) {
		syncCustomFormatSettings( settingsContainer, true );

		const fieldId = settingsContainer.getAttribute( 'data-fid' );
		syncSliderFieldAfterCurrencyChange( fieldId );
	}

	function syncSliderFieldAfterCurrencyChange( fieldId ) {
		const fieldPreview = document.getElementById( 'frm_field_id_' + fieldId );
		const range = fieldPreview.querySelector( 'input[type="range"]' );
		updateSliderFieldPreview({
			field: range,
			att: 'value',
			newValue: range.value
		});
	}

	function syncCustomFormatSettings( container, showSettings ) {
		const formatAsCurrencyOption = container.querySelector( '[name^="field_options[is_currency_"]' );
		const formatAsCurrencyWrapper = formatAsCurrencyOption.closest( '.frm_form_field' );
		const isCustomCurrencyCheckbox = container.querySelector( '[name^="field_options[custom_currency_"]' );
		const isCustomCurrency = isCustomCurrencyCheckbox.checked;
		const customCurrencyCheckboxWrapper = isCustomCurrencyCheckbox.closest( '.frm_form_field' );
		const isCurrency = formatAsCurrencyOption.checked;
		const customCurrencyOptionsWrapper = container.querySelector( '.frm_custom_currency_options_wrapper' );
		const wasCustomCurrency = ! customCurrencyOptionsWrapper.classList.contains( 'frm_hidden' );

		toggle( formatAsCurrencyWrapper, showSettings );
		toggle( customCurrencyCheckboxWrapper, showSettings && isCurrency );
		toggle( customCurrencyOptionsWrapper, showSettings && isCurrency && isCustomCurrency );

		if ( ! wasCustomCurrency && isCustomCurrency ) {
			setCustomCurrencyDefaultsToMatchDefaultCurrency( container );
		}
	}

	function setCustomCurrencyDefaultsToMatchDefaultCurrency( container ) {
		const settings = [
			'custom_decimals',
			'custom_decimal_separator',
			'custom_thousand_separator',
			'custom_symbol_left',
			'custom_symbol_right'
		];
		settings.forEach( updateCustomCurrencySettingToMatchDefault );

		function updateCustomCurrencySettingToMatchDefault( setting ) {
			container.querySelector( '[name^="field_options[' + setting + '_"]' ).value = frmProBuilderVars.currency[ setting.replace( 'custom_', '' ) ];
		}
	}

	function setScaleValues( target ) {
		const fieldID = target.id.replace( 'scale_maxnum_', '' ).replace( 'scale_minnum_', '' ).replace( 'frm_step_', '' );
		let min = document.getElementById( 'scale_minnum_' + fieldID ).value;
		let max = document.getElementById( 'scale_maxnum_' + fieldID ).value;

		updateScaleValues( parseInt( min, 10 ), parseInt( max, 10 ), fieldID );
	}

	function updateScaleValues( min, max, fieldID ) {
		const container = jQuery( '#field_' + fieldID + '_inner_container .frm_form_fields' );
		const appendFieldToContainer = ( optionValue ) => {
			container.append( '<div class="frm_scale"><label><input type="hidden" name="field_options[options_' + fieldID + '][' + optionValue + ']" value="' + optionValue + '"> <input type="radio" name="item_meta[' + fieldID + ']" value="' + optionValue + '"> ' + optionValue + ' </label></div>' );
		};

		container.html( '' );
		let step = parseInt( document.getElementById( 'frm_step_' + fieldID ).value, 10 );
		if ( step === 0 ) {
			step = 1;
		}

		const ascending = min <= max;

		step = Math.abs( step );

		for ( let i = min; ascending ? i <= max : i >= max; i = ascending ? i + step : i - step ) {
			appendFieldToContainer( i );
		}

		container.append( '<div class="clear"></div>' );
	}

	function toggle( element, on ) {
		jQuery( element ).stop();
		element.style.opacity = 1;

		if ( on ) {
			if ( element.classList.contains( 'frm_hidden' ) ) {
				element.style.opacity = 0;
				element.classList.remove( 'frm_hidden' );
				jQuery( element ).animate({ opacity: 1 });
			}
		} else if ( ! element.classList.contains( 'frm_hidden' ) ) {
			jQuery( element ).animate({ opacity: 0 }, function() {
				element.classList.add( 'frm_hidden' );
			});
		}
	}

	hooks.addAction( 'frm_update_slider_field_preview', hookNamespace, updateSliderFieldPreview, 10 );

	function updateSliderFieldPreview({ field, att, newValue }) {
		if ( 'value' === att ) {
			if ( '' === newValue ) {
				newValue = getSliderMidpoint( field );
			}
			field.value = newValue;
		} else {
			field.setAttribute( att, newValue );
		}

		if ( -1 === [ 'value', 'min', 'max' ].indexOf( att ) ) {
			return;
		}

		if ( ( 'max' === att || 'min' === att ) && '' === getSliderDefaultValueInput( field.id ) ) {
			field.value = getSliderMidpoint( field );
		}

		const fieldId = field.getAttribute( 'name' ).replace( 'item_meta[', '' ).replace( ']', '' );
		const settingsContainer = document.getElementById( 'frm-single-settings-' + fieldId );
		const isCurrency = settingsContainer.querySelector( 'input[name="field_options[is_currency_' + fieldId + ']"]' ).checked;
		const sliderValueSpan = field.parentNode.querySelector( '.frm_range_value' );

		if ( ! isCurrency ) {
			sliderValueSpan.textContent = field.value;
			return;
		}

		const isCustomCurrency = settingsContainer.querySelector( 'input[name="field_options[custom_currency_' + fieldId + ']"]' ).checked;
		const currency = isCustomCurrency ? {
			decimals: parseInt( getValueFromSettingsContainerInput( 'select', 'custom_decimals' ) ),
			decimal_separator: getValueFromSettingsContainerInput( 'input', 'custom_decimal_separator' ),
			thousand_separator: getValueFromSettingsContainerInput( 'input', 'custom_thousand_separator' ),
			symbol_left: getValueFromSettingsContainerInput( 'input', 'custom_symbol_left' ),
			symbol_right: getValueFromSettingsContainerInput( 'input', 'custom_symbol_right' ),
			symbol_padding: ''
		} : frmProBuilderVars.currency;

		sliderValueSpan.textContent = formatCurrency( normalizeTotal( field.value, currency ), currency );

		function getValueFromSettingsContainerInput( type, name ) {
			let selector = type + '[name="field_options[' + name + '_' + fieldId + ']"]';
			if ( 'select' === type ) {
				selector += ' option:checked';
			}
			return settingsContainer.querySelector( selector ).value;
		}

		function getSliderDefaultValueInput( previewInputId ) {
			return document.querySelector( 'input[data-changeme="' + previewInputId + '"][data-changeatt="value"]' ).value;
		}

		function getSliderMidpoint( sliderInput ) {
			const max = parseFloat( sliderInput.getAttribute( 'max' ) );
			const min = parseFloat( sliderInput.getAttribute( 'min' ) );
			return ( max - min ) / 2 + min;
		}
	}

	function normalizeTotal( total, currency ) {
		total = currency.decimals > 0 ? round10( total, currency.decimals ) : Math.ceil( total );
		return maybeAddTrailingZeroToPrice( total, currency );
	}

	function round10( value, decimals ) {
		return Number( Math.round( value + 'e' + decimals ) + 'e-' + decimals );
	}

	function formatCurrency( total, currency ) {
		let leftSymbol, rightSymbol;

		total = maybeAddTrailingZeroToPrice( total, currency );
		total = maybeRemoveTrailingZerosFromPrice( total, currency );
		total = addThousands( total, currency );
		leftSymbol = currency.symbol_left + currency.symbol_padding;
		rightSymbol = currency.symbol_padding + currency.symbol_right;

		function maybeRemoveTrailingZerosFromPrice( total, currency ) {
			var split = total.split( currency.decimal_separator );
			if ( 2 !== split.length || split[1].length <= currency.decimals ) {
				return total;
			}
			if ( 0 === currency.decimals ) {
				return split[0];
			}
			return split[0] + currency.decimal_separator + split[1].substr( 0, currency.decimals );
		}

		function addThousands( total, currency ) {
			if ( currency.thousand_separator ) {
				total = total.toString().replace( /\B(?=(\d{3})+(?!\d))/g, currency.thousand_separator );
			}
			return total;
		}

		return leftSymbol + total + rightSymbol;
	}

	function maybeAddTrailingZeroToPrice( price, currency ) {
		if ( 'number' !== typeof price ) {
			return price;
		}

		price += ''; // first convert to string

		const pos = price.indexOf( '.' );
		if ( pos === -1 ) {
			price = price + '.00';
		} else if ( price.substring( pos + 1 ).length < 2 ) {
			price += '0';
		}

		return price.replace( '.', currency.decimal_separator );
	}

	/**
	 * Wrap rich text logic into a function and initialize.
	 * A RTE field has uses TinyMCE for the preview, and for the default value input.
	 * The RTE needs to re-initialize at various points including:
	 * - when drag-and-dropped
	 * - when added with AJA
	 * - when a new field is inserted
	 * - when a group is broken into rows
	 * - when rows are merged into a group
	 *
	 * @returns {void}
	 */
	function initRichTextFields() {
		appendModalTriggersToRtePlaceholderSettings();

		document.addEventListener(
			'click',
			function( event ) {
				const classList = event.target.classList;
				if ( classList.contains( 'frm-break-field-group' ) || classList.contains( 'frm-row-layout-option' ) || classList.contains( 'frm-save-custom-field-group-layout' ) ) {
					initializeAllWysiwygsAfterSlightDelay();
				}
			}
		);

		document.addEventListener(
			'frm_added_field',
			/**
			 * Prepare an RTE field when a new field is added.
			 *
			 * @param {Event} event
			 * @returns {void}
			 */
			event => {
				if ( 'rte' !== event.frmType || ! event.frmField ) {
					return;
				}

				prepareDefaultValueInput( event.frmField.getAttribute( 'data-fid' ) )

				const wysiwyg = event.frmField.querySelector( '.wp-editor-area' );
				if ( wysiwyg ) {
					frmDom.wysiwyg.init( wysiwyg );
				}
			}
		);

		document.addEventListener(
			'frm_ajax_loaded_field',
			/**
			 * When new fields are loaded with AJAX, check if any are RTE fields and initialize.
			 *
			 * @param {Event} event
			 * @returns {void}
			 */
			event => {
				event.frmFields.forEach(
					/**
					 * Check if a single field is an RTE and possibly initialize.
					 *
					 * @param {Object} field {
					 *     @type {String} id Numeric field ID.
					 * }
					 * @returns {void}
					 */
					field => {
						if ( 'rte' !== field.type ) {
							return;
						}

						prepareDefaultValueInput( field.id );

						const wysiwyg = document.querySelector( '#frm_field_id_' + field.id + ' .wp-editor-area' );
						if ( wysiwyg ) {
							frmDom.wysiwyg.init( wysiwyg );
						}
					}
				);
			}
		);

		let draggable;
		// frm_sync_after_drag_and_drop does not pass along information about the draggable, so hook into dropdeactivate.
		jQuery( document ).on( 'dropdeactivate', function( _, ui ) {
			draggable = ui.draggable.get( 0 );
		});
		document.addEventListener(
			'frm_sync_after_drag_and_drop',
			() => {
				if ( draggable ) {
					// Use querySelectorAll as frm_sync_after_drag_and_drop is also called for field groups.
					draggable.querySelectorAll( '.wp-editor-area' ).forEach( frmDom.wysiwyg.init );
				}
			}
		);

		function prepareDefaultValueInput( fieldId ) {
			const defaultValueWrapper = document.getElementById( 'default-value-for-' + fieldId );
			addSmartValuesTriggerToDefaultValueWrapper( defaultValueWrapper );
			copyChangemeFromWrapperToInput( defaultValueWrapper );
		}

		function initializeAllWysiwygsAfterSlightDelay() {
			setTimeout(
				() => document.querySelectorAll( '#frm-show-fields .wp-editor-area' ).forEach( frmDom.wysiwyg.init ),
				1
			);
		}

		function appendModalTriggersToRtePlaceholderSettings() {
			const rtePlaceholderDefaults = document.querySelectorAll( '.frm-single-settings.frm-type-rte .frm-default-value-wrapper' );
			if ( ! rtePlaceholderDefaults.length ) {
				return;
			}

			rtePlaceholderDefaults.forEach(
				defaultValueWrapper => {
					addSmartValuesTriggerToDefaultValueWrapper( defaultValueWrapper );
					copyChangemeFromWrapperToInput( defaultValueWrapper );
				}
			);
		}

		function copyChangemeFromWrapperToInput( defaultValueWrapper ) {
			const fieldToChangeId = defaultValueWrapper.getAttribute( 'data-changeme' );

			document.getElementById( defaultValueWrapper.getAttribute( 'data-html-id' ) ).setAttribute( 'data-changeme', fieldToChangeId );
			defaultValueWrapper.removeAttribute( 'data-changeme' );

			const field = document.getElementById( fieldToChangeId );
			if ( field ) {
				jQuery( field ).on(
					'change',
					function() {
						if ( ! tinyMCE.editors[ field.id ] || tinyMCE.editors[ field.id ].isHidden() ) {
							return;
						}
						tinyMCE.editors[ field.id ].setContent( field.value );
					}
				);
			}
		}

		function addSmartValuesTriggerToDefaultValueWrapper( defaultValueWrapper ) {
			/*global frmDom */
			const { svg } = frmDom;

			const inputID = defaultValueWrapper.getAttribute( 'data-html-id' );

			const modalTrigger = svg({ href: '#frm_more_horiz_solid_icon', classList: [ 'frm_more_horiz_solid_icon', 'frm-show-inline-modal' ] });
			modalTrigger.setAttribute( 'data-open', 'frm-smart-values-box' );
			modalTrigger.setAttribute( 'title', defaultValueWrapper.getAttribute( 'data-modal-trigger-title' ) );

			document.getElementById( inputID ).parentElement.prepend( modalTrigger );

			// The icon should be wrapped in a 'p' tag, as the modal box is appended to the 'closest' p.
			const wrapper = document.createElement( 'p' );
			wrapper.prepend( document.getElementById( 'wp-' + inputID + '-wrap' ) );
			defaultValueWrapper.appendChild( wrapper );
		}
	}

	function validateTimeFieldRangeValue( target ) {
		if ( ! ( target.id.startsWith( 'start_time' ) || target.id.startsWith( 'end_time' ) ) ) {
			return;
		}

		const timeRangeInput = target;
		let isValid          = true;

		function getStepUnit() {
			const stepUnitEl = timeRangeInput.closest( '.frm-single-settings' ).querySelector( 'select[id^="step_unit_"]' );
			if ( ! stepUnitEl ) {
				return false;
			}
			return stepUnitEl.value;
		}

		if ( timeRangeInput.matches( '[id^=frm_step_]' ) ) {
			if ( timeRangeInput.value.match( /^\d{1,2}$/ ) ) {
				return;
			}
			frmAdminBuild.infoModal( 'Step value is invalid.' );
			isValid = false;

		} else if ( ! timeRangeInput.value.match( getTimeRangeRegex( getStepUnit() ) ) ) {
			let timeRangeString;
			if ( timeRangeInput.matches( '.frm-type-time [id^=start_time]' ) ) {
				timeRangeString = 'Start time';
			} else {
				timeRangeString = 'End time';
			}
			frmAdminBuild.infoModal( `${timeRangeString} is invalid.` );
			isValid = false;
		}

		if ( ! isValid ) {
			handleModalDismiss( timeRangeInput );
		} else if ( timeRangeInput.classList.contains( 'frm_invalid_field' ) ) {
			timeRangeInput.classList.remove( 'frm_invalid_field' );
		}
	}

	const PageBreakField = {
		transition: false, // Track the transition value when one of page break transition changes.

		/**
		 * Handles change transition event.
		 *
		 * @param {Event} event Event object.
		 */
		onChangeTransition: function( event ) {
			// Store the updated value to update new page break field.
			PageBreakField.transition = event.target.value;

			// Update other page break fields.
			document.querySelectorAll( '.frm_page_transition_setting' ).forEach( el => {
				if ( el.id === event.target.id ) {
					// Do not update current setting.
					return;
				}

				el.value = event.target.value;
			});
		},

		/**
		 * Handlers added field.
		 *
		 * @param {Event} event Event object.
		 */
		onAddedField: function( event ) {
			if ( false === PageBreakField.transition || 'break' !== event.frmType ) {
				return;
			}

			const transitionSetting = document.getElementById( 'frm_transition_' + event.frmField.dataset.fid );
			transitionSetting.value = PageBreakField.transition;
		}
	};

	const Rootline = {
		init: function() {
			hooks.addAction( 'frmShowedFieldSettings', hookNamespace, this.showedFieldSettings );

			document.addEventListener( 'frm_added_field', function( event ) {
				if ( 'break' === event.frmType && Rootline.isRootlineAvailable() ) {
					Rootline.updateRootline();
				}
			});

			hooks.addAction( 'frm_renumber_page_breaks', hookNamespace, function( pages ) {
				if ( ! Rootline.isRootlineAvailable() ) {
					return;
				}

				if ( pages.length > 1 ) {
					Rootline.updateRootline();
				} else {
					Rootline.toggleRootline( false );
				}
			});

			// Listen for rootline settings change to update the rootline.
			const settingSelectors = '#frm-rootline-type,#frm-rootline-titles-on,.frm-rootline-title-setting input,#frm-rootline-numbers-off,#frm-lines-numbers-off';
			frmDom.util.documentOn( 'change', settingSelectors, this.updateRootline );

			this.makeRootlineResponsive();
		},

		/**
		 * Checks if rootline is available in form builder.
		 *
		 * @return {Boolean}
		 */
		isRootlineAvailable: function() {
			return document.getElementById( 'frm-rootline-type' );
		},

		/**
		 * Does after showed field settings.
		 *
		 * @param {HTMLElement} showBtn Show settings button.
		 * @param {HTMLElement} settingsEl Settings element.
		 */
		showedFieldSettings: function( showBtn, settingsEl ) {
			if ( 'rootline' !== showBtn.dataset.fid ) {
				return;
			}

			Rootline.loadPageTitlesSetting( settingsEl );
		},

		/**
		 * Loads titles setting for Rootline.
		 *
		 * @param {HTMLElement} settingsEl Settings element.
		 */
		loadPageTitlesSetting: function( settingsEl ) {
			const pageBreaks    = document.querySelectorAll( '.frm_field_box.edit_field_type_break' );
			const titleSettings = settingsEl.querySelectorAll( '.frm-rootline-title-setting' );
			const pagesCount    = pageBreaks.length + 1; // Plus the first page break.

			for ( let i = 1; i < pagesCount; i++ ) {
				if ( 'undefined' !== typeof titleSettings[ i ] ) {
					// Show it.
					titleSettings[ i ].classList.remove( 'frm_hidden' );
				} else {
					// Append new title setting.
					Rootline.appendPageTitleSetting( titleSettings[0], pageBreaks[ i - 1 ] );
				}
			}

			// Hide title exceeded settings.
			if ( pagesCount < titleSettings.length ) {
				for ( let i = pagesCount; i < titleSettings.length; i++ ) {
					titleSettings[ i ].classList.add( 'frm_hidden' );
				}
			}
		},

		/**
		 * Appends page title input in the rootline titles setting.
		 *
		 * @param {HTMLElement} firstSetting First title setting element.
		 * @param {HTMLElement} pageBreak    Corresponding page break element.
		 */
		appendPageTitleSetting: function( firstSetting, pageBreak ) {
			const cloneSetting = firstSetting.cloneNode( true );
			const label        = cloneSetting.querySelector( 'label' );
			const input        = cloneSetting.querySelector( 'input' );
			const pageId       = pageBreak.dataset.fid;

			frmDom.setAttributes( label, {
				for: label.getAttribute( 'for' ).replace( '1', pageId )
			});

			frmDom.setAttributes( input, {
				id: input.id.replace( '1', pageId ),
				name: input.name.replace( '[0]', '[' + pageId + ']' ),
				'data-page': pageId,
				value: pageBreak.querySelector( '.frm_button_submit' ).innerText
			});

			firstSetting.parentNode.appendChild( cloneSetting );
		},

		/**
		 * Gets rootline settings.
		 *
		 * @return {{titlesOn, numbersOn: boolean, linesOn: boolean, position, titles: any[], type}}
		 */
		getSettings: function() {
			return {
				type: document.getElementById( 'frm-rootline-type' ).value,
				position: document.getElementById( 'frm-pagination-position' ).value,
				titlesOn: document.getElementById( 'frm-rootline-titles-on' ).checked,
				numbersOn: ! document.getElementById( 'frm-rootline-numbers-off' ).checked,
				linesOn: ! document.getElementById( 'frm-rootline-lines-off' ).checked,
				titles: Array.from( document.querySelectorAll( '.frm-rootline-title-setting input' ) ).map( input => input.value )
			};
		},

		/**
		 * Updates rootline UI.
		 */
		updateRootline: function() {
			const settings = Rootline.getSettings();
			if ( ! settings.type ) {
				Rootline.toggleRootline( false );
				return;
			}

			Rootline.toggleRootline( true );

			/**
			 * Allows using custom handler for live updating rootline in form builder.
			 *
			 * @since 6.9
			 *
			 * @param {Boolean} skip     Return `true` to skip remaining updates.
			 * @param {Object}  settings Rootline settings.
			 * @param {Object}  Rootline Rootline class.
			 */
			const skip = hooks.applyFilters( 'frm_pro_backend_update_rootline', false, settings, Rootline );
			if ( skip ) {
				return;
			}

			const rootlineWrapper = document.getElementById( 'frm-backend-rootline' );
			const rootlineList    = rootlineWrapper.querySelector( 'ul' );
			const pages           = document.querySelectorAll( '.frm-page-num' );

			Array.from( rootlineList.children ).forEach( el => el.remove() );

			for ( let i = 0; i < pages.length; i++ ) {
				// Add "more" rootline item.
				if ( pages.length - 1 === i ) {
					rootlineList.appendChild(
						Rootline.getRootlineItem(
							{
								title: '',
								number: '...',
								className: 'frm-rootline-item-more frm_hidden'
							},
							settings
						)
					);
				}

				rootlineList.appendChild(
					Rootline.getRootlineItem(
						{
							title: settings.titles[ i ] || wp.i18n.sprintf( wp.i18n.__( 'Page %d', 'formidable-pro' ), i + 1 ),
							number: i + 1
						},
						settings
					)
				);
			}

			rootlineWrapper.setAttribute( 'data-type', settings.type );
			rootlineWrapper.classList.toggle( 'frm-rootline-no-titles', ! settings.titlesOn );
			rootlineWrapper.classList.toggle( 'frm-rootline-no-numbers', ! settings.numbersOn );
			rootlineWrapper.classList.toggle( 'frm-rootline-no-lines', ! settings.linesOn );

			rootlineWrapper.querySelector( '.frm_pages_total' ).innerText = pages.length;

			Rootline.resizeRootline();
		},

		/**
		 * Gets one rootline item.
		 *
		 * @param {Object} data     Rootline item data.
		 * @param {Object} settings Rootline settings.
		 * @return {HTMLElement}
		 */
		getRootlineItem: function( data, settings ) {
			return frmDom.tag( 'li', {
				children: [
					'rootline' === settings.type ? frmDom.tag(
						'span',
						{
							className: 'frm-rootline-number',
							text: data.number
						}
					) : '',
					frmDom.tag(
						'span',
						{
							className: 'frm-rootline-title',
							text: data.title
						}
					)
				],
				className: data.className
			});
		},

		/**
		 * Toggles rootline.
		 *
		 * @param {Boolean} show Show rootline or not.
		 */
		toggleRootline: function( show ) {
			document.getElementById( 'frm-backend-rootline-wrapper' ).classList.toggle( 'frm_hidden', ! show );
		},

		/**
		 * Resizes rootline.
		 */
		resizeRootline: function() {
			if ( ! Rootline.isRootlineAvailable() ) {
				return;
			}

			const rootlineWrapper = document.getElementById( 'frm-backend-rootline' );
			if ( 'rootline' !== rootlineWrapper.dataset.type ) {
				// Don't need to resize progress bar.
				return;
			}

			const width = rootlineWrapper.offsetWidth;
			const itemWidth = rootlineWrapper.classList.contains( 'frm-rootline-no-titles' ) ? 50 : 150;
			const showItems = Math.floor( width / itemWidth );
			const items = rootlineWrapper.querySelectorAll( 'li' );

			items.forEach( ( item, index ) => {
				if ( ! index || items.length - 1 === index ) {
					// Always show the first and last item.
					return;
				}

				if ( items.length < showItems + 2 ) {
					item.classList.toggle( 'frm_hidden', item.classList.contains( 'frm-rootline-item-more' ) );
					return;
				}

				if ( index < showItems - 2 ) {
					item.classList.remove( 'frm_hidden' );
					return;
				}

				item.classList.toggle( 'frm_hidden', ! item.classList.contains( 'frm-rootline-item-more' ) );
			});
		},

		/**
		 * Makes rootline responsive.
		 */
		makeRootlineResponsive: function() {
			window.addEventListener( 'resize', this.resizeRootline );

			window.dispatchEvent( new Event( 'resize' ) );
		}
	};

	function getTimeRangeRegex( stepUnit ) {
		let regex = '^(?:\\d|[01]\\d|2[0-3]):[0-5]\\d';
		if ( 'sec' === stepUnit ) {
			regex += ':[0-5]\\d';
		} else if ( 'millisec' === stepUnit ) {
			regex += ':[0-5]\\d\\:\\d\\d\\d';
		}

		regex += '$';

		return new RegExp( regex );
	}

	function onChangeStepUnit( event ) {
		const stepUnit = event.target.value;
		const regex = getTimeRangeRegex( stepUnit );
		const wrapper = event.target.closest( '.frm-single-settings' );
		const inputs = wrapper.querySelectorAll( '.frm-number-range input[type="text"]' );
		const stepInput = wrapper.querySelector( 'input[id^="frm_step_"]' );
		const singleCheckbox = wrapper.querySelector( 'label[for^="single_time_"]' );

		const getFormattedRangeValue = value => {
			let [ h, m, s, ms ] = value.split( ':' );

			if ( ! h || isNaN( h ) ) {
				h = '00';
			}

			if ( ! m || isNaN( m ) ) {
				m = '00';
			}

			if ( STEP_UNIT_SECOND !== stepUnit && STEP_UNIT_MILLISECOND !== stepUnit ) {
				return h + ':' + m;
			}

			if ( ! s || isNaN( s ) ) {
				s = '00';
			}

			if ( STEP_UNIT_SECOND === stepUnit ) {
				return [ h, m, s ].join( ':' );
			}

			if ( ! ms || isNaN( ms ) ) {
				ms = '000';
			}

			return [ h, m, s, ms ].join( ':' );
		};

		const changeValueFormat = input => {
			if ( input.value.match( regex ) ) {
				return;
			}

			input.value = getFormattedRangeValue( input.value );
		};

		// Change format of time range inputs.
		inputs.forEach( changeValueFormat );

		// Change format of step input. If step setting is empty or is a number, don't change.
		if ( stepInput.value && isNaN( stepInput.value ) ) {
			stepInput.value = getFormattedRangeValue( stepInput.value );
		}

		// Show or hide single time dropdown checkbox.
		if ( STEP_UNIT_SECOND === stepUnit || STEP_UNIT_MILLISECOND === stepUnit ) {
			singleCheckbox.classList.add( 'frm_hidden' );
		} else {
			singleCheckbox.classList.remove( 'frm_hidden' );
		}
	}

	/**
	 * @param {HTMLElement} input
	 * @returns {void}
	 */
	function setStarValues( input ) {
		/*jshint validthis:true */
		const fieldID   = input.id.replace( 'radio_maxnum_', '' );
		const container = document.querySelector( '#field_' + fieldID + '_inner_container .frm-star-group' );

		if ( ! container ) {
			return;
		}

		const fieldKey      = document.getElementsByName( 'field_options[field_key_' + fieldID + ']' )[0].value;
		container.innerHTML = '';

		const min = 1;
		let max   = input.value;
		if ( min > max ) {
			max = min;
		}

		let i, hiddenInput, radioInput, label;
		const fragment = document.createDocumentFragment();
		for ( i = min; i <= max; i++ ) {
			hiddenInput = frmDom.tag( 'input' );
			hiddenInput.setAttribute( 'name', 'field_options[options_' + fieldID + '][' + i + ']' );
			hiddenInput.setAttribute( 'type', 'hidden' );

			radioInput = frmDom.tag( 'input' );
			radioInput.id = 'field_' + fieldKey + '-' + i;
			radioInput.setAttribute( 'type', 'radio' );
			radioInput.setAttribute( 'name', 'item_meta[' + fieldID + ']"' );

			label = frmDom.tag(
				'label',
				{
					className: 'star-rating',
					children: [
						frmDom.svg({ href: '#frm_star_icon' }),
						frmDom.svg({ href: '#frm_star_full_icon' })
					]
				}
			);
			label.setAttribute( 'for', radioInput.id );

			fragment.appendChild( hiddenInput );
			fragment.appendChild( radioInput );
			fragment.appendChild( label );
			fragment.appendChild( document.createTextNode( ' ' ) );
		}

		container.appendChild( fragment );
	}

	/**
	 * Deletes all conditional logic dropdown option elements that correspond to the deleted field option.
	 *
	 * @since 6.12
	 * @param {HTMLElement} option
	 * @return {void}
	 */
	function deleteConditionalLogicOptions( option ) {
		const deletedOption = option.closest( '.frm_single_option' ).querySelector( '.frm_option_key input[type="text"]' );
		if ( ! deletedOption ) {
			return;
		}
		const deletedOptionValue = deletedOption.value;
		const rows               = document.querySelectorAll( '.frm_logic_row' );

		rows.forEach( row => {
			const fieldId = row.id.split( '_' )[ 2 ]; // row.id Example: frm_logic_1234_0 where 1234 is the field id and 0 the conditional logic row.
			const relatedConditionalLogicOption = row.querySelector( 'select[name="field_options[hide_opt_' + fieldId + '][]"] option[value="' + deletedOptionValue + '"]' );
			if ( relatedConditionalLogicOption ) {
				relatedConditionalLogicOption.remove();
			}
		});
	}
	 
	addEventListeners();
	initRichTextFields();
	Rootline.init();

}() );
