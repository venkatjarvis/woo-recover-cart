<?php
/*
	Plugin Name: Woocomerce CWD Recover Abandoned Cart
	Description: This plugin helps you manage easily and efficiently all the abandoned carts of your customers.
	Version: 1.0
	Author: Coral Web Designs
	Author URI: http://coralwebdesigns.com/
*/
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	
	function create_woo_cwd_cart_posttype() {		
		$labels = array(			
			'edit_item'          => __( 'Edit Abandoned Cart', 'your-plugin-textdomain' )
		);
		register_post_type( 'woo_cwd_cart',
			array(
				'labels' => $labels,
                'supports' => array('title'),
                'hierarchical' => false,
                'public' => false,
                'show_ui' => true,
                'show_in_menu' => false,
                'exclude_from_search' => true,
                'capability_type' => 'post',
                'capabilities'       => array( 'create_posts' => false ),
                'map_meta_cap'       => true
			)
		);
	}
	add_action( 'init', 'create_woo_cwd_cart_posttype' );
	add_filter('post_row_actions','my_action_row', 10, 2);
	function my_action_row($actions, $post){
	    if ($post->post_type =="woo_cwd_cart"){
	    	unset($actions['trash']);
	    	unset($actions['inline hide-if-no-js']);
	    	unset($actions['view']);
	    	unset($actions['edit']);
	    	$actions['cart_view']='<a href="post.php?post=' . $post->ID . '&action=edit">View</a>';	        
	    }
	    return $actions;
	}
	add_action('admin_head', 'wpds_custom_admin_post_css');
	function wpds_custom_admin_post_css() {
	    global $post_type;
	    if ($post_type == 'woo_cwd_cart') {
	        echo "<style>#edit-slug-box {display:none;}</style>";
	    }
	}
	function remove_woo_cwd_cart_publish_box() {
		remove_meta_box( 'submitdiv', 'woo_cwd_cart', 'side' );
	}
	add_action( 'admin_menu', 'remove_woo_cwd_cart_publish_box' );
	function custom_woo_cwd_cart_action_meta_boxes() {
	    add_meta_box( 'woo_cwd_cart_action', __( 'Cart Action', 'textdomain' ), 'woo_cwd_cart_action_display_callback', 'woo_cwd_cart','normal','high' );
	}
	add_action( 'add_meta_boxes', 'custom_woo_cwd_cart_action_meta_boxes' );
	function woo_cwd_cart_action_display_callback(){
		global $post;
		?>
		<div>
			<table cellspacing="20">
			    <tbody>
			        <tr>
			            <th align="left">Email sent:</th>
			            <td>Not sent</td>
			        </tr>
			        <tr>
			            <th align="left">Email action:</th>
			            <td><input type="button" id="sendemail" class="button" value="Send email" data-id="<?php echo $post->ID; ?>" wtx-context="471E8A0B-0553-4E67-BF85-F3FA9DD421DF"></td>
			        </tr>
			    </tbody>
			</table>
		</div>
		<?php
	}
	function custom_woo_cwd_cart_info_meta_boxes() {
	    add_meta_box( 'woo_cwd_cart_info', __( 'Cart Info', 'textdomain' ), 'woo_cwd_cart_info_display_callback', 'woo_cwd_cart','normal','high' );
	}
	add_action( 'add_meta_boxes', 'custom_woo_cwd_cart_info_meta_boxes' );
	function woo_cwd_cart_info_display_callback(){
		global $post;
		?>
		<style>
			.abandoned{
				background: red;
				text-transform: uppercase;
			}
		</style>
		<div>
			<table cellspacing="20">
			    <tbody>
			        <tr>
			            <th align="left">Cart Status:</th>
			            <td><span class="abandoned">ABANDONED</span></td>
			        </tr>
			        <tr>
			            <th align="left">Cart Last Update:</th>
			            <td>2017-01-19 04:55:19am</td>
			        </tr>
			        <tr>
			            <th align="left">User:</th>
			            <td>abcte</td>
			        </tr>
			        <tr>
			            <th align="left">User email:</th>
			            <td><a href="mailto:svenkatesan1995@gmail.com">svenkatesan1995@gmail.com</a></td>
			        </tr>
			    </tbody>
			</table>
		</div>
		<?php
	}
	function custom_woo_cwd_cart_content_meta_boxes() {
	    add_meta_box( 'woo_cwd_cart_content', __( 'Cart Content', 'textdomain' ), 'woo_cwd_cart_content_display_callback', 'woo_cwd_cart','normal','high' );
	}
	add_action( 'add_meta_boxes', 'custom_woo_cwd_cart_content_meta_boxes' );
	function woo_cwd_cart_content_display_callback(){
		global $post;
		?>
		<div>
			<table cellspacing="20">
				<thead>
				    <tr>
				        <th class="product-thumbnail">Thumbnail</th>
				        <th class="product-name">Product</th>
				        <th class="product-single">Product Price</th>
				        <th class="product-quantity">Quantity</th>
				        <th class="product-subtotal">Total</th>
				    </tr>
				</thead>
				<tbody>
					
				</tbody>
			</table>
		</div>
		<?php
	}
	function woo_cwd_recover_cart_menu() {
	    add_menu_page(
	        __( 'Woo CWD Cart', 'textdomain' ),
	        'Woo CWD Cart',
	        'manage_options',
	        'woocommerce_cwd_recover_abandoned_cart',
	        'woocommerce_cwd_recover_abandoned_cart_page',	        
	        '',
	        6
	    );
	}
	function my_plugin_load_js() {
		wp_enqueue_script('jquery-ui-tabs');
	}
	function mypluginjs() {
		echo '<script>
		jQuery(function() {
		    jQuery("#tabs").tabs();
		});
		</script>';
	}
	add_action( 'admin_enqueue_scripts', 'mypluginjs' );
	add_action('admin_enqueue_scripts', 'my_plugin_load_js' );

	function woocommerce_cwd_recover_abandoned_cart_page(){
		function page_tabs($current = 'settings') {
		    $tabs = array(
		        'settings'   => __("Settings", 'plugin-textdomain'), 
		        'carts'  => __("Carts", 'plugin-textdomain'),
		        'email'	=> __("Email Template",'plugin-textdomain')
		    );
		    $html =  '<h2 class="nav-tab-wrapper">';
		    foreach( $tabs as $tab => $name ){
		        $class = ($tab == $current) ? 'nav-tab-active' : '';
		        $html .=  '<a class="nav-tab ' . $class . '" href="?page=woocommerce_cwd_recover_abandoned_cart&tab=' . $tab . '">' . $name . '</a>';
		    }
		    $html .= '</h2>';
		    echo $html;
		}		
		$tab = (!empty($_GET['tab']))? esc_attr($_GET['tab']) : 'settings';
		page_tabs($tab);
		if($tab == 'carts' ) {
			?>
			<div class="wrap">
				<h2>Carts</h2>
				<table class="wp-list-table widefat fixed striped">
					<thead>
						<tr>							
							<th scope="col" id="post_title" class="manage-column column-post_title column-primary sortable desc">
								<a href="http://localhost/abcte/wordpress/wp-admin/admin.php?page=yith_woocommerce_recover_abandoned_cart&amp;tab=carts&amp;orderby=post_title&amp;order=asc">
									<span>Info</span><span class="sorting-indicator"></span>
								</a>
							</th>
							<th scope="col" id="email" class="manage-column column-email sortable desc">
								<a href="http://localhost/abcte/wordpress/wp-admin/admin.php?page=yith_woocommerce_recover_abandoned_cart&amp;tab=carts&amp;orderby=email&amp;order=asc">
									<span>Email</span><span class="sorting-indicator"></span>
								</a>
							</th>
							<th scope="col" id="subtotal" class="manage-column column-subtotal sortable desc">
								<a href="http://localhost/abcte/wordpress/wp-admin/admin.php?page=yith_woocommerce_recover_abandoned_cart&amp;tab=carts&amp;orderby=email&amp;order=asc">
									<span>Subtotal</span><span class="sorting-indicator"></span>
								</a>
							</th>
							<th scope="col" id="status" class="manage-column column-status sortable desc">
								<a href="http://localhost/abcte/wordpress/wp-admin/admin.php?page=yith_woocommerce_recover_abandoned_cart&amp;tab=carts&amp;orderby=status&amp;order=asc">
									<span>Status</span><span class="sorting-indicator"></span>
								</a>
							</th>
							<th scope="col" id="status_email" class="manage-column column-status_email sortable desc">
								<a href="http://localhost/abcte/wordpress/wp-admin/admin.php?page=yith_woocommerce_recover_abandoned_cart&amp;tab=carts&amp;orderby=status_email&amp;order=asc">
									<span>Email sent</span><span class="sorting-indicator"></span>
								</a>
							</th>
							<th scope="col" id="last_update" class="manage-column column-last_update sortable desc">
								<a href="http://localhost/abcte/wordpress/wp-admin/admin.php?page=yith_woocommerce_recover_abandoned_cart&amp;tab=carts&amp;orderby=last_update&amp;order=asc">
									<span>Last update</span><span class="sorting-indicator"></span>
								</a>
							</th>
							<th scope="col" id="action" class="manage-column column-action">Action</th>
						</tr>
					</thead>
					<tfoot>
						<tr>							
							<th scope="col" id="post_title" class="manage-column column-post_title column-primary sortable desc">
								<a href="http://localhost/abcte/wordpress/wp-admin/admin.php?page=yith_woocommerce_recover_abandoned_cart&amp;tab=carts&amp;orderby=post_title&amp;order=asc">
									<span>Info</span><span class="sorting-indicator"></span>
								</a>
							</th>
							<th scope="col" id="email" class="manage-column column-email sortable desc">
								<a href="http://localhost/abcte/wordpress/wp-admin/admin.php?page=yith_woocommerce_recover_abandoned_cart&amp;tab=carts&amp;orderby=email&amp;order=asc">
									<span>Email</span><span class="sorting-indicator"></span>
								</a>
							</th>
							<th scope="col" id="subtotal" class="manage-column column-subtotal sortable desc">
								<a href="http://localhost/abcte/wordpress/wp-admin/admin.php?page=yith_woocommerce_recover_abandoned_cart&amp;tab=carts&amp;orderby=email&amp;order=asc">
									<span>Subtotal</span><span class="sorting-indicator"></span>
								</a>
							</th>
							<th scope="col" id="status" class="manage-column column-status sortable desc">
								<a href="http://localhost/abcte/wordpress/wp-admin/admin.php?page=yith_woocommerce_recover_abandoned_cart&amp;tab=carts&amp;orderby=status&amp;order=asc">
									<span>Status</span><span class="sorting-indicator"></span>
								</a>
							</th>
							<th scope="col" id="status_email" class="manage-column column-status_email sortable desc">
								<a href="http://localhost/abcte/wordpress/wp-admin/admin.php?page=yith_woocommerce_recover_abandoned_cart&amp;tab=carts&amp;orderby=status_email&amp;order=asc">
									<span>Email sent</span><span class="sorting-indicator"></span>
								</a>
							</th>
							<th scope="col" id="last_update" class="manage-column column-last_update sortable desc">
								<a href="http://localhost/abcte/wordpress/wp-admin/admin.php?page=yith_woocommerce_recover_abandoned_cart&amp;tab=carts&amp;orderby=last_update&amp;order=asc">
									<span>Last update</span><span class="sorting-indicator"></span>
								</a>
							</th>
							<th scope="col" id="action" class="manage-column column-action">Action</th>
						</tr>
					</tfoot>
					<tbody>
						<?php
							$args = array('post_type' => 'woo_cwd_cart');
							$the_query = new WP_Query( $args ); 
							if ( $the_query->have_posts() ) : 
								while ( $the_query->have_posts() ) : $the_query->the_post(); 
								?>
									<tr>
										<td>
											<?php the_title(); ?>
											<div class="row-actions">
												<span class="cart_view">
													<a href="post.php?post=<?php echo get_the_ID(); ?>&action=edit">View</a>
												</span>
											</div>
										</td>
										<td>
											<?php echo get_post_meta(get_the_ID(),'visitor_email',true); ?>
										</td>
										<td>
											<?php echo get_post_meta(get_the_ID(),'cart_subtotal',true); ?>
										</td>
										<td>
											<?php echo get_post_meta(get_the_ID(),'cart_status',true); ?>
										</td>
										<td>
											<?php echo get_post_meta(get_the_ID(),'cart_email_sent',true); ?>
										</td>
										<td>
											<?php echo get_post_meta(get_the_ID(),'cart_last_update',true); ?>
										</td>
										<td>
											<input type="button" class="button action" value="Send email" data-id="<?php echo get_the_ID(); ?>">
										</td>
									</tr>
									<?php endwhile; ?>
									<?php wp_reset_postdata(); ?>
								<?php else : ?>
									<tr><td colspan="7">No items found.</td></tr>
							<?php endif;
						?>
					</tbody>
				</table>
			</div>
			<?php
		}
		else if($tab == 'email'){
			if($_POST){
				if(isset($_POST['woo_cwd_email_sender_name'])){
					update_option( "woo_cwd_email_sender_name", $_POST['woo_cwd_email_sender_name'], $autoload );
				}
				if(isset($_POST['woo_cwd_email_sender'])){
					update_option( "woo_cwd_email_sender", $_POST['woo_cwd_email_sender'], $autoload );
				}
				if(isset($_POST['woo_cwd_email_subject'])){
					update_option( "woo_cwd_email_subject", $_POST['woo_cwd_email_subject'], $autoload );
				}
				if(isset($_POST['woo_cwd_email_content'])){
					update_option( "woo_cwd_email_content", $_POST['woo_cwd_email_content'], $autoload );
				}
			}
			?>
			<form id="woo_cwd_mail" method="post">
				<h2>Email Template</h2>
				<table class="form-table">
					<tbody>
						<tr>
							<th scope="row">
								<label for="woo_cwd_email_sender_name">Email Sender Name</label>
							</th>
							<td>
								<input name="woo_cwd_email_sender_name" id="woo_cwd_email_sender_name" value="<?php echo get_option('woo_cwd_email_sender_name'); ?>" type="text" class="regular-text">
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="woo_cwd_email_sender">Email Sender</label>
							</th>
							<td>
								<input name="woo_cwd_email_sender" id="woo_cwd_email_sender" value="<?php echo get_option('woo_cwd_email_sender'); ?>" type="text" class="regular-text">
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="woo_cwd_email_subject">Email Subject</label>
							</th>
							<td>
								<input name="woo_cwd_email_subject" id="woo_cwd_email_subject" value="<?php echo get_option('woo_cwd_email_subject'); ?>" type="text" class="regular-text">
							</td>
						</tr>
						<tr>
				            <th scope="row">
				                <label for="woo_cwd_email_content">Email content</label>
				            </th>
				            <td>
				                <?php wp_editor(get_option('woo_cwd_email_content'), "woo_cwd_email_content"); ?>
							</td>
				        </tr>
				    </tbody>
				</table>				
				<input class="button-primary" type="submit" value="Save Changes">
        	</form>
			<?php
		}
		else{
			if($_POST){
				if(isset($_POST['woo_cwd_cart_enable'])){
					update_option( "woo_cwd_cart_enable", $_POST['woo_cwd_cart_enable'], $autoload );					
				}
				if(!isset($_POST['woo_cwd_cart_enable'])){
					update_option( "woo_cwd_cart_enable", "off", $autoload );
				}
				if(isset($_POST['woo_cwd_cart_cut_off_time'])){
					update_option( "woo_cwd_cart_cut_off_time", $_POST['woo_cwd_cart_cut_off_time'], $autoload );
				}
			}
			?>
			<form id="woo_cwd_general_settings" method="post">
            	<h2>General settings</h2>
            	<table class="form-table">
            		<tbody>
            			<tr>
							<th scope="row">
								Enable Recover Abandoned Cart
							</th>
							<td>
								<input name="woo_cwd_cart_enable" id="woo_cwd_cart_enable" <?php if(get_option('woo_cwd_cart_enable') == 'on'){echo 'checked="checked"';} ?> type="checkbox">
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="woo_cwd_cart_cut_off_time">Cart abandoned cut-off time</label>
							</th>
							<td>
								<input name="woo_cwd_cart_cut_off_time" id="woo_cwd_cart_cut_off_time" type="text" value="<?php echo get_option('woo_cwd_cart_cut_off_time'); ?>" class="regular-text">
								<span class="description">Minutes that have to pass to consider a cart abandoned</span>
							</td>
						</tr>
					</tbody>
				</table>
				<input class="button-primary" type="submit" value="Save Changes">
        	</form>
			<?php
		}
		?>		
		<?php
	}
	add_action('admin_menu', 'woo_cwd_recover_cart_menu');
	add_action('woocommerce_cart_updated','woo_cwd_cart_update');
	function woo_cwd_cart_update(){
		if(is_user_logged_in()){
			$user_id      = get_current_user_id();
            $user_details = get_userdata($user_id);
            $user_email   = $user_details->user_email;            
            $post = array(
                'post_content' => '',
                'post_status'  => 'publish',
                'post_title'   => $user_details->display_name,
                'post_type'    => 'woo_cwd_cart'
            );
            $post_id = wp_insert_post($post);
            //update_post_meta( $post_id, '_user_id', $user_id);
            update_post_meta( $post_id, 'visitor_email', $user_email);
            update_post_meta( $post_id, 'cart_email_sent', 'Not sent');
		}
	}
}
else {
	?>
	<div id="message" class="updated notice is-dismissible"><p>WooCommerce CWD Recover Abandoned Cart is enabled but not effective. It requires WooCommerce in order to work.</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>
	<?php
  return;
}
?>