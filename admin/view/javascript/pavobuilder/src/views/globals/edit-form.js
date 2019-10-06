import Backbone from 'Backbone';
import _ from 'underscore';
import Common from '../../common/functions';
import serializeJSON from 'jquery-serializejson';
import ElementModel from '../../models/element';
import GroupItems from './group';
import draggable from 'jquery-ui/ui/widgets/draggable';

export default class EditForm extends Backbone.View {

	/**
	 * Constructor class
	 */
	initialize( data = { settings: {} }, title = '', fields = [] ) {
		// super();
		// data is a Model
		this.data = data;
		this.title = title;
		this.fields = fields;

		// this.data.set( 'fields', fields );
		this.template = _.template( $( '#pa-edit-form-template' ).html(), { variable: 'data' } );
		this.listenTo( this.data, 'change:editing', this._toggle_form );
		this.listenTo( this.data, 'destroy', this.remove );

		this.events = {
			'click .btn.pa-close, .close'		: '_closeHandler',
			'click .btn.pa-update'				: '_updateHandler',
			'change #animate-select'			: '_effectChange',
			'change #disable_padding_margin'	: ( e ) => {
				let val = $( e.target ).val();
				let onion = this.$( '.pa-layout-onion' ).parents( '.form-group:first' );

				if ( val == 1 ) {
					onion.addClass( 'hide' );
				} else {
					onion.removeClass( 'hide' );
				}
			},
			'change select'						: (e) => {
				e.preventDefault();
				let val = $(e.target).val();
				let name = $(e.target).data('name');

				// trigger change select
				Backbone.trigger('pa-trigger-change-select', {
					name: name,
					value: val
				});
				return false;
			}
		}

		// close other modal
		Backbone.on( 'pa-close-modal', ( args ) => {
			if ( args.cid !== undefined && args.cid !== this.data.cid ) {
				this._close();
			}
		} );

		// trigger relation
		Backbone.on('pa-trigger-change-select', (args) => {
			let name = args.name;
			let value = args.value;
			let relations = this.$('[data-relation="'+name+'"]');
			if ( relations.length !== 0 ) {
				for ( let i = 0; i < relations.length; i++ ) {
					let relation = $(relations[i]);
					if ( value == relation.data('relation-value') ) {
						relation.removeClass( 'hide' );
					} else {
						relation.addClass( 'hide' );
					}
				}
			}
		});

		// this.render();
		this.listenTo( this.data, 'change:editing', this.render );
	}

	/**
	 * Render html
	 */
	render() {
		if ( this.data.get( 'editing' ) == true ) {
			// close other modal
			Backbone.trigger( 'pa-close-modal', { cid: this.data.cid } );

			let data = this.data.toJSON();
			data.edit_title = this.title;
			let template = this.template( data );
			// set element
			this.setElement( template );
			if ( $( 'body' ).find( this.$el ).length === 0 ) {
				$( 'body' ).append( this.el );
			}

			// $( 'body' ).find( this.$el ).modal( 'show' );
			this.$el.draggable({
				handle: '.modal-header, .modal-footer',
				containment: '#container'
			});
			this._calculateHeight();
			$( 'body' ).find( this.$el ).on( 'hidden.bs.modal', ( e ) => {
				this.data.set( 'editing', false );
			} );

			if ( this.data.get( 'element_type' ) == 'module' ) {
				let url = PA_PARAMS.site_url + 'admin/index.php?route=extension/module/' + this.data.get( 'moduleCode' ) + '&module_id='+ this.data.get( 'moduleId' ) + '&user_token=' + PA_PARAMS.user_token;
				let html = '<div class="loading text-center"><i class="fa fa-spinner fa-spin" aria-hidden="true"></i></div>';
				html += '<iframe id="pa-iframe-edit-module" src="' + url + '"></iframe>';
				this.$( '#pa-edit-form-settings' ).replaceWith( html );
				let loaded = true;
				this.$( '#pa-iframe-edit-module' ).on( 'load', () => {
					loaded = ! loaded;
					if ( loaded ) {
						this.data.set( 'editing', false );
					} else {
						this.$( '.loading' ).remove();
				 		this.$( '#pa-iframe-edit-module' ).contents().find( '#header' ).remove();
				 		this.$( '#pa-iframe-edit-module' ).contents().find( '#column-left' ).remove();
				 		this.$( '#pa-iframe-edit-module' ).contents().find( '#footer' ).remove();
				 		this.$( '.pa-update' ).remove();
					}
	 			} );
			} else {
				// render fields
				this.renderFields();
			}
		} else {
			this._close();
		}
		return this;
	}

	/**
	 * render edit fields
	 */
	renderFields() {
		let settings = { ...this.data.get( 'settings' ) };
		let globalSettings = {};
		if ( this.data.get( 'widget' ) !== undefined && this.fields.length == 0 ) {
			if ( PA_PARAMS.element_fields[ this.data.get( 'widget' ) ] !== undefined ) {
				this.fields = PA_PARAMS.element_fields[ this.data.get( 'widget' ) ];
				this.$( '#pa-edit-form-settings' ).addClass( this.data.get( 'widget' ) )
			}
		}

		let current_screen = this.data.get( 'screen' );
		let responsive = this.data.get( 'responsive' );
		current_screen = current_screen !== undefined ? current_screen : 'lg';
		if ( current_screen ) {
			let screen_settings = responsive !== undefined && responsive[current_screen] !== undefined && responsive[current_screen].settings !== undefined ? responsive[current_screen].settings : {};

			// calculate global settings
			if ( settings.background_video !== undefined ) {
				globalSettings.background_video = settings.background_video;
			}
			_.map(this.fields, (group, groupName) => {
				let is_global = group.is_global !== undefined && group.is_global;
				_.map(group.fields, (field) => {
					let name = field.name;
					if ( settings[name] !== undefined ) {
						if (groupName === 'general' || is_global) {
							globalSettings[name] = settings[name];
						} else {

						}
					}
				});
				_.map( window.PA_PARAMS.languages_list, ( opt, name ) => {
					if ( settings[name] !== undefined ) {
						globalSettings[name] = settings[name];
					}
				} );
			});
			if ( Object.keys( screen_settings ).length > 0 ) {
				settings = { ...globalSettings, ...screen_settings };
			}

		}

		let tabs = [];
		_.map( this.fields, ( fields, tab ) => {
			tabs.push({
				tab: tab,
				label: fields.label// != undefined ? PA_PARAMS.languages[fields.label] : ''
			});
		} );

		if ( tabs.length > 0 ) {
			this.$( '.modal-header' ).append( _.template( $( '#pa-modal-panel-heading' ).html(), { variable: 'data' } )( { tabs: tabs } ) );
			this.$( '#pa-edit-form-settings' ).html( _.template( $( '#pa-modal-panel' ).html(), { variable: 'data' } )( { tabs: tabs } ) );
			// let settings = this.data.get( 'settings' );
			// clone new settings
			let cloneSettings = { ...settings };
			// render fields inside modal content
			_.map( this.fields, ( fields, tab ) => {
				// clone to new object is required
				let clonefields = { ...fields };
				this._renderFields( clonefields, tab, cloneSettings );
			} );
		}

		// init thirdparty scripts
		Common.init_thirdparty_scripts( this.data );

		this.summernote();
	}

	_renderFields( clonefields = {}, tab = '', cloneSettings = {} ) {
		let languageFields = [];
		_.map( clonefields.fields, ( field, key ) => {
			let cloneField = { ...field };
			if ( cloneField.language !== undefined && cloneField.language == true ) {
				languageFields.push( cloneField );
				return false;
			}

			if ( cloneField.type == 'select-animate' ) {
				// cloneField.type = 'select';
				cloneField.groups = true;
				cloneField.none = true;
				cloneField.options = PA_PARAMS.animate_groups ? PA_PARAMS.animate_groups : PA_PARAMS.animates;
			}

			// set default values
			cloneField.value = cloneSettings[cloneField.name] !== undefined ? cloneSettings[cloneField.name] : ( cloneField.default !== undefined ? cloneField.default : '' );
			if ( cloneField.name === 'background_video' ) {
				cloneField.value = cloneSettings.background_video !== undefined ? cloneSettings.background_video : '';
			}
			if ( cloneField.type !== 'group' ) {
				cloneField.value = cloneField.value ? cloneField.value : '';
				let innerField = _.template( $( '#pa-' + cloneField.type + '-form-field' ).html(), { variable: 'data' } )( { field: cloneField, settings: cloneSettings, widget: this.data.get( 'widget' ) } );
				let formGroup = '';
				if ( cloneField.type !== 'layout-onion' ) {
					formGroup = _.template( $( '#pa-group-wrapper-form-field' ).html(), { variable: 'data' } )( { innerHTML: innerField, field: cloneField, settings: cloneSettings, widget: this.data.get( 'widget' ) } );
				} else {
					formGroup = innerField;
				}
				this.$( '#nav-' + tab ).append( formGroup );
			} else {
				this.$( '#nav-' + tab ).append( new GroupItems( { field: cloneField, settings: cloneSettings } ).el );
			}
		} );

		if ( Object.keys( languageFields ).length > 0 ) {
			this.$( '#nav-' + tab ).append( _.template( $( '#pa-languages-panel' ).html(), { variable: 'data' } ) );
			_.map( window.PA_PARAMS.languages_list, ( language, key ) => {
				_.map( languageFields, ( field, key ) => {
					let cloneField = { ...field };
					let value = cloneSettings[language.code] !== undefined && cloneSettings[language.code][cloneField.name] !== undefined ? cloneSettings[language.code][cloneField.name] : false;
					cloneField.value = ! value && cloneField.value !== '' ? cloneField.value : ( value ? value : cloneField.default !== undefined ? cloneField.default : '' );
					let name = language.code + '[' + cloneField.name + ']';
					cloneField.name = name;

					let innerField = _.template( $( '#pa-' + cloneField.type + '-form-field' ).html(), { variable: 'data' } )( { field: cloneField, settings: cloneSettings } );
					let formGroup = _.template( $( '#pa-group-wrapper-form-field' ).html(), { variable: 'data' } )( { innerHTML: innerField, field: cloneField, settings: cloneSettings, widget: this.data.get( 'widget' ) } )
					this.$( '#language' + language.language_id ).append( formGroup );
				} );
			} );
		}
	}

	summernote() {
		$.each( $('.pa-editor'), function() {
			var element = this;
			if ($(this).attr('data-lang')) {
				$('head').append('<script type="text/javascript" src="view/javascript/summernote/lang/summernote-' + $(this).attr('data-lang') + '.js"></script>');
			}

			$(element).summernote({
				lang: $(this).attr('data-lang'),
				disableDragAndDrop: true,
				height: 300,
				emptyPara: '',
				codemirror: { // codemirror options
					mode: 'text/html',
					htmlMode: true,
					lineNumbers: true,
					theme: 'monokai'
				},
				fontsize: ['8', '9', '10', '11', '12', '14', '16', '18', '20', '24', '30', '36', '48' , '64'],
				toolbar: [
					['style', ['style']],
					['font', ['bold', 'underline', 'clear']],
					['fontname', ['fontname']],
					['fontsize', ['fontsize']],
					['color', ['color']],
					['para', ['ul', 'ol', 'paragraph']],
					['table', ['table']],
					['insert', ['image']],//['link', 'image', 'video']
					['view', ['fullscreen', 'codeview', 'help']]
				],
				popover: {
	           		image: [
						['custom', ['imageAttributes']],
						['imagesize', ['imageSize100', 'imageSize50', 'imageSize25']],
						['float', ['floatLeft', 'floatRight', 'floatNone']],
						['remove', ['removeMedia']]
					],
				},
				buttons: {
	    			image: function() {
						var ui = $.summernote.ui;
						// create button
						var button = ui.button({
							contents: '<i class="note-icon-picture" />',
							tooltip: $.summernote.lang[$.summernote.options.lang].image.image,
							click: function () {
								$('#modal-image').remove();
								$.ajax({
									url: 'index.php?route=common/filemanager&user_token=' + getURLVar('user_token'),
									dataType: 'html',
									beforeSend: function() {
										$('#button-image i').replaceWith('<i class="fa fa-circle-o-notch fa-spin"></i>');
										$('#button-image').prop('disabled', true);
									},
									complete: function() {
										$('#button-image i').replaceWith('<i class="fa fa-upload"></i>');
										$('#button-image').prop('disabled', false);
									},
									success: function(html) {
										$('body').append('<div id="modal-image" class="modal">' + html + '</div>');

										$('#modal-image').modal('show');

										$('#modal-image').delegate('a.thumbnail', 'click', function(e) {
											e.preventDefault();

											$(element).summernote('insertImage', $(this).attr('href'));

											$('#modal-image').modal('hide');
										});
									}
								});
							}
						});

						return button.render();
					},
					link: function() {
						var ui = $.summernote.ui;
					}
	  			}
			});
		} );
	}

	/**
	 * Toggle show edit form
	 */
	_toggle_form( model ) {
		if ( model.get( 'editing' ) === false ) {
			$( 'body' ).find( this.$el ).modal( 'hide' );
			this.remove();
		}
	}

	/**
	 * Close handler click
	 */
	_closeHandler( e ) {
		e.preventDefault();
		this._close();
		return false;
	}

	/**
	 * Close modal and, set 'editing' false
	 *
	 * when model change 'setting' to false view will be lose
	 */
	_close() {
		$( 'body' ).find( this.$el ).modal( 'hide' );
		$( 'body' ).find( '.sp-container' ).remove();
		this.data.set( 'editing', false );
	}

	/**
	 * Update data settings
	 */
	_updateHandler( e ) {
		e.preventDefault();
		new Promise( ( resolve, reject ) => {
			let formSettings = this.$el.find( '#pa-edit-form-settings' ).serializeJSON();
			let globalSettings = { ...this.data.get( 'settings' ), ...{ uniqid_id: formSettings.uniqid_id } };
			let screenSettings = {};
			let responsive = { ...this.data.get( 'responsive' ) };
			let current_screen = this.data.get( 'screen' );
			current_screen = current_screen === undefined ? 'lg' : current_screen;

			// calculate global settings
			_.map( this.fields, ( group, name ) => {
				let is_global = group.is_global !== undefined && group.is_global;
				_.map( group.fields, ( field ) => {
					if ( field.language !== undefined && field.language ) {
						_.map( window.PA_PARAMS.languages_list, ( lang, code ) => {
							if ( formSettings[code] !== undefined && formSettings[code][field.name] !== undefined ) {
								if ( globalSettings[code] == undefined || globalSettings[code].length === 0 ) globalSettings[code] = {};
								globalSettings[code][field.name] = formSettings[code][field.name];
							}
						} );
					} else if ( name == 'general' || is_global) {
						// name !== 'background' && name !== 'style'
						globalSettings[field.name] = formSettings[field.name] !== undefined ? formSettings[field.name] : '';
					} else {
						if ( field.name === 'background_video' ) {
							globalSettings[field.name] = formSettings[field.name] !== undefined ? formSettings[field.name] : '';
						} else {
							screenSettings[field.name] = formSettings[field.name] !== undefined ? formSettings[field.name] : '';
						}
					}
				} );
			} );

			screenSettings.selectors = globalSettings.selectors !== undefined ? globalSettings.selectors : {};
			// set global settings
			this.data.set( 'settings', globalSettings );

			if ( responsive === undefined ) {
				responsive = {};
			}
			if ( responsive[current_screen] === undefined ) {
				responsive[current_screen] = {};
			}

			responsive[current_screen].settings = screenSettings;
			this.data.set( 'responsive', responsive );

			if ( this.data instanceof ElementModel ) {
				this.data.set( 'reRender', true );
			}
			// call close method
			resolve();
		} ).then(() => {
			this._close();
		});
		return false;
	}

	/**
	 * change data effect
	 */
	_effectChange( e ) {
		e.preventDefault();
		let select = $( e.target );
		let effect = select.val();

		this.$( '#animate-preview' ).addClass( effect + ' animated' );
		setTimeout( () => {
			this.$( '#animate-preview' ).removeClass( effect + ' animated' );
		}, 1000 );
		return false;
	}

	/**
	 * calculate modal height
	 */
	_calculateHeight() {
		let winHeight = $( window ).height();
		let modalBodyHeight = winHeight - this.$( '.modal-footer' ).height() - this.$( '.modal-header' ).height() - 15 * 6;
		this.$( '.modal-body' ).css( { 'max-height': modalBodyHeight, 'overflow-y': 'scroll' } );
	}

}