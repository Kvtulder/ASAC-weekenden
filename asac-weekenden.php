<?php
/*
Plugin Name: ASAC Weekenden
Plugin URI:  https://asac.alpenclub.nl
Description: ASAC Weekenden plugin: Schrijf je in voor weekenden, beheer ze & kijk wie er mee gaat!
Version:     1.0
Author:      ASAC SiteCo
*/

// Genereer de database voor de weekenden bij activatie plugin
register_activation_hook( __FILE__, 'create_weekenden_pages');
add_action( 'init', 'register_weekenden' );

function register_weekenden() {
 
    $labels = array(
        'name' => _x( 'ASAC Weekenden', 'asac_weekenden' ),
        'singular_name' => _x( 'Alle weekenden', 'weekenden' ),
        'add_new' => _x( 'Nieuw weekend', 'weekenden' ),
        'add_new_item' => _x( 'Voeg een nieuw weekend toe', 'weekenden' ),
        'edit_item' => _x( 'Pas weekend aan', 'weekenden' ),
        'new_item' => _x( 'Nieuw weekend', 'weekenden' ),
        'view_item' => _x( 'Bekijk weekend', 'weekenden' ),
        'search_items' => _x( 'Zoek', 'weekenden' ),
        'not_found' => _x( 'Er zijn op dit moment geen weekenden :(', 'weekenden' ),
        'not_found_in_trash' => _x( 'Geen weekenden in de prullebak', 'weekenden' ),
        'menu_name' => _x( 'ASAC Weekenden', 'weekenden' ),
    );
 
    $args = array(
        'labels' => $labels,
        'hierarchical' => true,
        'description' => 'Sorteer weekenden op basis van de locatie',
        'supports' => array( 'title', 'editor', 'author', 'thumbnail', 'trackbacks', 'custom-fields', 'comments', 'revisions', 'page-attributes' ),
        'taxonomies' => array( 'locaties' ),
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_position' => 5,
        'menu_icon' => 'dashicons-palmtree',
        'show_in_nav_menus' => true,
        'publicly_queryable' => true,
        'exclude_from_search' => false,
        'has_archive' => true,
        'query_var' => true,
        'can_export' => true,
        'rewrite' => true,
        'capability_type' => 'post'
    );
 
    register_post_type( 'weekenden', $args );
}
 
function genres_taxonomy() {
    register_taxonomy(
        'locaties',
        'weekenden',
        array(
            'hierarchical' => true,
            'label' => 'Locatie',
            'query_var' => true,
            'rewrite' => array(
                'slug' => 'locatie',
                'with_front' => false
            )
        )
    );
}
add_action( 'init', 'genres_taxonomy');

// Function used to automatically create Music Reviews page.
function create_weekenden_pages()
  {
   //post status and options
    $post = array(
          'comment_status' => 'open',
          'ping_status' =>  'closed' ,
          'post_date' => date('Y-m-d H:i:s'),
          'post_name' => 'weekenden',
          'post_status' => 'publish' ,
          'post_title' => 'ASAC weekenden',
          'post_type' => 'page',
    );
    //insert page and save the id
    $newvalue = wp_insert_post( $post, false );
    //save the id in the database
    update_option( 'weekendpage', $newvalue );
  }

add_action('admin_menu', 'my_plugin_menu');

function my_plugin_menu() {
	add_dashboard_page('My Plugin Dashboard', 'My Plugin', 'read', 'my-unique-identifier', 'my_plugin_function');
}

function my_plugin_options() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	echo '<div class="wrap">';
	echo '<p>Here is where the form would go if I actually had options.</p>';
	echo '</div>';
}

function acas_weekend_display_registrations($args)
{
	if(!is_user_logged_in())
	{
		return "Log in om te kunnen zien wie mee gaat op dit weekend";
	}
	if(!isset($args['weekendid']))
	{
		return;
	}
	
	global $wpdb;
	
	$message = array();
	$table_name = $wpdb->prefix . 'cf_form_entries';
	$sqlquery = $wpdb->prepare( "SELECT * FROM $table_name WHERE form_id = '%s' AND status = 'active'", $args['weekendid']);
	$query = $wpdb->get_results($sqlquery);

	$entryids = "0";
	foreach($query as $a){
		$entryids .= ',' . $a->id;
	}
	
	$table_name = $wpdb->prefix . 'cf_form_entry_values';
	$query= $wpdb->get_results("SELECT * FROM $table_name WHERE entry_id IN ($entryids) AND slug = 'voornaam'");
	foreach($query as $a){
		$message[] = $a->value;
	}
	return 'Huidige inschrijvingen: ' . implode($message, ', ');
}
add_shortcode('WeekendInschrijvingen' , 'acas_weekend_display_registrations' );

?>
