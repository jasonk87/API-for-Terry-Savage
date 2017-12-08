<?php
/*
Plugin Name: Dev Mode
Description: Add a development mode, which will warn users you are currently working on their site.
Author: Jason Kinslow
Version: 0.0.1
Author URI: https://www.kinslowdesigns.com
*/

//this will include my plugin options folder
//@include 'options.php';

defined( 'ABSPATH' ) or die( 'This plugin requires WordPress' );
define( 'DEVMODE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

if( WP_DEBUG ){
	ini_set( 'error_log', DEVMODE_PLUGIN_DIR . '/debug.log' );
}

class Dev_Mode {

	function __construct(){
		// Add settings page to plugin
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
	}

	function add_admin_menu(){
		add_menu_page( 'Dev Mode Settings', 'Dev Mode', 'manage_options', 'dev-mode', array( $this, 'settings' ) );
	}

	function settings(){
		?>
			<section class="settings">
				<form action="" method="GET">
					<input type="hidden" name="page" value="dev-mode">
					<input type="hidden" name="action" value="start_process">
					<input type="submit" value="Start Process">
				</form>

				<?php 
					$home_posts = get_field( 'articles', get_post( get_option( 'page_on_front' ) )->ID );

					$new_posts = array();
					foreach ( $home_posts as $article ){
						if ( ! ( $article[ 'article_title' ] && $article[ 'summary' ] ) ) {
							continue;
						}

						if ( $article[ 'article_date' ] ){
							$date = DateTime::createFromFormat( 'M j, Y', $article[ 'article_date' ] );
						}

						if ( isset( $date ) ){
							$my_post = array(
								'post_status' => 'published',
								'post_title' => $article[ 'article_title' ],
								'post_content' => $article[ 'summary' ] . PHP_EOL . PHP_EOL . '<a href="' . $article[ 'article_link' ] . '" target="_blank">Read It Here</a>',
								'post_excerpt' => $article[ 'summary' ],
								'post_date' => $date->format( 'Y-m-d H:i:s' ),
								'post_category' => array( 'suntimes' ),
								'article_link' => $article[ 'article_link' ]
							);
						} else {
							$my_post = array(
								'post_type' => 'post',
								'post_status' => 'publish',
								'post_title' => $article[ 'article_title' ],
								'post_content' => $article[ 'summary' ] . PHP_EOL . PHP_EOL . '<a href="' . $article[ 'article_link' ] . '" target="_blank">Read It Here</a>',
								'post_excerpt' => $article[ 'summary' ],
								'article_link' => $article[ 'article_link' ]
							);
						}

						array_push( $new_posts, $my_post );
					}
				?>

				<?php if ( isset( $_GET[ 'action' ] ) && $_GET[ 'action' ] == 'start_process' ): ?>
					<?php  
						$count = 0;
						foreach ( $new_posts as $article ){
							$article_link = $article[ 'article_link' ];
							unset( $article[ 'article_link' ] );

							$article_id = wp_insert_post( $article );
							update_post_meta( $article_id, 'article_url', $article_link );

							wp_set_post_categories( $article_id, array( 22 ) );

							echo '#' . $count . ': ID = ' . $article_id . '<br>';
							echo 'Title: ' . $article[ 'post_title' ] . '<br>';
							echo '<a href="' . get_permalink( $article_id ) . '" target="_blank">Link is here</a><br><br>';

							$count ++;
						}
					?>
				<?php endif ?>
			</section> 
		<?php
	}
}

new Dev_Mode();

function print_pre( $var ){
	?><pre><?php print_r( $var ); ?></pre><?php
}