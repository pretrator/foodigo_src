import $ from 'jquery';
import { View } from 'Backbone';
import _ from 'underscore';
import serializeJSON from 'jquery-serializejson';
import GroupCollection from '../../collections/group';
import Common from '../../common/functions';

export default class GroupItems extends View {

	initialize( data = { field: {}, settings: {} } ) {
		this.data = data;
		this.name = this.data.field.name;
		if ( data.field.fields == undefined ) return;
		this.fields = data.field.fields;
		this.settings = this.data.settings;
		let models = this.settings[this.name] !== undefined ? this.settings[this.name] : {};
		this.groups = new GroupCollection();
		let length = 0;
		if ( typeof models === 'object' ) {
			length = Object.keys( models ).length;
		} else {
			length = models.length;
		}
		for ( let i = 0; i < length; i++ ) {
			this.groups.add( models[i] );
		}
		this.languageFields = [];

		this.template = $( '#pa-group-form-field' ).html();
		this.events = {
			'click .add-new-group-item' 	: '_addNewHandler',
			'click .close'					: '_deleteHandlder',
			'change input, textarea, select': '_onChangeHandler'
		};
		this.groups.on( 'update', () => {
			this._reRender();
		} );
		this.render();
	}

	render() {
		this.data.field.id = this.data.field.id !== undefined ? this.data.field.id : this.data.field.name;
		this.template = _.template( this.template, { variable: 'data' } )( this.data );
		this.setElement( this.template );
		this.$( '.group-content' ).append( _.template( $( '#pa-group-item-add-new' ).html() ) );
		this.groups.map( ( group, index ) => {
			this._renderGroup( group, index );
			this.languageFields = [];
		} );
		return this;
	}

	_renderGroup( group = {}, index = 0 ) {
		let group_item = _.template( $( '#pa-group-item-form-field' ).html(), { variable: 'data' } )( { field: { ...group.toJSON(), ...{ cid: group.cid, label: this.data.field.label, index: index, id: this.data.field.id } } } );
		this.$( '.group-content .group-item-add-new' ).before( group_item );
		if ( this.fields.length > 0 ) {
			let settings = this.settings[this.name] !== undefined && this.settings[this.name][index] !== undefined ? this.settings[this.name][index] : {};

			let field_ID = 0;
			this.fields.map( ( field ) => {
				field = { ...field };
				field.id = field_ID = this.data.field.id;
				field.index = index;
				if ( field.language !== undefined && field.language == true ) {
					this.languageFields.push( field );
					return false;
				}

				if ( field.type == 'select-animate' ) {
					field.type = 'select';
					field.groups = true;
					field.options = PA_PARAMS.animate_groups ? PA_PARAMS.animate_groups : PA_PARAMS.animates;
				}
				// set default values
				field.value = settings[field.name] !== undefined ? settings[field.name] : ( field.default !== undefined ? field.default : '' );
				field.name = this.data.field.name + '[' + index + ']' + '[' + field.name + ']';

				let formGroup = '';
				let innerField = _.template( $( '#pa-' + field.type + '-form-field' ).html(), { variable: 'data' } )( { field: field, settings: settings } );
				formGroup = _.template( $( '#pa-group-wrapper-form-field' ).html(), { variable: 'data' } )( { innerHTML: innerField, field: field, settings: settings } );
				this.$( '.group-content #' + field.id + '-' + index + ' .group-body' ).append( formGroup );
			} );

			if ( Object.keys( this.languageFields ).length > 0 ) {
				this.$( '.group-content #' + field_ID + '-' + index + ' .group-body' ).append( _.template( $( '#pa-group-languages-panel' ).html(), { variable: 'data' } )( { index: index } ) );
				_.map( window.PA_PARAMS.languages_list, ( language, key ) => {
					language = { ...language }
					_.map( this.languageFields, ( field, key ) => {
						field = { ...field };

						let value = settings.languages !== undefined && settings.languages[language.code] !== undefined && settings.languages[language.code][field.name] !== undefined ? settings.languages[language.code][field.name] : false;
						field.value = ! value && field.value !== '' ? field.value : ( value ? value : field.default !== undefined ? field.default : '' );
						let name = this.data.field.name + '[' + index + '][languages][' + language.code + '][' + field.name + ']';
						field.name = name;

						let formGroup = '';
						let innerField = _.template( $( '#pa-' + field.type + '-form-field' ).html(), { variable: 'data' } )( { field: field, settings: settings } );
						formGroup = _.template( $( '#pa-group-wrapper-form-field' ).html(), { variable: 'data' } )( { innerHTML: innerField, field: field, settings: settings } );
						this.$( '.group-content #' + field_ID + '-' + index + ' .group-body #language-' + index + language.language_id ).append( formGroup );
					} );
				} );
			}
			// init scripts
			Common.init_thirdparty_scripts();
		}
	}

	_reRender() {
		this.$el.replaceWith( this.render().el );
	}

	_addNewHandler( e ) {
		e.preventDefault();
		if ( this.data.field.length == 0 ) return;
		this.groups.add({});
		return false;
	}

	_deleteHandlder( e ) {
		e.preventDefault();
		if ( confirm( $( e.target ).data( 'confirm' ) ) ) {
			let button = $( e.target );
			let group = button.parents( '.panel-default:first' );
			let cid = group.data( 'cid' );
			let model = this.groups.get( { cid: cid } );
			this.groups.remove( model );
		}
		return false;
	}

	_onChangeHandler( e ) {
		e.preventDefault();
		let jsons = this.$('input,textarea,select').serializeJSON();
		this.settings = jsons;
		// this.groups.map( ( model, index ) => {
		// 	let values = jsons[this.name] !== undefined && jsons[this.name][index] !== undefined ? jsons[this.name][index] : {};
		// 	_.map( values, ( value, name ) => {
		// 		model.set( name, value );
		// 		console.log( model.get( name ) );
		// 	} );
		// } );
		return false;
	}

}