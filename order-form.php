<?php
/**
* Plugin Name: Order Form
* Plugin URI: https://dev-3j1k.pantheonsite.io/
* Description: Creates a simple order form you can insert to pages.
* Version: 1.0
* Author: 3J1K
*/

// Anslut CSS och JS till ditt plugin

function my_enqueue_scripts_and_styles()
{
    wp_enqueue_style('menu_order_styles', plugins_url('design.css', __FILE__));
    wp_enqueue_script('menu_order_script', plugins_url( 'script.js' , __FILE__ ));
}
add_action('wp_enqueue_scripts','my_enqueue_scripts_and_styles');  // för innehållet på sidan
add_action('admin_enqueue_scripts', 'my_enqueue_scripts_and_styles'); // för admin. 
// echo __FILE__;



// --------------WP admin meny-------------

function my_menu_menu() 
{
    add_menu_page(
        "Menu Dashboard", // Title för sidan
        "Menu Dashboard Admin", // Menyval som syns i panelen
        "manage_options",  // https://wordpress.org/support/article/roles-and-capabilities/
        "menu_order",     // Meny slug
        "edit_menu_order"); // Funktion som ska köras
}
add_action("admin_menu", "my_menu_menu");

//-----------------adminsida för ordrarna
function my_orders_menu() 
{
    add_menu_page(
        "Order Dashboard", // Title för sidan
        "Order Dashboard Admin", // Menyval som syns i panelen
        "manage_options",  // https://wordpress.org/support/article/roles-and-capabilities/
        "orders",     // Meny slug
        "edit_orders_order"); // Funktion som ska köras
}
add_action("admin_menu", "my_orders_menu");





//----------------MENY ADMIN-----------------------
function edit_menu_order()
{
		global $wpdb;
    $table_name = $wpdb->prefix . 'menu_orders';
    if( $wpdb->query( 'SELECT * FROM ' . $table_name ) === false ) // Kolla om tabellen finns
    {
        echo ("Creating table...");
				giorgios_admin_createdb();
        echo ("Table created");
    }

    echo '<h1 id="admin_header">Mitt admin</h1>';
    echo '<form action="" method="post" name="menu_order">';
    echo '<div><label for="dish_name">Rättens namn: </label>';
    echo '<input type="text" id="dish_name" name="dish_name" required /></div>';
    echo '<div><label for="dish_price">Rättens pris: </label>';
    echo '<input type="text" id="dish_price" name="dish_price" required /></div>';
    echo '<input type="submit" name="menu_submitted" value="Spara">';
    echo '</form>';

    if (isset($_POST['menu_submitted'])) // Kolla om formuläret är submittat
    {
        $dish_name = sanitize_text_field( $_POST["dish_name"] );
        $dish_price = sanitize_text_field( $_POST["dish_price"] );
        
        $wpdb->insert($table_name, array(
            'time'=> date("Y-m-d H:i:s"),
            'dish_name'=>$dish_name,
            'dish_price'=>$dish_price
            ));
    }

    // Hämta alla rader i tabellen
    $retrieve_entries = $wpdb->get_results( "SELECT * FROM $table_name" );

    if ($retrieve_entries)
    {
        echo "<table><tr><th>ID</th><th>Namn</th><th>Pris</th></tr>";
                
        foreach ($retrieve_entries as $entry) 
        {
            echo "<tr>";
            echo "<td>$entry->id</td>";
            echo "<td>$entry->dish_name</td>";
						echo "<td>$entry->dish_price</td>";
						echo "<td><form action='admin-post.php' method='post'>";
            wp_nonce_field( 'delete_row_event_' . $entry->id );
            echo "<input type='hidden' name='action' value='delete_row_event'>";
            echo "<input type='hidden' name='eventid' value='$entry->id'>";
            echo "<input type='submit' class='delete' value='Delete' /></td>
            </form>";
						echo "</tr>";
        }               
        echo "</table>";
    } 
    else 
    {
        echo "<p>Listan är tom!</p>";
    }			
}



// Där du skriver [menu_order] på sajten visas det som funktionen 'view_menu_orders' skriver ut
add_shortcode('menu_order', 'view_menu_order');


//----------ORDER ADMIN---------------------se och ta bort beställningarna
function edit_orders_order()
{
		global $wpdb;
		$table_name_favorites = $wpdb->prefix . 'customer_orders2';
		if( $wpdb->query( 'SELECT * FROM ' . $table_name_favorites ) === false ) // Kolla om tabellen finns
    {
        echo ("Creating table...");
				giorgios_admin_createDb();
        echo ("Table created");
    }
    // Hämta alla rader i tabellen
			echo '<h1 id="admin_header">Ordrar:</h1>';
			$retrieve_orders = $wpdb->get_results( "SELECT * FROM $table_name_favorites" );
			if($retrieve_orders){
				echo "<table><tr><th>ID</th><th>Namn</th></tr>";
				foreach ($retrieve_orders as $entry) 
        {
            echo "<tr>";
            echo "<td>$entry->id2</td>";
            echo "<td>$entry->dish_name</td>";
						echo "<td><form action='admin-post.php' method='post'>";
            wp_nonce_field( 'delete_row_event_' . $entry->id2 );
            echo "<input type='hidden' name='action' value='delete_row_event'>";
            echo "<input type='hidden' name='eventid2' value='$entry->id2'>";
            echo "<input type='submit' class='delete' value='Delete' /></td>
            </form>";
						echo "</tr>";
        }
                
        echo "</table>";
			}
			else 
			{
					echo "<p>Listan är tom!</p>";
			}
}

//MENY SOM VISAS PÅ SIDAN
function view_menu_order(){
	global $wpdb;
	$table_favorites = $wpdb->prefix . 'customer_orders2';
	// Kod för att fånga upp val av rätt
	if(isset($_GET['customer_orders2']))
    {
				// echo "Du har valt: " . $_GET['customer_orders2'];
				if( $wpdb->query( 'SELECT * FROM ' . $table_favorites ) === false ) // Kolla om tabellen finns
				{
					echo ("Creating table...");
					customers_orders_createdb();
					echo ("Table created");
			}
			//echo "Sparar i db";
        $wpdb->insert($table_favorites, array(
                'time'=> date("Y-m-d H:i:s"),
								'id2'=>(int)$_GET['id2'],
								'dish_name'=>$_GET['customer_orders2']
                ));
}

		$table_name = $wpdb->prefix . 'menu_orders';
		echo '<h2>Vår Menu</h2>';
		$retrieve_entries = $wpdb->get_results( "SELECT * FROM $table_name" );

		if ($retrieve_entries)
		{
				echo "<table><tr><th>Namn</th><th>Pris</th><th>Beställ</th></tr>";
								
				foreach ($retrieve_entries as $entry) 
				{
					echo "<tr>";
					echo "<td>$entry->dish_name</td>";
					echo "<td>$entry->dish_price</td>";
					echo "<td><a href='?customer_orders2=$entry->dish_name&id2=$entry->id2' >Lägg till</a></td>";
					echo "</tr>";
					
				}
								
				echo "</table>";

				$table_name_favorites = $wpdb->prefix . 'customer_orders2';
				echo "<h4>Du har valt: "  . $_GET['customer_orders2'] . "</h4>";
				// echo '<input type="button" value="Skicka beställning"/>';
				$retrieve_entries = $wpdb->get_results( "SELECT * FROM $table_name_favorites" );

				
		}
}


//---DATABASEN för menyn på sidan
function giorgios_admin_createdb()
{
    global $wpdb;
		global $menuform_db_version;
		
    $table_name = $wpdb->prefix . 'menu_orders';
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
        dish_name text NOT NULL,
        dish_price text NOT NULL,
        PRIMARY KEY  (id)
        ) $charset_collate;";
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' ); // Så dbDelta fungerar
        dbDelta( $sql );
				
				add_option( 'menuform_db_version', $menuform_db_version );
}
register_activation_hook( __FILE__, 'edit_menu_order' );


// Databas för beställning
function customers_orders_createdb()
{
    global $wpdb;
		global $menuform_db_version;
		
    $table_name_favorites = $wpdb->prefix . 'customer_orders2';
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql = "CREATE TABLE $table_name_favorites (
        id2 mediumint(9) NOT NULL AUTO_INCREMENT,
        time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
				dish_name text NOT NULL,
        PRIMARY KEY  (id2)
        ) $charset_collate;";
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' ); // Så dbDelta fungerar
        dbDelta( $sql );
				
				add_option( 'menuform_db_version', $menuform_db_version );
}
register_activation_hook( __FILE__, 'edit_orders_order' );


//-----------------------TA BORT funktion-------------------------------------------------
add_action( 'admin_post_delete_row_event', function () {


		if (!empty($_POST['eventid2'])) {
			$event_id = $_POST['eventid2'];
			check_admin_referer( 'delete_row_event_' . $event_id );
			global $wpdb;
			$table_name_favorites = $wpdb->prefix . 'customer_orders2';
			$wpdb->delete($table_name_favorites,
										[ 'id2' => $event_id ],
										[ '%d' ] );
										}

										wp_redirect(admin_url('/admin.php?page=orders'));

		if (!empty($_POST['eventid'])) {
			$event_id = $_POST['eventid'];
			check_admin_referer( 'delete_row_event_' . $event_id );
			global $wpdb;
			$table_name = $wpdb->prefix . 'menu_orders';
			$wpdb->delete($table_name,
										[ 'id' => $event_id ],
										[ '%d' ] );
										}

										wp_redirect(admin_url('/admin.php?page=orders'));
										// wp_redirect(admin_url('/index.php'));
										exit;
});




