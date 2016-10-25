<?php
/*
Plugin Name: Widget Shortcodes
Plugin URI: https://github.com/jonmcpartland/Widget-Shortcodes/
Description: Allows use of widgets within post content.
Author: Jon McPartland
Version: 0.1.0
Author URI: https://jon.mcpart.land
Textdomain: widgetshortcodes
*/

new class {

	protected $sidebarName = 'Shortcode-able Widgets';
	protected $sidebarID   = 'widget_shortcodes';
	protected $description = 'Sidebar to hold widgets and their settings';

	public function __construct() {
		\add_action( 'in_widget_form', [ $this, 'widget_form'  ] );
		\add_action( 'widgets_init',   [ $this, 'init_sidebar' ] );

		\add_shortcode( 'widget',      [ $this, 'shortcode_widget'  ] );
		\add_shortcode( 'widget_area', [ $this, 'shortcode_sidebar' ] );
	}

	public function widget_form( $instance ) {
		if ( '__i__' === $instance->number ) {
			return;
		}

		echo sprintf( '<p>Usage:<br><code>[widget id="%s"]</code></p>', $instance->id );
	}

	public function init_sidebar() {
		\register_sidebar( [
			'id'   => $this->sidebarID,
			'name' => $this->sidebarName,
			'description'   => $this->description,
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		] );
	}

	public function shortcode_widget( $params ) {
		if ( \is_admin() || ! isset( $GLOBALS['_wp_sidebars_widgets'] ) ) {
			return '';
		}

		$defaults = \apply_filters( 'widgetshortcodes_params', [] );
		$defaults = array_merge( [ 'id' => '', ], $defaults );
		$params   = \shortcode_atts( $defaults, $params, 'widget' );

		if ( ! isset( $GLOBALS['wp_registered_widgets'][ $params['id'] ] ) ) {
			return '';
		}

		$theWidget = $GLOBALS['wp_registered_widgets'][ $params['id'] ];

		$beforeWidget = sprintf( '<div id="%1$s" class="widget">', "widget-{$params['id']}" );

		$callbackArgs = [ [
			'id'            => $this->sidebarID,
			'name'          => $this->sidebarName,
			'description'   => $this->description,
			'class'         => $theWidget['classname'],
			'before_widget' => $beforeWidget,
			'after_widget'  => '</div>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
			'widget_id'     => $theWidget['id'],
			'widget_name'   => $theWidget['name'],
		], $params, 3 ];

		ob_start();
		call_user_func_array( $theWidget['callback'], $callbackArgs );
		return ob_get_clean();
	}

	public function shortcode_sidebar() {
		ob_start();
		\dynamic_sidebar( $this->sidebarID );
		return sprintf( '<div class="sidebar">%s</div>', ob_get_clean() );
	}

};
