<?php
/**
 * Wow Company Class
 *
 * @package     WOW_Plugin
 * @subpackage  Includes/Wow_Company
 * @author      Dmytro Lobov <helper@wow-support.com>
 * @copyright   2019 Wow-Company
 * @license     GNU Publisher License
 * @version     1.0
 */

use FloatingButton\WOW_Plugin;

defined( 'ABSPATH' ) || exit;

/**
 * Creates the menu in admin panel general for all Wow plugin
 *
 * @property string text_domain - Text domain for translate
 *
 * @since 1.0
 */
final class Wow_Company {

	public function __construct() {
		add_action( 'admin_menu', [ $this, 'add_menu' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_style' ] );
		add_filter( 'admin_footer_text', [ $this, 'footer_text' ] );

	}

	public function admin_style( $hook ): void {
		wp_enqueue_style( 'wow-page', WOW_Plugin::url() . 'assets/css/admin-wow-company.css', null, '1.0' );
	}

	/**
	 * Register the plugin menu on sidebar menu in admin panel.
	 *
	 * @since 1.0
	 */
	public function add_menu(): void {
		$icon =
			'data:image/svg+xml;base64, PHN2ZyB3aWR0aD0iNTEycHgiIGhlaWdodD0iNTEycHgiIHZpZXdCb3g9IjAgMCA1MTIgNTEyIiB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiPgogICAgPHRpdGxlPldvdy1Mb2dvIDIgQ29weTwvdGl0bGU+CiAgICA8ZGVmcz4KICAgICAgICA8bGluZWFyR3JhZGllbnQgeDE9IjYuNDQ1NTMzNjMlIiB5MT0iODQuMTM4MTg4MSUiIHgyPSIxMDAlIiB5Mj0iMTguMjM1MzQyMiUiIGlkPSJsaW5lYXJHcmFkaWVudC0xIj4KICAgICAgICAgICAgPHN0b3Agc3RvcC1jb2xvcj0iI0U4NkUyQyIgb2Zmc2V0PSIwJSI+PC9zdG9wPgogICAgICAgICAgICA8c3RvcCBzdG9wLWNvbG9yPSIjRTg2RTJDIiBvZmZzZXQ9IjEwMCUiPjwvc3RvcD4KICAgICAgICA8L2xpbmVhckdyYWRpZW50PgogICAgPC9kZWZzPgogICAgPGcgaWQ9Ildvdy1Mb2dvLTItQ29weSIgc3Ryb2tlPSJub25lIiBzdHJva2Utd2lkdGg9IjEiIGZpbGw9Im5vbmUiIGZpbGwtcnVsZT0iZXZlbm9kZCIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIj4KICAgICAgICA8cGF0aCBkPSJNMjU2LDQ4MSBDMzgwLjI2NDA2OSw0ODEgNDgxLDM4MC4yNjQwNjkgNDgxLDI1NiBDNDgxLDEzMS43MzU5MzEgMzgwLjI2NDA2OSwzMSAyNTYsMzEgQzEzMS43MzU5MzEsMzEgMzEsMTMxLjczNTkzMSAzMSwyNTYgQzMxLDM4MC4yNjQwNjkgMTMxLjczNTkzMSw0ODEgMjU2LDQ4MSBaIiBpZD0iT3ZhbCIgc3Ryb2tlPSIjRTg2RTJDIiBzdHJva2Utd2lkdGg9IjQyIiBmaWxsLW9wYWNpdHk9IjAiIGZpbGw9IiNGMEY2RkMiIHN0cm9rZS1kYXNoYXJyYXk9IjEyNzUuNzUsOTk5OTkiIHRyYW5zZm9ybT0idHJhbnNsYXRlKDI1Ni4wMDAwMDAsIDI1Ni4wMDAwMDApIHJvdGF0ZSgtMTM3LjAwMDAwMCkgdHJhbnNsYXRlKC0yNTYuMDAwMDAwLCAtMjU2LjAwMDAwMCkgIj48L3BhdGg+CiAgICAgICAgPGcgaWQ9Ikdyb3VwIiB0cmFuc2Zvcm09InRyYW5zbGF0ZSgxMDUuNDU1MDg2LCA4My44NDk0NTIpIiBzdHJva2U9InVybCgjbGluZWFyR3JhZGllbnQtMSkiIHN0cm9rZS13aWR0aD0iNDgiPgogICAgICAgICAgICA8cGF0aCBkPSJNMC40MzMwMTI3MDIsMTI4LjUxNDE1NyBMMTAwLjQzMzAxMywzMDEuNzE5MjM4IE0xMDAuNDMzMDEzLDEyOC41MTQxNTcgTDIwMC40MzMwMTMsMzAxLjcxOTIzOCBNMzc3LjU0NDkxNCwwLjE1MDU0NzUwMyBMMjAyLjI1NTcwNywzMDEuNzgwNDg5IiBpZD0iQ29tYmluZWQtU2hhcGUiPjwvcGF0aD4KICAgICAgICA8L2c+CiAgICA8L2c+Cjwvc3ZnPg==';

		add_menu_page( 'Wow Plugins', 'Wow Plugins', 'manage_options', 'wow-company', array(
			$this,
			'main_page',
		), $icon );
		add_submenu_page( 'wow-company', 'Welcome to Wow-Company', '&#128075; Hey', 'manage_options', 'wow-company' );
	}

	/**
	 * Include the main file
	 */
	public function main_page(): void {
		require_once WOW_Plugin::dir() . 'includes/wow-company/main.php';
	}

	public function footer_text( $footer_text ) {
		global $pagenow;

		if ( $pagenow === 'admin.php' && ( isset( $_GET['page'] ) && $_GET['page'] === 'wow-company' ) ) {
			$text = __( 'Thank you for using <b>Our plugins</b>! Our website <a href="https://wow-estore.com/" target="_blank">Wow-Estore.com</a>',
				'floating-button' );

			return str_replace( '</span>', '', $footer_text ) . ' | ' . $text . '</span>';
		}

		return $footer_text;
	}

}

new Wow_Company;