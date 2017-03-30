<?php
/**
 * Verification Step template
 */
?>
<h2><?php esc_html_e( 'Install Theme', 'tm-theme-wizard' ); ?></h2>
<div class="desc"><?php
	esc_html_e( 'Please, enter your Template ID and order ID to start installation:', 'tm-theme-wizard' );
?></div>
<div class="theme-wizard-form">
	<?php
		ttw_interface()->add_form_row( array(
			'label'       => esc_html__( 'Template ID:', 'tm-theme-wizard' ),
			'field'       => 'template_id',
			'placeholder' => esc_html__( 'Enter your template ID here...', 'tm-theme-wizard' ),
		) );
		ttw_interface()->add_form_row( array(
			'label'       => esc_html__( 'Order ID:', 'tm-theme-wizard' ),
			'field'       => 'order_id',
			'placeholder' => esc_html__( 'Enter your order ID here...', 'tm-theme-wizard' ),
		) );
		ttw_interface()->button( array(
			'action' => 'start-install',
			'text'   => esc_html__( 'Start Install', 'tm-theme-wizard' ),
		) );
	?>
</div>