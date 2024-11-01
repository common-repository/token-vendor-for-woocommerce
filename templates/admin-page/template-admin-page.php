<?php
// URL Base
$setting_url_base = add_query_arg( array( 'page' => 'ntvwc_admin_page' ), admin_url( 'admin.php' ) );

// Tabs
$tabs = apply_filters( 'ntvwc_filter_setting_page_tabs', array(
	'settings' => array( 
		'id'    => 'settings',
		'class' => 'nav-tab',
		'text'  => __( 'Settings', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN )
	),
	'tools' => array( 
		'id'    => 'tools',
		'class' => 'nav-tab',
		'text'  => __( 'Tools', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN )
	),
	'guide' => array( 
		'id'    => 'guide',
		'class' => 'nav-tab',
		'text'  => __( 'Guide', Nora_Token_Vendor_For_WooCommerce::TEXTDOMAIN )
	),
) );


$tab = ( isset( $_GET['tab'] ) ? $_GET['tab'] : 'settings' );
if ( isset( $tabs[ $tab ] ) ) {
	$tabs[ $tab ]['class']        .= ' nav-tab-active';
} else {
	$tabs['settings']['class'] .= ' nav-tab-active';
}


// Options
?>
<div class="wrap">
	<h1>Package Update Checker for WooCommerce</h1>
	<!--form id="<?php echo ''; ?>" method="post" action="options.php"-->
		<?php require( ntvwc_get_template_file_path( 'template-admin-menu-page-tabs', 'admin-page/header' ) ); ?>
		<div class="metabox-holder">
<?php



if ( 'settings' === $tab ) {
	require( ntvwc_get_template_file_path( 'settings', 'admin-page/parts' ) );
} elseif ( 'tools' === $tab ) {
	require( ntvwc_get_template_file_path( 'tools', 'admin-page/parts' ) );
} elseif ( 'guide' === $tab ) {
	require( ntvwc_get_template_file_path( 'guide', 'admin-page/parts' ) );
}

?>
		</div>
	<!--/form-->
</div>
