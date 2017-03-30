<?php
/**
 * Install child theme template
 */
$theme_data = get_option( ttw()->settings['options']['parent_data'] );

if ( ! $theme_data ) {
	echo '<div class="theme-wizard-error">' . esc_html__( 'We can\'t found inforamtion about installed theme. Plaese, return to previous step and enter your verification data again.', 'tm-theme-wizard' ) . '</div>';
	return;
}

?>
<h2><?php esc_html_e( 'Use child theme?', 'tm-theme-wizard' ); ?></h2>
<div class="desc"><?php
	printf( esc_html__( 'We recommend you to use our child themes generator to get child theme for %s', 'tm-theme-wizard' ), $theme_data['ThemeName'] );
?></div>
<div class="theme-wizard-form">
	<?php
		ttw_interface()->add_form_radio( array(
			'label'   => esc_html__( 'Continue with parent theme', 'tm-theme-wizard' ),
			'field'   => 'use_child',
			'value'   => 'not_use_child',
			'checked' => true,
		) );
		ttw_interface()->add_form_radio( array(
			'label'   => esc_html__( 'Use child theme', 'tm-theme-wizard' ),
			'field'   => 'use_child',
			'value'   => 'use_child',
		) );
		ttw_interface()->button( array(
			'action' => 'get-child',
			'text'   => esc_html__( 'Continue', 'tm-theme-wizard' ),
		) );
	?>
</div>