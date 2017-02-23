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
	    add_meta_box( 'woo_cwd_cart_action', __( 'Cart Action', 'textdomain' ), 'woo_cwd_cart_action_display_callback', 'woo_cwd_cart','side','high' );
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
				color: #fff;
				padding: 5px 10px;
			}
		</style>
		<div>
			<table cellspacing="20">
			    <tbody>
			        <tr>
			            <th align="left">Cart Status:</th>
			            <td><span class="abandoned"><?php echo get_post_meta($post->ID,"_cart_status")[0]; ?></span></td>
			        </tr>
			        <tr>
			            <th align="left">Cart Last Update:</th>
			            <td>
			            	<?php
			            		echo the_modified_date("F j, Y g:i a");
							?>
						</td>
			        </tr>
			        <tr>
			            <th align="left">User:</th>
			            <td><?php echo $post->post_title; ?></td>
			        </tr>
			        <tr>
			            <th align="left">User email:</th>
			            <td><a href="mailto:<?php echo get_post_meta($post->ID,'visitor_email',true); ?>"><?php echo get_post_meta($post->ID,'visitor_email',true); ?></a></td>
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
					<?php
						$cart_content=get_post_meta($post->ID,'cwd_cart_content');
						$cart_content=maybe_unserialize($cart_content[0]);
					?>
					<?php
						foreach ($cart_content['cart'] as $key => $value) {
							$_product = wc_get_product($value['product_id']);
							?>
							<tr>
								<td>
								<?php $thumbnail =  $_product->get_image(array(36,36));
					                if(!$_product->is_visible())
					                    echo $thumbnail;
					                else
					                    printf( '<a href="%s">%s</a>', $_product->get_permalink(), $thumbnail );
					            ?>					                
					            </td>
								<td>
									<a href="<?php echo $_product->get_permalink() ?>"><?php echo $_product->get_title() ?></a>
								</td>
								<td><?php echo $_product->get_price(); ?></td>
								<td><?php echo $value['quantity']; ?></td>
								<td><?php echo $value['line_total']; ?></td>
							</tr>
							<?php
						}
					?>
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
								<a href="http://localhost/abcte/wordpress/wp-admin/admin.php?page=woocommerce_cwd_recover_abandoned_cart&amp;tab=carts&amp;orderby=post_title&amp;order=asc">
									<span>Info</span><span class="sorting-indicator"></span>
								</a>
							</th>
							<th scope="col" id="email" class="manage-column column-email sortable desc">
								<a href="http://localhost/abcte/wordpress/wp-admin/admin.php?page=woocommerce_cwd_recover_abandoned_cart&tab=carts&orderby=visitor_email&order=asc">
									<span>Email</span><span class="sorting-indicator"></span>
								</a>
							</th>
							<th scope="col" id="subtotal" class="manage-column column-subtotal sortable desc">
								<a href="http://localhost/abcte/wordpress/wp-admin/admin.php?page=woocommerce_cwd_recover_abandoned_cart&amp;tab=carts&amp;orderby=cart_subtotal&amp;order=asc">
									<span>Subtotal</span><span class="sorting-indicator"></span>
								</a>
							</th>
							<th scope="col" id="status" class="manage-column column-status sortable desc">
								<a href="http://localhost/abcte/wordpress/wp-admin/admin.php?page=woocommerce_cwd_recover_abandoned_cart&amp;tab=carts&amp;orderby=_cart_status&amp;order=asc">
									<span>Status</span><span class="sorting-indicator"></span>
								</a>
							</th>
							<th scope="col" id="status_email" class="manage-column column-status_email sortable desc">
								<a href="http://localhost/abcte/wordpress/wp-admin/admin.php?page=woocommerce_cwd_recover_abandoned_cart&amp;tab=carts&amp;orderby=cart_email_sent&amp;order=asc">
									<span>Email sent</span><span class="sorting-indicator"></span>
								</a>
							</th>
							<th scope="col" id="last_update" class="manage-column column-last_update sortable desc">
								<a href="http://localhost/abcte/wordpress/wp-admin/admin.php?page=woocommerce_cwd_recover_abandoned_cart&amp;tab=carts&amp;orderby=last_update&amp;order=asc">
									<span>Last update</span><span class="sorting-indicator"></span>
								</a>
							</th>
							<th scope="col" id="action" class="manage-column column-action">Action</th>
						</tr>
					</thead>
					<tfoot>
						<tr>							
							<th scope="col" id="post_title" class="manage-column column-post_title column-primary sortable asc">
								<a href="http://localhost/abcte/wordpress/wp-admin/admin.php?page=woocommerce_cwd_recover_abandoned_cart&amp;tab=carts&amp;orderby=post_title&amp;order=desc">
									<span>Info</span><span class="sorting-indicator"></span>
								</a>
							</th>
							<th scope="col" id="email" class="manage-column column-email sortable asc">
								<a href="http://localhost/abcte/wordpress/wp-admin/admin.php?page=woocommerce_cwd_recover_abandoned_cart&amp;tab=carts&amp;orderby=visitor_email&amp;order=desc">
									<span>Email</span><span class="sorting-indicator"></span>
								</a>
							</th>
							<th scope="col" id="subtotal" class="manage-column column-subtotal sortable asc">
								<a href="http://localhost/abcte/wordpress/wp-admin/admin.php?page=woocommerce_cwd_recover_abandoned_cart&amp;tab=carts&amp;orderby=cart_subtotal&amp;order=desc">
									<span>Subtotal</span><span class="sorting-indicator"></span>
								</a>
							</th>
							<th scope="col" id="status" class="manage-column column-status sortable asc">
								<a href="http://localhost/abcte/wordpress/wp-admin/admin.php?page=woocommerce_cwd_recover_abandoned_cart&amp;tab=carts&amp;orderby=_cart_status&amp;order=desc">
									<span>Status</span><span class="sorting-indicator"></span>
								</a>
							</th>
							<th scope="col" id="status_email" class="manage-column column-status_email sortable asc">
								<a href="http://localhost/abcte/wordpress/wp-admin/admin.php?page=woocommerce_cwd_recover_abandoned_cart&amp;tab=carts&amp;orderby=cart_email_sent&amp;order=desc">
									<span>Email sent</span><span class="sorting-indicator"></span>
								</a>
							</th>
							<th scope="col" id="last_update" class="manage-column column-last_update sortable asc">
								<a href="http://localhost/abcte/wordpress/wp-admin/admin.php?page=woocommerce_cwd_recover_abandoned_cart&amp;tab=carts&amp;orderby=last_update&amp;order=desc">
									<span>Last update</span><span class="sorting-indicator"></span>
								</a>
							</th>
							<th scope="col" id="action" class="manage-column column-action">Action</th>
						</tr>
					</tfoot>
					<tbody>
						<?php
							if(isset($_REQUEST['orderby']) && isset($_REQUEST['order'])){
								if($_REQUEST['orderby']=='post_title'){
									$args = array(
										'post_type' => 'woo_cwd_cart',
										'orderby' => 'post_title',
										'order' => $_REQUEST['order'],
									);
								}
								else if($_REQUEST['orderby']=='last_update'){
									$args = array(
										'post_type' => 'woo_cwd_cart',
										'orderby' => 'post_date',
										'order' => $_REQUEST['order'],
									);
								}
								else{
									$args = array(
										'post_type' => 'woo_cwd_cart',
										'meta_key' => $_REQUEST['orderby'],
										'order' => $_REQUEST['order'],
									);
								}
							}
							else{
								$args = array(
									'post_type' => 'woo_cwd_cart'
								);
							}
							$the_query = new WP_Query( $args );
							if ( $the_query->have_posts() ) : 
								while ( $the_query->have_posts() ) : $the_query->the_post(); 
									if(get_post_meta(get_the_ID(),"_cart_status")[0]=='abandoned'){
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
												<?php echo get_post_meta(get_the_ID(),"_cart_status")[0]; ?>
											</td>
											<td>
												<?php echo get_post_meta(get_the_ID(),'cart_email_sent',true); ?>
											</td>
											<td>
												<?php echo the_modified_date("F j, Y g:i a"); ?>
											</td>
											<td>
												<input type="button" class="button action" value="Send email" data-id="<?php echo get_the_ID(); ?>">
											</td>
										</tr>
									<?php
										}
										endwhile; 
									?>
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
				        <tr>
				            <th scope="row">
				                <label for="woo_cwd_email_content">Email Template</label>
				            </th>
				            <td>
				                <select name="cwd_mail_template" id="cwd_mail_template">
				                	<option value="">-- Select Template --</option>
				                	<option value="marketing-grid">Grid</option>
				                	<option value="marketing-list-left">Left Side List</option>
				                	<option value="marketing-list-right">Right Side List</option>
				                </select>
							</td>
				        </tr>
				    </tbody>
				</table>
				<div class="template_previews">
				</div>
				<input class="button-primary" type="submit" value="Save Changes">
        	</form>
        	<script type="text/javascript">
        		jQuery(document).ready(function(){
        			jQuery("body").on("change","#cwd_mail_template",function(){
        				if(jQuery(this).val()=='marketing-grid'){
        					jQuery('.template_previews').html('<h4>Preview</h4><img src="<?php echo plugin_dir_url(__FILE__);?>include/img/grid.png"><br><br>');
        				}
        				else if(jQuery(this).val()=='marketing-list-left'){
        					jQuery('.template_previews').html('<h4>Preview</h4><img src="<?php echo plugin_dir_url(__FILE__);?>include/img/left-side-list.png"><br><br>');
        				}
        				else if(jQuery(this).val()=='marketing-list-right'){
        					jQuery('.template_previews').html('<h4>Preview</h4><img src="<?php echo plugin_dir_url(__FILE__);?>include/img/right-side-list.png"><br><br>');
        				}
        				else{
        					jQuery('.template_previews').html('');
        				}
        			});
        		});
        	</script>
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
			$has_previous_cart=has_previous_cart($user_id);
			if( ! $has_previous_cart ){
		        $user_details = get_userdata($user_id);
		        $user_email   = $user_details->user_email;
		        $post = array(
	                'post_content' => '',
	                'post_status'  => 'publish',
	                'post_title'   => $user_details->display_name,
	                'post_type'    => 'woo_cwd_cart'
		        );
		        $post_id = wp_insert_post($post);
		        update_post_meta($post_id, 'visitor_email', $user_email);
		        update_post_meta($post_id, 'cart_email_sent', 'Not sent');
		        $subtotal = (WC()->cart->tax_display_cart == 'excl') ? WC()->cart->subtotal_ex_tax :  WC()->cart->subtotal;
		        update_post_meta($post_id, 'cart_subtotal', $subtotal );
		        update_post_meta( $post_id, 'cwd_user_id', $user_id);
		    }else{
		    	$post_id = $has_previous_cart->ID;
                $post_updated = array(
                    'ID' => $post_id,
                    'post_date' => $has_previous_cart->post_date,
                    'post_type' => 'woo_cwd_cart'
                );

                wp_update_post( $post_updated );
		    }
		    update_post_meta( $post_id, '_cart_status', 'open');
		    update_post_meta( $post_id, 'cwd_cart_content', get_item_cart() );
		    $subtotal = ( WC()->cart->tax_display_cart == 'excl' ) ? WC()->cart->subtotal_ex_tax :  WC()->cart->subtotal;
		    update_post_meta( $post_id, 'cart_subtotal', $subtotal );
		}else{			
			
		}
	}
	function woo_visitor_cart_updated(){
		if(!is_user_logged_in()){
		?>
		<script type="text/javascript">
			jQuery(document).ready(function(){
				var email=jQuery('#billing_email').val();
				if(email.length>5){
					var pattern = new RegExp(/^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/);
        			var validate=pattern.test(email);
        			if(validate==true){
        				var fname=jQuery('#billing_first_name').val();
        				var lname=jQuery('#billing_last_name').val();
        				jQuery.ajax({
				         	type : "post",
				         	dataType : "json",
				         	url : wc_checkout_params.ajax_url,
				         	data : {action:"create_cwd_cart_cpt_post",'email':email,'fname':fname,'lname':lname},
				         	success: function(response) {
				            	
				         	}
				      	});
        			}
				}
			});
		</script>
		<?php
		}
	}
	add_action('woocommerce_review_order_before_order_total', 'woo_visitor_cart_updated', 10);
	add_action("wp_ajax_nopriv_create_cwd_cart_cpt_post", "create_cwd_cart_cpt_post");
	function create_cwd_cart_cpt_post(){
		if(!is_user_logged_in()){
			$user_id      = get_current_user_id();
			$user_email   = $_POST['email'];
			$has_previous_user=has_previous_user($user_email);
			if(!$has_previous_user){
				if(isset($_POST['fname'])){
					$name=$_POST['fname']." ";
				}
				if(isset($_POST['lname'])){
					$name.=$_POST['lname'];
				}
				if(!isset($_POST['fname']) && !isset($_POST['lname'])){
					$name=$_POST['email'];
				}
		        $post = array(
	                'post_content' => '',
	                'post_status'  => 'publish',
	                'post_title'   => $name,
	                'post_type'    => 'woo_cwd_cart'
		        );
		        $post_id = wp_insert_post($post);
		        update_post_meta($post_id, 'visitor_email', $user_email);
		        update_post_meta($post_id, 'cart_email_sent', 'Not sent');
		        $subtotal = (WC()->cart->tax_display_cart == 'excl') ? WC()->cart->subtotal_ex_tax :  WC()->cart->subtotal;
		        update_post_meta($post_id, 'cart_subtotal', $subtotal );
		        update_post_meta( $post_id, 'cwd_user_id', $user_id);
		    }
		    else{
		    	$post_id=$has_previous_user->ID;
		    	$post_updated = array(
                    'ID' => $post_id,
                    'post_date' => $has_previous_cart->post_date,
                    'post_type' => 'woo_cwd_cart'
                );
                wp_update_post( $post_updated );
		    }
		}
		update_post_meta( $post_id, '_cart_status', 'open');
	    update_post_meta( $post_id, 'cwd_cart_content', get_item_cart() );
	    $subtotal = ( WC()->cart->tax_display_cart == 'excl' ) ? WC()->cart->subtotal_ex_tax :  WC()->cart->subtotal;
	    update_post_meta( $post_id, 'cart_subtotal', $subtotal );	    
	}
	function has_previous_user($mail){
		$args = array(
            'post_type'   => 'woo_cwd_cart',
            'meta_key'    => 'visitor_email',
            'meta_value'  => $mail,
            'post_status' => 'publish'
        );
        $r = get_posts($args);
        if( empty($r) ){
            return false;
        }else{
            return $r[0];
        }
	}
	function has_previous_cart( $user_id ){
        $args = array(
            'post_type'   => 'woo_cwd_cart',
            'meta_key'    => 'cwd_user_id',
            'meta_value'  => $user_id,
            'post_status' => 'publish'
        );

        $r = get_posts($args);
        if( empty($r) ){
            return false;
        }else{
            return $r[0];
        }
    }
    function get_item_cart() {
        if ( is_user_logged_in() ) {
            $user_id = get_current_user_id();
            $cart    = maybe_serialize(get_user_meta($user_id,'_woocommerce_persistent_cart', true ));
        }
        else {
            $cart = maybe_serialize(array('cart'=>WC()->session->get('cart')));
        }
        return $cart;
    }
    add_action('init','update_carts');
    function update_carts(){
    	if(is_user_logged_in()){
	    	$cutoff=60*get_option('woo_cwd_cart_cut_off_time');
	        $start_to_date=(int)(time() - $cutoff);
	        $args = array(
	            'post_type'   => 'woo_cwd_cart',
	            'post_status' => 'publish',
	            'meta_value'  => 'open',
	            'meta_key'    => '_cart_status',
	            'date_query' => array(
	                array(
	                    'column' => 'post_modified_gmt',
	                    'before'  => date("Y-m-d H:i:s", $start_to_date),
	                )
	            ),
	        );
	        $p = get_posts($args);
	        if(!empty($p)){
	            foreach( $p as $post ){
	                update_status($post);
	            }
	        }
	    }else{

	    }
    }
    function update_status($cart){
        $current_status = get_post_meta($cart->ID,'_cart_status',true);
        $post_modified = strtotime( $cart->post_modified );
        $current_time = time();
        if(($current_time-$post_modified)>$cutoff){
            if($current_status=='open'){
            	update_post_meta($cart->ID,'_cart_status','abandoned');
            }
        }
    }
    add_action( 'woocommerce_thankyou', 'woo_cwd_cart_thank_you_page');
    function woo_cwd_cart_thank_you_page($order){
    	if(is_user_logged_in()){
    		$user_id      = get_current_user_id();
    		$args = array(
	            'post_type'   => 'woo_cwd_cart',
	            'meta_key'    => 'cwd_user_id',
	            'meta_value'  => $user_id,
	            'post_status' => 'publish'
	        );

	        $r = get_posts($args);
	        if(empty($r)){
	            return false;
	        }else{
	            foreach ($r as $k => $v) {
	            	wp_delete_post($v->ID);
	            }
	        }
    	}else{
    		$order = new WC_Order($order);
    		$mail=$order->billing_email;
    		$args = array(
	            'post_type'   => 'woo_cwd_cart',
	            'meta_key'    => 'visitor_email',
	            'meta_value'  => $mail,
	            'post_status' => 'publish'
	        );
	        $r = get_posts($args);
	        if(empty($r)){
	            return false;
	        }else{
	            foreach ($r as $k => $v) {
	            	wp_delete_post($v->ID);
	            }
	        }
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