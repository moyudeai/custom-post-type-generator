<?php
/*
Plugin Name: Custom Post Type Generator
Plugin URI: http://hijiriworld.com/web/plugins/custom-post-type-generator/
Description: Generate Custom Post Types and Custom Taxonomies, from the admin interface which is easy to understand. it's a must have for any user working with WordPress.
Author: hijiri
Author URI: http://hijiriworld.com/web/
Version: 2.0.0
*/

/*  Copyright 2013 hijiri

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/***************************************************************

	Define

***************************************************************/

define( 'CPTG_URL', plugin_dir_path(__FILE__) );

load_plugin_textdomain( 'cptg', false, basename(dirname(__FILE__)).'/lang' );

/***************************************************************

	CPTG Class

***************************************************************/

$cptg = new Cptg;

class Cptg
{
	function __construct()
	{
		// add_menu
		add_action( 'admin_menu', array($this, 'add_menus' ));
		
		// generate
		add_action( 'init', array($this,'generate_ctps'), 0 );
		add_action( 'init', array($this,'generate_taxs'), 0 );
		
		// actions
		add_action( 'admin_init', array($this,'cptg_actions'));
		
		// load JavaScript and CSS
		if ( strpos( $_SERVER['REQUEST_URI'], '_cpt' ) > 0 || strpos( $_SERVER['REQUEST_URI'], '_tax' ) > 0 ) {
			add_action( 'admin_head', array($this, 'cptg_js') );
			add_action( 'admin_head', array($this, 'cptg_css') );
		}
	}
	
	function add_menus()
	{
		$menu_top = add_utility_page(__('Custom Post Type', 'cptg'), __('Custom Post Type', 'cptg'),  'administrator', 'manage_cpt', array($this,'manage_cpt'));
		add_submenu_page( $menu_top, __('Add New', 'cptg'), __('Add New', 'cptg'), 'administrator', 'regist_cpt', array($this,'regist_cpt'));
		add_submenu_page( 'manage_cpt', __('Custom Taxonomy', 'cptg'), __('Custom Taxonomy', 'cptg'), 'administrator', 'manage_tax', array($this,'manage_tax'));
		add_submenu_page( $menu_top, __('Add New', 'cptg'), __('Add New', 'cptg'), 'administrator', 'regist_tax', array($this,'regist_tax'));
	}
	
	function manage_cpt()
	{
		require CPTG_URL.'include/manage_cpt.php';
	}
	function manage_tax()
	{
		require CPTG_URL.'include/manage_tax.php';
	}
	function regist_cpt()
	{
		require CPTG_URL.'include/regist_cpt.php';
	}
	function regist_tax()
	{
		require CPTG_URL.'include/regist_tax.php';
	}
	
	function cptg_js()
	{
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'cptg', plugins_url('/js/cptg.js', __FILE__) );
	}
	function cptg_css()
	{
		wp_enqueue_style( 'cptg', plugins_url('/css/cptg.css', __FILE__), array(), null );
    }
	
	/************************************************************************************************
				
		Genarate Custom Post Type
				
	************************************************************************************************/
	
	function generate_ctps()
	{
		
		// get_option custom post types
		
		global $wpdb;

		$sql = "SELECT
				option_id, 
				option_name,
				option_value
			FROM 
				$wpdb->options 
			WHERE 
				option_name LIKE '%%cptg_cpt%%'
			ORDER BY 
				option_id ASC
			";
		
		$results = $wpdb->get_results($sql);
		
		if ( is_array( $results ) ) {
			foreach ( $results as $result ) {
				
				$cpt = unserialize( $result->option_value );
				
				/* --- Label Options Initialize --- */
				
				$cpt_label = $cpt["label"] ? esc_html($cpt["label"]) : esc_html($cpt["post_type"]);

					$cpt_labels['singular_name'] = $cpt["labels"]["singular_label"] ? $cpt["labels"]["singular_label"] :				$cpt_label;
					$cpt_labels['menu_name']			= $cpt["labels"]["menu_name"] ? $cpt["labels"]["menu_name"] :					$cpt_label;
					$cpt_labels['all_items']			= $cpt["labels"]["all_items"] ? $cpt["labels"]["all_items"] :					__('All Posts', 'cptg');
					$cpt_labels['add_new']				= $cpt["labels"]["add_new"] ? $cpt["labels"]["add_new"] :						__('Add New', 'cptg');
					$cpt_labels['add_new_item']			= $cpt["labels"]["add_new_item"] ? $cpt["labels"]["add_new_item"] :				__('Add New Post', 'cptg');
					$cpt_labels['edit_item']			= $cpt["labels"]["edit_item"] ? $cpt["labels"]["edit_item"] :					__('Edit Post', 'cptg');
					$cpt_labels['new_item']				= $cpt["labels"]["new_item"] ? $cpt["labels"]["new_item"] :						__('New Post', 'cptg');
					$cpt_labels['view_item']			= $cpt["labels"]["view_item"] ? $cpt["labels"]["view_item"] :					__('View Post', 'cptg');
					$cpt_labels['search_items']			= $cpt["labels"]["search_items"] ? $cpt["labels"]["search_items"] :				__('Search Posts', 'cptg');
					$cpt_labels['not_found']			= $cpt["labels"]["not_found"] ? $cpt["labels"]["not_found"] :					__('No posts found.', 'cptg');
					$cpt_labels['not_found_in_trash']	= $cpt["labels"]["not_found_in_trash"] ? $cpt["labels"]["not_found_in_trash"] :	__('No posts found in Trash.', 'cptg');
					$cpt_labels['parent_item_colon']	= $cpt["labels"]["parent_item_colon"] ? $cpt["labels"]["parent_item_colon"] :	__('Parent Page', 'cptg');
			
				/* --- Advanced Options Initialize --- */
				
				$cpt_rewrite_slug = $cpt["rewrite_slug"] ? esc_html($cpt["rewrite_slug"]) : esc_html($cpt["post_type"]);
				$cpt_menu_position = $cpt["menu_position"] ? intval($cpt["menu_position"]) : null;
				$cpt_supports = $cpt["supports"] ? $cpt["supports"] : array(null);
				
				if ( isset ( $cpt["show_in_menu"] ) ) {
					$cpt_show_in_menu = ( $cpt["show_in_menu"] == 1 ) ? true : false;
					$cpt_show_in_menu = ( $cpt["show_in_menu_string"] ) ? $cpt["show_in_menu_string"] : $cpt_show_in_menu;
				} else {
					$cpt_show_in_menu = true;
				}
				
				$cpt_menu_icon = $cpt["menu_icon"] ? $cpt["menu_icon"] : null;
				
				/* --- register_post_type() --- */
				
				register_post_type( $cpt["post_type"],
					array (
						'label'					=> $cpt_label,
						'labels'				=> $cpt_labels,
						'description'			=> esc_html($cpt["description"]),
						'public'				=> get_disp_cptg_boolean($cpt["public"]),
						'publicly_queryable'	=> get_disp_cptg_boolean($cpt["publicly_queryable"]),
						'exclude_from_search'	=> get_disp_cptg_boolean($cpt["exclude_from_search"]),
						'show_ui'				=> get_disp_cptg_boolean($cpt["show_ui"]),
						'show_in_nav_menus'		=> get_disp_cptg_boolean( $cpt["show_in_nav_menus"] ),
						'show_in_menu'			=> $cpt_show_in_menu,
						'capability_type'		=> $cpt["capability_type"],
						'has_archive'			=> get_disp_cptg_boolean($cpt["has_archive"]),
						'hierarchical'			=> get_disp_cptg_boolean($cpt["hierarchical"]),
						'rewrite'				=> array('slug' => $cpt_rewrite_slug),
						'query_var'				=> get_disp_cptg_boolean($cpt["query_var"]),
						'can_export'			=> get_disp_cptg_boolean($cpt["can_export"]),
						'menu_position'			=> $cpt_menu_position,
						'menu_icon'				=> $cpt_menu_icon,
						'supports'				=> $cpt_supports,
					)
				);
			}
		}
	}
	
	/************************************************************************************************
				
		Genarate Custom Taxonomy
				
	************************************************************************************************/
	
	function generate_taxs() {

		global $wpdb;

		$sql = "SELECT
				option_id, 
				option_name,
				option_value
			FROM 
				$wpdb->options 
			WHERE 
				option_name LIKE '%%cptg_tax%%'
			ORDER BY 
				option_id ASC
			";
		
		$results = $wpdb->get_results($sql);
		
		if ( is_array( $results ) ) {
			foreach ( $results as $result ) {
				
				$tax = unserialize( $result->option_value );
				
				/* --- Label Options Initialize --- */
				
				$tax_label = $tax["label"] ? esc_html($tax["label"]) : esc_html($tax["taxonomy"]);
					
					$tax_labels['singular_name']				= $tax["labels"]["singular_label"] ? $tax["labels"]["singular_label"] :							$tax_label;
					$tax_labels['search_items']					= $tax["labels"]["search_items"] ? $tax["labels"]["search_items"] :								__('Search Tags', 'cptg');
					$tax_labels['popular_items']				= $tax["labels"]["popular_items"] ? $tax["labels"]["popular_items"] :							__('Popular Tags', 'cptg');
					$tax_labels['all_items']					= $tax["labels"]["all_items"] ? $tax["labels"]["all_items"] :									__('All Tags', 'cptg');
					$tax_labels['parent_item']					= $tax["labels"]["parent_item"] ? $tax["labels"]["parent_item"] :								__('Parent Category', 'cptg');
					$tax_labels['parent_item_colon'] 			= $tax["labels"]["parent_item_colon"] ? $tax["labels"]["parent_item_colon"] :					__('Parent Category', 'cptg');
					$tax_labels['edit_item']					= $tax["labels"]["edit_item"] ? $tax["labels"]["edit_item"] :									__('Edit Tag', 'cptg');
					$tax_labels['update_item']					= $tax["labels"]["update_item"] ? $tax["labels"]["update_item"] :								__('Update Tag', 'cptg');
					$tax_labels['add_new_item']					= $tax["labels"]["add_new_item"] ? $tax["labels"]["add_new_item"] :								__('Add New Tag', 'cptg');
					$tax_labels['new_item_name']				= $tax["labels"]["new_item_name"] ? $tax["labels"]["new_item_name"] :							__('New Tag Name', 'cptg');
					$tax_labels['separate_items_with_commas']	= $tax["labels"]["separate_items_with_commas"] ? $tax["labels"]["separate_items_with_commas"] :	__('Separate tags with commas', 'cptg');
					$tax_labels['add_or_remove_items']			= $tax["labels"]["add_or_remove_items"] ? $tax["labels"]["add_or_remove_items"] :				__('Add or remove tags', 'cptg');
					$tax_labels['choose_from_most_used']		= $tax["labels"]["choose_from_most_used"] ? $tax["labels"]["choose_from_most_used"] :			__('Choose from the most used tags', 'cptg');

				/* --- Advanced Options Initialize --- */
				
				$tax_rewrite_slug = $tax["rewrite_slug"] ? esc_html($tax["rewrite_slug"]) : esc_html($tax["taxonomy"]);
				$tax_post_types = $tax["post_types"];
				
				/* --- register_taxonomy() --- */
				
				register_taxonomy( $tax["taxonomy"], $tax_post_types,
					array (
						'label'				=> $tax_label,
						'labels'			=> $tax_labels,
						'show_ui'			=> get_disp_cptg_boolean($tax["show_ui"]),
						'hierarchical'		=> get_disp_cptg_boolean($tax["hierarchical"]),
						'rewrite'			=> array('slug' => $tax_rewrite_slug),
						'query_var'			=> get_disp_cptg_boolean($tax["query_var"]),
					)
				);
			}
		}
	}
	
	
	
	/************************************************************************************************
	
		Actions
	
	************************************************************************************************/
	
	
	function cptg_actions()
	{
		
		/* --- Delete cpt --- */
		
		if ( isset( $_GET['action'] ) && $_GET['action'] == 'del_cpt' ) {
			check_admin_referer( 'nonce_del_cpt' );
			$key = $_GET['key'];
			delete_option( $key );
			wp_redirect( 'admin.php?page=manage_cpt&msg=del' );
		}
		
		/* --- Delete tax --- */
		
		if ( isset( $_GET['action'] ) && $_GET['action'] == 'del_tax' ) {
			check_admin_referer( 'nonce_del_tax' );
			$key = $_GET['key'];
			delete_option( $key );
			wp_redirect( 'admin.php?page=manage_tax&msg=del' );
		}
		
		
		if ( isset( $_POST['cpt_submit'] ) ) {
			
			// Edit cpt
			if ( isset( $_POST['key'] ) ) {
		
				check_admin_referer( 'nonce_regist_cpt' );
		
				$key = $_POST['key'];
				
				// get input values
				$input_data = $_POST['input_cpt'];
				
				// labels to array
				$input_data += array( 'labels' => $_POST['cpt_labels'] );
				// supports to array
				$cpt_supports = ( isset( $_POST['cpt_supports'] ) ) ? $_POST['cpt_supports'] : array();
				
				$input_data += array( 'supports' => $cpt_supports );
				
				update_option( $key, $input_data );
				
				wp_redirect( 'admin.php?page=manage_cpt&msg=edit' );
				
			// Add cpt
			} else {
			
				check_admin_referer( 'nonce_regist_cpt' );
		
				// get input values
				$input_data = $_POST['input_cpt'];
				
				// labels to array
				$input_data += array( 'labels' => $_POST['cpt_labels'] );
				// supports to array
				$cpt_supports = ( isset( $_POST['cpt_supports'] ) ) ? $_POST['cpt_supports'] : array();
				$input_data += array( 'supports' => $cpt_supports );
					
				update_option( uniqid('cptg_cpt_'), $input_data );
				
				wp_redirect( 'admin.php?page=manage_cpt&msg=add' );
			}
		}
		
		if ( isset( $_POST['tax_submit'] ) ) {
			
			// Eidt tax
			if ( isset( $_POST['key'] ) ) {
			
				check_admin_referer( 'nonce_regist_tax' );
		
				//custom taxonomy to edit
				$key = $_POST['key'];
		
				// get input values
				$input_data = $_POST['input_tax'];
				
				// labels to array
				$input_data += array( 'labels' => $_POST['tax_labels'] );
				// attached post type to array
				$input_data += array( 'post_types' => $_POST['tax_post_types'] );
				
				update_option( $key, $input_data );
				
				wp_redirect( 'admin.php?page=manage_tax&msg=edit' );
			
			// Add tax
			} else {
				
				check_admin_referer( 'nonce_regist_tax' );
		
				// get input values
				$input_data = $_POST['input_tax'];
				
				// labels to array
				$input_data += array( 'labels' => $_POST['tax_labels'] );
				// attached post type to array
				$input_data += array( 'post_types' => $_POST['tax_post_types'] );
				
				update_option( uniqid('cptg_tax_'), $input_data );
				
				wp_redirect( 'admin.php?page=manage_tax&msg=add' );
			}
		}
	}
}


/************************************************************************************************
				
	Method
				
************************************************************************************************/
	
function get_disp_cptg_boolean( $booText )
{
	return $booText ? true : false;
}

function disp_cptg_boolean( $booText )
{
	return $booText ? 'true' : 'false';
}

function echo_boolean_options($obj, $default)
{
	if (isset($obj)) {
		if ($obj == 0) {
			echo '<option value="0" selected="selected">false</option>';
			echo '<option value="1">true</option>';
		} else if($obj == 1) {
			echo '<option value="0">false</option>';
			echo '<option value="1" selected="selected">true</option>';
		}
	} else {
		if ($default == 0) {
			echo '<option value="0" selected="selected">false</option>';
			echo '<option value="1">true</option>';
		} else if($default == 1) {
			echo '<option value="0">false</option>';
			echo '<option value="1" selected="selected">true</option>';
		}
	}
}


/************************************************************************************************
				
	Note: WP Options Structure
				
************************************************************************************************/

/*

Array
(
    [post_type] =>
    [label] =>
    [menu_position] => 
    [has_archive] =>
    [description] =>
    [public] =>
    [publicly_queryable] =>
    [exclude_from_search] =>
    [show_ui] =>
    [show_in_nav_menus] =>
    [show_in_menu] =>
    [show_in_menu_string] => 
    [menu_icon] => 
    [capability_type] =>
    [hierarchical] =>
    [rewrite] =>
    [rewrite_slug] =>
    [query_var] =>
    [can_export] =>
    [labels] => Array
        (
            [singular_label]=> 
            [menu_name]=> 
            [all_items]=> 
            [add_new]=> 
            [add_new_item]=> 
            [edit_item]=> 
            [new_item]=> 
            [view_item]=> 
            [search_items]=> 
            [not_found]=> 
            [not_found_in_trash]=> 
            [parent_item_colon]=> 
        )

    [supports] => Array
        (
            [0] => title
            ...
        )

)
	
		
Array
(
    [taxonomy] =>
    [label] => 
    [public] =>
    [show_ui] =>
    [hierarchical] =>
    [rewrite] =>
    [rewrite_slug] => 
    [query_var] =>
    [labels] => Array
        (
            [singular_label] =>
            [search_items] => 
            [popular_items] => 
            [all_items] => 
            [parent_item] => 
            [parent_item_colon] => 
            [edit_item] => 
            [update_item] => 
            [add_new_item] => 
            [new_item_name] => 
            [separate_items_with_commas] => 
            [add_or_remove_items] => 
            [choose_from_most_used] => 
        )

    [post_types] => Array
        (
            [0] => post
            ...
        )

)

*/
	
?>