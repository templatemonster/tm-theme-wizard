( function( $, settings ) {

	'use strict';

	var tmThemeWizard = {
		css: {
			start: '[data-theme-wizard="start-install"]',
			form: '.theme-wiazrd-form',
			input: '.wizard-input'
		},

		init: function() {

			$( document )
				.on( 'focus.tmThemeWizard', tmThemeWizard.css.start, tmThemeWizard.startInstall )
				.on( 'click.tmThemeWizard', tmThemeWizard.css.input, tmThemeWizard.clearErrors );
		},

		startInstall: function() {

			var $this  = $( this ),
				$form  = $this.closest( tmThemeWizard.css.form ),
				$input = $( tmThemeWizard.css.input, $form ),
				errors = false,
				data   = {
					action: 'tm_theme_wizard_verify_data',
					nonce: settings.nonce
				};

			event.preventDefault();

			$input.each( function( index, el ) {
				var $this = $( this ),
					name  = $this.attr( 'name' ),
					val   = $this.val();

				if ( '' === val ) {
					tmThemeWizard.addError( $( this ), settings.errors.empty );
					errors = true;
				}

				data[ name ] = val;
			});

			if ( true === errors || $this.hasClass( 'in-progress' ) ) {
				return;
			}

			$this.addClass( 'in-progress' );
			tmThemeWizard.clearErrors();

			$.ajax({
				url: ajaxurl,
				type: 'get',
				dataType: 'json',
				data: data
			}).done( function( response ) {

				if ( true === response.success ) {
					tmThemeWizard.addLog( $this, response.data.message );
					return;
				}

				tmThemeWizard.addError( $this, response.data.message );
				$this.removeClass( 'in-progress' );
			});

		},

		installTheme: function() {

		},

		addLog: function ( $target, log ) {
			$target.after('<div class="wizard-log">' + log + '</div>');
		},

		addError: function( $target, error ) {

			if ( $target.hasClass( 'wizard-error' ) ) {
				return;
			}

			$target.addClass( 'wizard-error' ).after('<div class="wizard-error-message">' + error + '</div>');
		},

		clearErrors: function() {
			var $this = $( this );

			if ( $this.hasClass( 'wizard-error' ) ) {
				$this.removeClass( 'wizard-error' ).next( '.wizard-error-message' ).remove();
			}

		}
	};

	tmThemeWizard.init();

}( jQuery, window.tmThemeWizardSettings ) );