<?php
/**
 * Verification Step template
 */
?>
<h2><?php esc_html_e( 'Install Theme', 'tm-theme-wizard' ); ?></h2>
<div class="desc"><?php
	esc_html_e( 'Please, enter your Template ID and order ID to start installation:', 'tm-theme-wizard' );
?></div>
<div class="theme-wiazrd-form">
	<?php
		ttw_interface()->add_form_row( array(
			'label'       => esc_html__( 'Template ID:', 'tm-theme-wizard' ),
			'field'       => 'tempalte-id',
			'placeholder' => esc_html__( 'Enter your template ID:', 'tm-theme-wizard' ),
		) );
		ttw_interface()->add_form_row( array(
			'label'       => esc_html__( 'Order ID:', 'tm-theme-wizard' ),
			'field'       => 'order-id',
			'placeholder' => esc_html__( 'Enter your order ID:', 'tm-theme-wizard' ),
		) );
	?>
	<button class="btn btn-primary" data-theme-wizard="start-install" data-loader="true" data-href="">
		<span class="text"><?php
			esc_html_e( 'Start Install', 'tm-theme-wizard' );
		?></span><span class="tm-wizard-loader"><span class="tm-wizard-loader__spinner"></span></span>
	</button>
</div>