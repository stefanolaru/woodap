<?php
/*
Plugin Name: Woocommerce - Delete All Products
Description: Delete all products from Woocommerce
Author: Stefan Olaru
Author URI: http://stefanolaru.com
Version: 1.0
*/

// prevent direct access
if(!defined('ABSPATH')) {
	exit;
}

class WooDAP {

	public static $version = '1.0';

	function __construct() {

		// install/uninstall hooks
		register_activation_hook( __FILE__, ['WooDAP', 'install']);
		register_deactivation_hook( __FILE__, ['WooDAP', 'deactivate']);
		register_uninstall_hook( __FILE__, ['WooDAP', 'uninstall']);
		
		// add menu item
		add_action('admin_menu', [$this, 'add_delete_all_menu_item']);

		// start delete
		add_action('admin_post_woodap', [$this, 'delete']);

	}

	private function _debug($s, $die = false) {
		echo '<pre>'.print_r($s, true).'</pre>';
		if($die) die();
	}

	public function add_delete_all_menu_item() {
		add_submenu_page( 'edit.php?post_type=product', __('Delete All Products'), __('Delete All Products'), 'edit_products', 'delete_all_products', [$this, 'delete_products']);
	}

	public function delete_products() {
		// count products
		$count = wp_count_posts('product');
		?>
		<div class="wrap">
			<?php if($count->publish > 0): ?>
			<h1>Delete all products</h1>
			<p>You currently have <strong><?php echo $count->publish; ?> published products</strong> in Woocommerce. Please type "DELETE" in the field below.</p>
			<form method="post" id="woodap-form" data-url="<?php echo admin_url( 'admin-post.php' ); ?>">
				<input type="text" name="what-do-you-want" placeholder="DELETE" autocomplete="off" required>
				<?php submit_button('Start Delete'); ?>
			</form>
			<div id="woodap-output" style="display: none;">
				<h2>Delete in Progress: <strong><span>0/<?php echo $count->publish; ?></span></strong></h2>
				<ul></ul>
			</div>
			<?php else: ?>
			<h1>Nothing to delete.</h1>
			<?php endif; ?>
		</div>

		<script type="text/javascript">
			var total_products = <?php echo $count->publish; ?>;
			var deleted = [];
			jQuery(function($) {
				// submit form
				$('#woodap-form').submit(function(e) {
					if($('[name="what-do-you-want"]', this).val() == 'DELETE') {
						if(confirm('Are you sure? There\'s no way back!')) {
							// hide form
							$(this).hide();
							$('#woodap-output').show();
							woodap();
						}
					} else {
						alert('Please type "DELETE" to start deleting.');
					}
					e.preventDefault();
				});
			});

			function woodap() {
				// trigger action
				jQuery.ajax({
					url: jQuery('#woodap-form').data('url')+'?action=woodap',
					type: 'POST',
					dataType: 'json',
					success: function(r) {
						if(r.length) {
							for(var i=0; i<r.length; i++) {
								deleted.push(r[i]);
								jQuery('#woodap-output ul').prepend('<li><strong>[DELETED]</strong> [ID: '+r[i].id+'] - '+r[i].name+'</li>');
							}
							jQuery('#woodap-output h2 span').text(deleted.length+'/'+total_products);

							// continue polling
							woodap();

						} else {
							jQuery('#woodap-output h2').text('Delete Completed!');
						}
					}
				});
			}
		</script>

		<?php
	}

	public function delete() {

		// find products
		$products = get_posts([
			'post_type' => 'product',
			'post_status' => 'publish',
			'posts_per_page' => 25
		]);

		// delete!
		$deleted = [];
		if(!empty($products)) {
			foreach($products as $v) {
				$deleted[] = [
					'id' => $v->ID,
					'name' => $v->post_title
				];
				wp_delete_post($v->ID, true);
			}
		}
		
		header('Content-Type: application/json');
		echo json_encode($deleted);
		die();
	}

	public static function install() {
	
		// add version option
		add_option( 'woodap_version', self::$version );
			
	}
	
	public static function deactivate() {
		// nothing here yet
	}
	
	public static function uninstall() {
		
		delete_option( 'woodap_version' );
		
	}
	

}

new WooDAP();