/** global frmAcfData */
( function() {
	'use strict';

	const Helpers = {
		/**
		 * Does the same as jQuery( document ).on( 'event', 'selector', handler ).
		 *
		 * @param {String}         event    Event name.
		 * @param {String}         selector Selector.
		 * @param {Function}       handler  Handler.
		 * @param {Boolean|Object} options  Options to be added to `addEventListener()` method. Default is `false`.
		 */
		documentOn: function( event, selector, handler, options ) {
			if ( 'undefined' === typeof options ) {
				options = false;
			}

			document.addEventListener( event, function( e ) {
				var target;

				// loop parent nodes from the target to the delegation node.
				for ( target = e.target; target && target != this; target = target.parentNode ) {
					if ( target.matches( selector ) ) {
						handler.call( target, e );
						break;
					}
				}
			}, options );
		},

		triggerNative: function( el, eventName ) {
			const event = document.createEvent( 'HTMLEvents' );
			event.initEvent( eventName, true, false );
			el.dispatchEvent( event );
		},

		appendElString: function( parent, string ) {
			parent.insertAdjacentHTML( 'beforeend', string );
		},

		/**
		 * Gets ACF fields from a field group.
		 *
		 * @param {String} groupKey ACF field group key.
		 * @return {Array}
		 */
		getAcfFields: function( groupKey ) {
			return frmAcfData.acf_fields[ groupKey ] || [];
		},

		getFrmField: fieldId => frmAcfData.frm_ids_fields[ fieldId ] || false,

		getAcfField: name => frmAcfData.acf_names_fields[ name ] || false,

		isCompatibleType: function( frmType, acfField ) {
			if ( 'undefined' === typeof frmAcfData.compatible_types[ frmType ] ) {
				return false;
			}

			return -1 < frmAcfData.compatible_types[ frmType ].indexOf( acfField.type );
		},

		clearOptions: function( select ) {
			select.querySelectorAll( 'option:not(:first-child)' ).forEach( option => option.remove() );
			if ( select.nextElementSibling && 'INPUT' === select.nextElementSibling.tagName ) {
				select.nextElementSibling.value = '';
			}
		},

		appendOption: function( select, value, label ) {
			const option = document.createElement( 'option' );
			option.value = value;
			option.innerText = label;
			select.appendChild( option );
		},

		appendMappingRow: function( mapping, wrapperEl ) {
			const groupKey = Helpers.getGroupKey();

			if ( ! mapping ) {
				mapping = {};
			}

			const tmpl = wp.template( 'frm-acf-mapping-row' );
			const tmplData = {
				mapping: mapping,
				frmFields: frmAcfData.frm_fields,
				acfFields: [],
				frmFieldId: mapping.field_id,
				acfFieldName: mapping.meta_name,
				strings: frmAcfData.strings
			};

			const frmField = tmplData.frmFieldId ? Helpers.getFrmField( tmplData.frmFieldId ) : false;

			if ( frmField ) {
				tmplData.frmField = frmField;

				tmplData.acfFields = Helpers.getAcfFields( groupKey ).filter( field => {
					return Helpers.isCompatibleType( frmField.type, field );
				});

				if ( frmField.child_fields ) {
					tmplData.showChildFrmFields = Helpers.showChildFrmFields;
					tmplData.showChildAcfFields = Helpers.showChildAcfFields;
				}
			}

			Helpers.appendElString( wrapperEl, tmpl( tmplData ) );
		},

		getGroupKey: function() {
			return document.getElementById( 'frm_acf_select_field_group' ).value;
		},

		showDuplicateModal: () => frmAdminBuild.infoModal( frm_admin_js.field_already_used ),

		showChildFrmFields: data => {
			const field = data.frmField || Helpers.getFrmField( data.frmFieldId );
			if ( ! field || ! field.child_fields || ! field.child_fields.length ) {
				return '';
			}

			let subFields = '<ul>';

			field.child_fields.forEach( childField => {
				subFields += '<li class="frm_acf_frm_sub_field">';
				subFields += '<label>' + childField.name + '</label>';
				subFields += '<input type="hidden" name="frm_acf_frm_sub_fields[' + field.id + '][]" value="' + childField.id + '" />';
				subFields += '</li>';
			});

			subFields += '</ul>';
			return subFields;
		},

		showChildAcfFields: data => {
			const field = data.frmField || Helpers.getFrmField( data.frmFieldId );
			if ( ! field || ! field.child_fields || ! field.child_fields.length ) {
				return '';
			}

			const acfField      = data.acfFieldName ? frmAcfData.acf_names_fields[ data.acfFieldName ] : false;
			const idNameMapping = {};
			if ( data.mapping && data.mapping.child_mapping && data.mapping.child_mapping.length ) {
				data.mapping.child_mapping.forEach( mapping => {
					idNameMapping[ mapping.field_id ] = mapping.meta_name;
				});
			}

			let subfields = '<ul>';
			const disabled = acfField ? '' : ' disabled ';

			field.child_fields.forEach( childField => {
				const selectedAcfName = idNameMapping[ childField.id ] || '';

				subfields += '<li class="frm_acf_acf_sub_field">';
				subfields += '<select name="frm_acf_acf_sub_fields[' + field.id + '][]" ' +
					'data-frm-type="' + childField.type + '" ' +
					disabled +
					'class="frm_custom_field_key">';
				subfields += '<option value="">Select sub field</option>';

				if ( acfField && acfField.sub_fields ) {
					acfField.sub_fields.forEach( acfChildField => {
						if ( ! Helpers.isCompatibleType( childField.type, acfChildField ) ) {
							return;
						}

						const selected = selectedAcfName === acfChildField.name ? ' selected' : '';

						subfields += '<option value="' + acfChildField.name + '"' + selected + '>' + acfChildField.label + '</option>';
					});
				}

				subfields += '</select>';
				subfields += '</li>';
			});

			subfields += '</ul>';
			return subfields;
		}
	};

	const EventHandlers = {

		onClickAddRow: function( ev ) {
			ev.preventDefault();

			const wrapperEl = ev.target.closest( '#frm_acf_mapping' );

			Helpers.appendMappingRow( false, wrapperEl );

			document.getElementById( 'frm_acf_mapping_wrapper' ).classList.add( 'frm_acf_mapping_has_field' );
		},

		onClickRemoveRow: function( ev ) {
			ev.preventDefault();

			ev.target.closest( '.frm_acf_mapping_row' ).remove();
			if ( ! document.querySelector( '.frm_acf_mapping_row' ) ) {
				document.getElementById( 'frm_acf_mapping_wrapper' ).classList.remove( 'frm_acf_mapping_has_field' );
			}
		},

		onChangeFieldGroup: function( ev ) {
			const groupKey = ev.target.value;
			const acfFieldSelects = document.querySelectorAll( '.frm_custom_field_key' );

			acfFieldSelects.forEach( acfFieldSelect => {
				Helpers.clearOptions( acfFieldSelect );
			});

			EventHandlers.showHideMapping( groupKey );

			if ( ! groupKey ) {
				return;
			}

			document.querySelectorAll( '.frm_acf_select_frm_field' ).forEach( frmSelect => {
				Helpers.triggerNative( frmSelect, 'change' );
			});

			// Show the first empty row each time changing the field group and there is no row.
			if ( ! acfFieldSelects.length ) {
				const addBtn = document.querySelector( '#frm_acf_add_row_wrapper a' );
				Helpers.triggerNative( addBtn, 'click' );
			}
		},

		onChangeFrmField: function( ev ) {
			const acfSelect   = ev.target.closest( '.frm_grid_container' ).querySelector( '.frm_custom_field_key' );
			const firstAcfOpt = acfSelect.querySelector( 'option:first-child' );
			if ( ! ev.target.value ) {
				// If no field is selected.
				acfSelect.setAttribute( 'disabled', true );
				firstAcfOpt.innerHTML = frmAcfData.strings.select_frm_first;
				return;
			}

			acfSelect.removeAttribute( 'disabled' );
			firstAcfOpt.innerHTML = frmAcfData.strings.select_field;

			const groupKey = Helpers.getGroupKey();
			if ( ! groupKey ) {
				return;
			}

			const frmFieldId  = ev.target.value;
			const frmField    = Helpers.getFrmField( frmFieldId );
			if ( ! frmField ) {
				return;
			}

			const acfFields   = Helpers.getAcfFields( groupKey );
			const selectedOpt = ev.target.options[ ev.target.selectedIndex ];

			ev.target.closest( '.frm_grid_container' ).querySelectorAll( 'ul' ).forEach( el => el.remove() );

			Helpers.clearOptions( acfSelect );

			EventHandlers.checkDupFrmField( ev );

			if ( ! ev.target.value ) {
				return;
			}

			acfFields.forEach( acfField => {
				if ( ! Helpers.isCompatibleType( frmField.type, acfField ) ) {
					return;
				}

				if ( 'divider' === frmField.type ) {
					if ( parseInt( frmField.field_options.repeat ) && 'group' === acfField.type ) {
						return;
					}

					if ( ! parseInt( frmField.field_options.repeat ) && 'repeater' === acfField.type ) {
						return;
					}
				}

				Helpers.appendOption( acfSelect, acfField.name, acfField.label );
			});

			if ( frmField.child_fields ) {
				const childFrmFields = Helpers.showChildFrmFields({
					frmField: frmField
				});

				const childAcfFields = Helpers.showChildAcfFields({
					frmField: frmField
				});

				Helpers.appendElString( ev.target.parentNode, childFrmFields );
				Helpers.appendElString( acfSelect.parentNode, childAcfFields );
			} else {
				if ( ev.target.nextElementSibling ) {
					ev.target.nextElementSibling.remove();
				}

				if ( acfSelect.nextElementSibling.nextElementSibling ) {
					acfSelect.nextElementSibling.nextElementSibling.remove();
				}
			}
		},

		loadFieldsMapping: function() {
			if ( ! frmAcfData.mapping || {} === frmAcfData.mapping ) {
				return;
			}

			const wrapperEl = document.getElementById( 'frm_acf_mapping' );

			frmAcfData.mapping.forEach( mapping => {
				if ( ! mapping.is_acf ) {
					return;
				}
				Helpers.appendMappingRow( mapping, wrapperEl );
			});
		},

		checkMappingDisplayOnLoad: function() {
			const groupKey = frmAcfData.action_settings.acf_field_group;
			this.showHideMapping( groupKey );
		},

		showHideMapping: function( groupKey ) {
			const mappingEl = document.getElementById( 'frm_acf_mapping' );
			const emptyFieldEl = document.getElementById( 'frm_acf_empty_acf_fields' );
			const hideClass    = 'frm_hidden';

			if ( ! groupKey ) {
				mappingEl.classList.add( hideClass );
				emptyFieldEl.classList.add( hideClass );
				return;
			}

			if ( 'object' === typeof frmAcfData.acf_fields[ groupKey ] && frmAcfData.acf_fields[ groupKey ].length ) {
				mappingEl.classList.remove( hideClass );
				emptyFieldEl.classList.add( hideClass );
				return;
			}

			mappingEl.classList.add( hideClass );
			emptyFieldEl.classList.remove( hideClass );
		},

		checkDupMetaKey: function( ev ) {
			EventHandlers.checkDupInput( ev, '.frm_custom_field_key' );
		},

		checkDupFrmField: function( ev ) {
			EventHandlers.checkDupInput( ev, '.frm_single_post_field' );
		},

		checkDupInput: function( ev, inputSelector ) {
			const inputs = document.querySelectorAll( inputSelector );

			inputs.forEach( input => input.style.borderColor = '' );

			if ( ! ev.target.value ) {
				return;
			}

			for ( let i = 0; i < inputs.length; i++ ) {
				if ( inputs[ i ] === ev.target || ev.target.value !== inputs[ i ].value ) {
					continue;
				}

				Helpers.showDuplicateModal();
				inputs[ i ].style.borderColor = 'red';
				ev.target.value = '';
				break;
			}
		},

		onChangeAcfField: ev => {
			if ( ! ev.target.nextElementSibling ) {
				// This is a ACF dropdown inside Repeater.
				return;
			}

			const acfField = Helpers.getAcfField( ev.target.value );

			if ( 'INPUT' === ev.target.nextElementSibling.tagName ) {
				ev.target.nextElementSibling.value = acfField && acfField.key ? acfField.key : '';
			}

			const childUl = ev.target.nextElementSibling.nextElementSibling;

			if ( ! childUl ) {
				return;
			}

			const childAcfSelects = childUl.querySelectorAll( 'select' );
			const childAcfOptions = childUl.querySelectorAll( 'option:not(:first-child)' );
			childAcfSelects.forEach( select => {
				select.value = '';
			});

			// Remove all fields first.
			childAcfOptions.forEach( option => option.remove() );

			if ( ! ev.target.value ) {
				// Disable child field selects.
				childAcfSelects.forEach( select => {
					select.setAttribute( 'disabled', 'disabled' );
				});
				return;
			}

			// Then append proper fields.
			if ( ! acfField || ! acfField.sub_fields ) {
				return;
			}

			childAcfSelects.forEach( ( select, index ) => {
				select.removeAttribute( 'disabled' );
				acfField.sub_fields.forEach( subField => {
					if ( ! Helpers.isCompatibleType( select.dataset.frmType, subField ) ) {
						return;
					}
					Helpers.appendOption( select, subField.name, subField.label );
				});
			});
		},

		init: function() {
			Helpers.documentOn( 'change', '#frm_acf_select_field_group', this.onChangeFieldGroup );
			Helpers.documentOn( 'click', '.frm_acf_add_row', this.onClickAddRow );
			Helpers.documentOn( 'click', '.frm_acf_remove_row', this.onClickRemoveRow );
			Helpers.documentOn( 'change', '.frm_acf_select_frm_field', this.onChangeFrmField );
			Helpers.documentOn( 'change', '.frm_custom_field_key', this.checkDupMetaKey );
			Helpers.documentOn( 'change', '.frm_acf_mapping_row .frm_custom_field_key', this.onChangeAcfField );

			wp.hooks.addAction(
				'frm_filled_form_action',
				'frm_acf',
				function( insideEl ) {
					const dataEl = insideEl[0].querySelector( '#frm_acf_form_action_data' );
					if ( ! dataEl ) {
						return;
					}

					const formActionData = JSON.parse( insideEl[0].querySelector( '#frm_acf_form_action_data' ).value );
					frmAcfData.mapping = formActionData.mapping;
					frmAcfData.action_settings = formActionData.action_settings;

					EventHandlers.loadFieldsMapping();
					EventHandlers.checkMappingDisplayOnLoad();
				}
			);
		}
	};

	EventHandlers.init();
}() );
