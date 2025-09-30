<?php
/**
 * Class Wow_Company
 *
 * The Wow_Company class represents a company in the WordPress admin menu that provides Wow Plugins.
 *
 * @package    WowPlugin
 * @subpackage General
 * @author     Dmytro Lobov <dev@wow-company.com>, Wow-Company
 * @copyright  2024 Dmytro Lobov
 * @license    GPL-2.0+
 */


defined( 'ABSPATH' ) || exit;

final class Wow_Company {
	public function __construct() {

		add_action( 'admin_menu', [ $this, 'add_menu' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_style' ] );

	}

	public function add_menu(): void {
		$icon       = self::icon();
		$page_title = __( 'WordPress plugins from Wow-Company', 'floating-button' );
		$menu_title = __( 'Wow Plugins', 'floating-button' );
		$capability = 'manage_options';
		$slug       = 'wow-company';

		add_menu_page( $page_title, $menu_title, $capability, $slug, [ $this, 'welcome_page' ], $icon );
		add_submenu_page( $slug, $page_title, '&#128075; Hey', $capability, $slug );
	}

	public function welcome_page(): void {
		require_once plugin_dir_path( __FILE__ ) . 'page-welcome.php';
		wp_enqueue_style( 'admin-wpie-page', plugin_dir_url( __FILE__ ) . 'assets/css/style.css', null, '1.0' );
	}

	public function admin_style(): void {
		wp_enqueue_style( 'wow-plugins', plugin_dir_url( __FILE__ ) . 'assets/css/admin.css', null, '1.0' );
	}


	private static function icon(): string {
		return 'data:image/svg+xml;base64, PHN2ZyB3aWR0aD0iNTEycHgiIGhlaWdodD0iNTEycHgiIHZpZXdCb3g9IjAgMCA1MTIgNTEyIiB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiPgogICAgPHRpdGxlPldvdy1Mb2dvIDIgQ29weTwvdGl0bGU+CiAgICA8ZGVmcz4KICAgICAgICA8bGluZWFyR3JhZGllbnQgeDE9IjYuNDQ1NTMzNjMlIiB5MT0iODQuMTM4MTg4MSUiIHgyPSIxMDAlIiB5Mj0iMTguMjM1MzQyMiUiIGlkPSJsaW5lYXJHcmFkaWVudC0xIj4KICAgICAgICAgICAgPHN0b3Agc3RvcC1jb2xvcj0iI0U4NkUyQyIgb2Zmc2V0PSIwJSI+PC9zdG9wPgogICAgICAgICAgICA8c3RvcCBzdG9wLWNvbG9yPSIjRTg2RTJDIiBvZmZzZXQ9IjEwMCUiPjwvc3RvcD4KICAgICAgICA8L2xpbmVhckdyYWRpZW50PgogICAgPC9kZWZzPgogICAgPGcgaWQ9Ildvdy1Mb2dvLTItQ29weSIgc3Ryb2tlPSJub25lIiBzdHJva2Utd2lkdGg9IjEiIGZpbGw9Im5vbmUiIGZpbGwtcnVsZT0iZXZlbm9kZCIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIj4KICAgICAgICA8cGF0aCBkPSJNMjU2LDQ4MSBDMzgwLjI2NDA2OSw0ODEgNDgxLDM4MC4yNjQwNjkgNDgxLDI1NiBDNDgxLDEzMS43MzU5MzEgMzgwLjI2NDA2OSwzMSAyNTYsMzEgQzEzMS43MzU5MzEsMzEgMzEsMTMxLjczNTkzMSAzMSwyNTYgQzMxLDM4MC4yNjQwNjkgMTMxLjczNTkzMSw0ODEgMjU2LDQ4MSBaIiBpZD0iT3ZhbCIgc3Ryb2tlPSIjRTg2RTJDIiBzdHJva2Utd2lkdGg9IjQyIiBmaWxsLW9wYWNpdHk9IjAiIGZpbGw9IiNGMEY2RkMiIHN0cm9rZS1kYXNoYXJyYXk9IjEyNzUuNzUsOTk5OTkiIHRyYW5zZm9ybT0idHJhbnNsYXRlKDI1Ni4wMDAwMDAsIDI1Ni4wMDAwMDApIHJvdGF0ZSgtMTM3LjAwMDAwMCkgdHJhbnNsYXRlKC0yNTYuMDAwMDAwLCAtMjU2LjAwMDAwMCkgIj48L3BhdGg+CiAgICAgICAgPGcgaWQ9Ikdyb3VwIiB0cmFuc2Zvcm09InRyYW5zbGF0ZSgxMDUuNDU1MDg2LCA4My44NDk0NTIpIiBzdHJva2U9InVybCgjbGluZWFyR3JhZGllbnQtMSkiIHN0cm9rZS13aWR0aD0iNDgiPgogICAgICAgICAgICA8cGF0aCBkPSJNMC40MzMwMTI3MDIsMTI4LjUxNDE1NyBMMTAwLjQzMzAxMywzMDEuNzE5MjM4IE0xMDAuNDMzMDEzLDEyOC41MTQxNTcgTDIwMC40MzMwMTMsMzAxLjcxOTIzOCBNMzc3LjU0NDkxNCwwLjE1MDU0NzUwMyBMMjAyLjI1NTcwNywzMDEuNzgwNDg5IiBpZD0iQ29tYmluZWQtU2hhcGUiPjwvcGF0aD4KICAgICAgICA8L2c+CiAgICA8L2c+Cjwvc3ZnPg==';

	}

}

new Wow_Company;