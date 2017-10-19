
//Allow for external interactions
window.Elementa_Instances = window.Elementa_Instances || {};

( function( $ ) {

	/**
	 * An Elementa instance
	 */
	function Elementa ( args ) {
		Elementa_Instances[ args.id ] = this;

		this.id 		  	  = args.id
		this.i18         	  = args.translations
		this.$el   			  = $( '#'+ this.id );
		this.check_save   	  = false;
		this.args			  = args

		//Datepickers
		if ( typeof ( $.fn.datepicker ) != "undefined" ) {
			$.fn.wp_elements_datepicker = $.fn.datepicker.noConflict();
			this.$el.find('.wpe-date-control').wp_elements_datepicker();
		}

		//Colorpickers
		if ( typeof ( $.fn.wpColorPicker ) != "undefined" ) {
				this.$el.find('.elementa-color').wpColorPicker({
				change: function( event, ui ) {
					$( this ).closest( '.elementa-color-wrapper' ).find( '.elementa-color-preview' ).css({ backgroundColor: ui.color.toString() });
				},
				clear: function() {
					$( this ).closest( '.elementa-color-wrapper' ).find( '.elementa-color-preview' ).css({ backgroundColor: '' });
				}
			})
		}

		//Select boxes
		if ( typeof ( $.fn.selectize ) != "undefined" ) {
			this.$el.find('select').selectize();
		}

		//Alert boxes
		this.$el.find('.alert-close').on('click', function( e ){
			e.preventDefault();
			$( this ).closest('.alert').remove();
		})

		//Toggle export / import buttons
		this.$el.find('.wpe-export-btn').on('click', function( e ){
			e.preventDefault();
			$( this ).siblings( '.wpe-import' ).addClass('d-none');
			$( this ).siblings( '.wpe-export' ).removeClass('d-none');
		})

		this.$el.find('.wpe-import-btn').on('click', function( e ){
			e.preventDefault();
			$( this ).siblings( '.wpe-import' ).removeClass('d-none');
			$( this ).siblings( '.wpe-export' ).addClass('d-none');
		})

		//Handle data imports whenever data is imported
		var self = this;
		this.$el.find('.wpe-finish-import-btn').on('click', function( e ){
			e.preventDefault();
			var importdata = $( this ).siblings( 'textarea' ).val();
			var feedaback_el = $( this ).siblings( '.alert-warning' ).empty().hide();

			if( importdata.length == 0 ){
				self.log_feedaback( feedaback_el, self.i18.emptyData, 'danger' );
				return;
			}

			try {
				var _importdata = $.parseJSON( importdata );
				self.log_feedaback( feedaback_el, self.i18.importing, 'info' );

				if ( _.isEmpty( _importdata )) {
					//Was valid json but still empty
					self.log_feedaback(feedaback_el, self.i18.emptyJson, 'warning');
					return;
				}

				self.log_feedaback(feedaback_el, self.i18.finalising, 'info');
				var _x = $( '<textarea name="elementa-imported-data" class="d-none"></textarea>' ).val( importdata );
				$( this ).closest('form').append(_x).submit();
			}catch ( error ) {
				//Invalid JSON
				self.log_feedaback(feedaback_el, self.i18.badFormat, 'danger');
				return;
			}

		});

		//Conditionally display sections
		if( args.has_sections ) this.do_sections()

		this.$el.find('.elementa-sub_section-changer').on('click', function( e ){
			e.preventDefault();
			self.args.active_section = $( this ).data('section');
			self.args.active_sub_section = $( this ).data('subSection');

			self.hide_inactive_sub_sections();
		})
	}

	Elementa.prototype.log_feedaback = function ( el, data, type ) {
		return el
				.removeClass( 'alert-danger alert-warning alert-info' )
				.addClass('alert-' + type )
				.html( data )
				.show();
	}

	Elementa.prototype.prompt_exit = function () {
		// If we haven't been passed the event get the window.event
		e = e || window.event;

		// For IE6-8 and Firefox prior to version 4
		if ( e ) {
			e.returnValue = this.i18.exit_prompt;
		}

		window.onbeforeunload = null;

		// For Chrome, Safari, IE8+ and Opera 12+
		return this.i18.exit_prompt;
	}

	Elementa.prototype.do_sections = function () {
		var self = this
		this.args.active_sub_section
		this.hide_inactive_sub_sections( this.args.active_section, this.args.active_section );

		this.$el.
			find('.elementa_sub_section_wrapper.elementa_section_' + this.args.active_section )
			.removeClass('d-none');

		$('.elementa-section-wrapper').on('click', function( e ){
			e.preventDefault();

			//Abort if the current section was clicked
			if( self.args.active_section === $( this ).data('section') ) return;

			//Add the active class to the clicked nav element
			$('.elementa-section-wrapper').removeClass('nav-tab-active');
			$( this ).addClass('nav-tab-active');

			//Save the current section data
			self.args.active_section = $( this ).data('section');
			self.args.active_sub_section = $( this ).data('defaultSection');

			self.hide_inactive_sub_sections();

			//Hide section links
			self.$el.find('.elementa_sub_section_wrapper').addClass('d-none');
			self.$el.
				find('.elementa_sub_section_wrapper.elementa_section_' + self.args.active_section )
				.removeClass('d-none');
		});

	}

	Elementa.prototype.hide_inactive_sub_sections = function () {
		var section 	= this.args.active_section
		var sub_section = this.args.active_sub_section

		this.$el.find('[class*="elementa-sub_section-element-"]').addClass('d-none');
		this.$el.find('.elementa-sub_section-element-' + section + '-' + sub_section ).removeClass('d-none');
	}

	$.Elementa = Elementa
} )( jQuery );
