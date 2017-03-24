( function( $, settings ) {

	'use strict';

	var tmThemeWizard = {
		css: {
			start: '[data-theme-wizard="start-install"]',
		},

		init: function() {

			$( document )
				.on( 'click.tmThemeWizard', tmThemeWizard.css.start, tmThemeWizard.startInstall );
		},

		startInstall: function() {

			var $this   = $( this );

			event.preventDefault();

			$this.addClass( 'in-progress' );

			$.ajax({
				url: ajaxurl,
				type: 'get',
				dataType: 'json',
				data: {
					action: 'tm_wizard_store_plugins',
					plugins: plugins
				}
			}).done( function( response ) {
				window.location = href;
			});

		},

		showLoader: function() {
			$( this ).addClass( 'in-progress' );
		}
	};

	tmThemeWizard.init();

}( jQuery, window.tmThemeWizardSettings ) );