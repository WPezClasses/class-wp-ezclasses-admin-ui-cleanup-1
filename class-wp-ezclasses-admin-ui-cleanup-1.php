<?
/** 
 * Engine that automates the cleanup of the WordPress Admin UI. Remove this, filter that, action ahoy. Things done on the regular (or not) all array / option driven.
 *
 * Long description TODO (@link http://)
 *
 * PHP version 5.3
 *
 * LICENSE: MIT 
 *
 * @package WP ezClasses
 * @dependencies: Class_WP_ezClasses_Images_Helper_Methods
 * @author Mark Simchock <mark.simchock@alchemyunited.com>
 * @since 0.5.0
 * @license MIT
 */
 
/*
 * == Change Log == 
 *
 *  -- FIXED: Issue (warning) with unset in method: media_box_unset_default_sizes()
 *
 * 
 */

 
if (!class_exists('Class_WP_ezClasses_Admin_UI_Cleanup_1')) {
	class Class_WP_ezClasses_Admin_UI_Cleanup_1 extends Class_WP_ezClasses_Master_Singleton {
		
		protected $_arr_posts_all_posts_list_remove_quick_edit;			// posts types NOT to remove Quick Edit from (i.e. QE remains)
		protected $_arr_posts_all_posts_list_remove_edit_trash;			// posts types NOT to remove Edit and Trash from 
		protected $_arr_posts_all_posts_list_remove_edit_trash_view;	// posts types NOT to remove Edit, Trash and View from
		protected $_arr_pages_all_pages_list_remove_quick_edit;
		protected $_arr_pages_all_pages_list_remove_edit_trash;
		protected $_arr_pages_all_pages_list_remove_edit_trash_view;
		protected $_arr_post_row_actions_remove;
		
		protected $_arr_posts_all_pages_all_remove_bulk_actions;
		protected $_arr_manage_custom_columns_remove_yoast_seo;			// the yoast cols we'll remove
		protected $_arr_manage_post_custom_columns_remove_yoast_seo;
		protected $_arr_manage_page_custom_columns_remove_yoast_seo;	
	
		protected $_arr_globals_wp_filter_unset_action_or_filter;
		protected $_arr_media_box_unset_default_sizes;
		protected $_arr_media_box_default_settings;
		protected $_arr_media_box_default_sizes_add_custom;

		/**
		 *
		 */
		protected function __construct(){
			parent::__construct();
		}
		
		/**
		 *
		 */
		public function ezc_init(){ 
			// Nuttin' - yet. 
		}
		
		
		/**
		 * Takes the array of args and makes the necessary magic. 
		 */
		public function cleanup_do($arr_args = NULL){
		
			if (isset( $this->ezCONFIGS['validate']) && $this->ezCONFIGS['validate'] !== false ){
				$arr_args = $this->cleanup_validate($arr_args);
			}
			
			if ( is_array($arr_args) && ! empty($arr_args) ){
				foreach ($arr_args as $str_key => $arr_value){
				
					/*
					 * properties are defined above 
					 */
					if ( isset($arr_value['arr_args']) && is_array($arr_value['arr_args']) ){
						$str_property_to_set = '_arr_' . trim($arr_value['method_name']);
						$this->$str_property_to_set = $arr_value['arr_args'];
					}
		
					$str_method_name = trim($arr_value['method_name']);
					if ( $arr_value['hook_type'] == 'action' ){
					
						$str_method_name = trim($arr_value['method_name']);
						add_action( $arr_value['hook_name'], array($this, $arr_value['method_name']), $arr_value['priority'], $arr_value['args_count'] );
					
					} elseif ( $arr_value['hook_type'] == 'remove_action' ) {
						/*
						 * TODO remove_action() = 
						 */
						 
						// $str_method_name = trim($arr_value['method_name']);
						// remove_action( $arr_value['hook_name'], $arr_value['method_name'], $int_priority, $int_args_count );					
					} else {
						/*
						 * add_filter
						 */
						add_filter( $arr_value['hook_name'], array($this, $arr_value['method_name']), $arr_value['priority'], $arr_value['args_count'] );
						
					}
				}
			}
		}
		
		/*
		 * Just a quick simple way to change an defaults. 
		 */
		protected function cleanup_defaults(){
		
			$arr_defaults = array(
								'priority'		=> 10,
								'args_count'	=> 0,
							);
			return $arr_defaults;
		}
		
		
		protected function cleanup_validate($arr_args = array()){
		
			if ( is_array($arr_args) && !empty($arr_args) ){
			
				$arr_defaults = $this->cleanup_defaults();
			
				foreach ($arr_args as $str_key => $arr_value){
				
					if (( isset($arr_value['active']) && $arr_value['active'] === true ) && 
						( isset($arr_value['hook_type']) && is_string($arr_value['hook_type']) ) &&
						( $arr_value['hook_type'] == 'action' || $arr_value['hook_type'] == 'filter' || $arr_value['hook_type'] == 'remove_action' ) &&
						( isset($arr_value['hook_name']) && is_string($arr_value['hook_name']) ) &&
						( isset($arr_value['method_name']) && is_string($arr_value['method_name']) ) ){
						
						// TODO - method_exists()
						
						if ( ! isset($arr_value['priority']) || ! is_int($arr_value['priority']) ){
							$arr_value['priority'] = $arr_defaults['priority'];
						}
						
						if ( ! isset($arr_value['args_count']) || is_int($arr_value['args_count']) ){
							// TODO - check args_count against count(arr_args) 
							$arr_value['args_count'] = $arr_defaults['args_count'];
						}
			
					} else {
						// if something isn't quite right we'll unset() and keep going
						unset ($arr_args[$str_key]);
					}
				}
			}
		}
		
		/*
		 * Apparently WP requires a one-to-one between filter and method. 
		 * So to get to the main method (manage_custom_columns_remove_yoast_seo()) we use this proxy / wrapper method.
		 * That is, we're able to share the same code (read: end method) across a number of proxy / wrapper methods.
		 */
		public function manage_post_custom_columns_remove_yoast_seo( $arr_cols ){
			$this->_arr_manage_custom_columns_remove_yoast_seo = $this->_arr_manage_post_custom_columns_remove_yoast_seo;
			return $this->manage_custom_columns_remove_yoast_seo( $arr_cols );
		}
		
		/*
		 * Apparently WP requires a one-to-one between filter and method. 
		 * So to get to the main method (manage_custom_columns_remove_yoast_seo()) we use this proxy / wrapper method.
		 * That is, we're able to share the same code (read: end method) across a number of proxy / wrapper methods.
		 */	
		public function manage_page_custom_columns_remove_yoast_seo( $arr_cols ){
			$this->_arr_manage_custom_columns_remove_yoast_seo = $this->_arr_manage_page_custom_columns_remove_yoast_seo;
			return $this->manage_custom_columns_remove_yoast_seo( $arr_cols );
		}
		
		/*
		 *
		 */
		 public function manage_custom_columns_remove_yoast_seo( $arr_cols ){
		 
			global $current_screen;

			if ( WP_ezMethods::array_pass($this->_arr_manage_custom_columns_remove_yoast_seo) ){
				foreach ( $this->_arr_manage_custom_columns_remove_yoast_seo as $str_screen_post_type => $arr_remove_cols ){
					if ( $current_screen->post_type == $str_screen_post_type ){
						foreach ( $arr_remove_cols as $str_remove_col){
							unset($arr_cols[$str_remove_col]);
						}
					}
				}
			}
			return $arr_cols;
		}
		
		/**
		 *
		 */
		 public function wp_before_admin_bar_render_remove_yoast_seo(){
		 	
			global $wp_admin_bar;
			$wp_admin_bar->remove_menu('wpseo-menu');
		 }
		

		/*
		 * http://wordpress.org/support/topic/how-to-remove-boxes-from-the-dashboard
		 */ 
		public function admin_dashboard_remove_quick_press(){
		
			global $wp_meta_boxes;
			unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_quick_press']);
		}
		
		public function admin_dashboard_remove_incoming_links(){
		
			global $wp_meta_boxes;
			unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_incoming_links']);
		}
		
		public function admin_dashboard_remove_right_now(){
		
			global $wp_meta_boxes;
			unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_right_now']);
		}
		
		public function admin_dashboard_remove_dashboard_recent_drafts(){
		
			global $wp_meta_boxes;
			unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_recent_drafts']);
		}
		
		/*
		 * aka WordPress blog
		 */
		public function admin_dashboard_remove_dashboard_primary(){
		
			global $wp_meta_boxes;
			unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_primary']);
		}
		
		/*
		 * aka Other WordPress News
		 */
		public function admin_dashboard_remove_dashboard_secondary(){
		
			global $wp_meta_boxes;
			unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_secondary']);
		}
		
		
		public function admin_dashboard_remove_dashboard_recent_comments(){
		
			global $wp_meta_boxes;
			unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_recent_comments']);
		}
		

		/**
		 * Appearance > Editor can be dangerous, let's sack it. 
		 */
		public function appearance_editor_remove_submenu_page(){
			remove_submenu_page( 'themes.php', 'theme-editor.php' ); 
		}
		
		
		/**
		 * Let's not let just anybody modify the Settings > Media settings
		 */			
		public function settings_media_remove_submenu_page(){		
			// remove the regular Settings > Media menu
			remove_submenu_page( 'options-general.php', 'options-media.php'); 
		}
		
		
		
		/*
		 * This is the mother of all action remove methods. Automation is full effect. 
		 */
		public function post_row_actions_remove($arr_actions, $obj_post){
		
			global $current_screen;
			
			$arr_args = $this->_arr_post_row_actions_remove;
			
			if ( isset($arr_args['exclude']) && WP_ezMethods::array_pass($arr_args['remove']) ){
				$str_screen = $current_screen->post_type;
				if( in_array($str_screen, $arr_args['exclude']) ){
					return $arr_actions;
				}
				foreach ( $arr_args['remove'] as $str_remove ){
					unset( $arr_actions[$str_remove] );
				}
				return $arr_actions;
			}
		}
		
		/*
		 * Apparently WP requires a one-to-one between filter and method. 
		 * So to get to the main method (post_row_actions_remove()) we use a proxy / wrapper method.
		 * That is, we're able to share the same code (read: end method) across a number of proxy / wrapper methods.
		 */	
		public function posts_all_posts_list_remove_quick_edit($arr_actions, $obj_post){
			$this->_arr_post_row_actions_remove = $this->_arr_posts_all_posts_list_remove_quick_edit;
			return $this->post_row_actions_remove($arr_actions, $obj_post);
		}

		public function posts_all_posts_list_remove_edit_trash($arr_actions, $obj_post){
			$this->_arr_post_row_actions_remove = $this->_arr_posts_all_posts_list_remove_edit_trash;
			return $this->post_row_actions_remove($arr_actions, $obj_post);
		}
		
		public function posts_all_posts_list_remove_edit_trash_view($arr_actions, $obj_post){
			$this->_arr_post_row_actions_remove = $this->_arr_posts_all_posts_list_remove_edit_trash_view;
			return $this->post_row_actions_remove($arr_actions, $obj_post);
		}
		
		public function pages_all_pages_list_remove_quick_edit($arr_actions, $obj_post){
			$this->_arr_post_row_actions_remove = $this->_arr_pages_all_pages_list_remove_quick_edit;
			return $this->post_row_actions_remove($arr_actions, $obj_post);
		}
		
		public function pages_all_pages_list_remove_edit_trash($arr_actions, $obj_post){
			$this->_arr_post_row_actions_remove = $this->_arr_pages_all_pages_list_remove_edit_trash;
			return $this->post_row_actions_remove($arr_actions, $obj_post);
		}
		
		public function pages_all_pages_list_remove_edit_trash_view($arr_actions, $obj_post){
			$this->_arr_post_row_actions_remove = $this->_arr_pages_all_pages_list_remove_edit_trash_view;
			return $this->post_row_actions_remove($arr_actions, $obj_post);
		}
		

		
		/*
		 * filter - used for both edit-post and edit-page
		 */
		public function posts_all_pages_all_remove_bulk_actions($arr_actions){
	
			global $current_screen;
			
			if ( isset($this->_arr_posts_all_pages_all_remove_bulk_actions) ){
				$str_post_type = $current_screen->post_type;
				if( in_array($str_post_type, $this->_arr_posts_all_pages_all_remove_bulk_actions) ){
					return $arr_actions;
				}
			}			
			unset( $arr_actions['trash'] );
			unset( $arr_actions['edit'] );
			
			return $arr_actions;
		}
		
		/*
		 * filter - remove specified image sizes from the media box for adding media into posts
		 */
		public function media_box_unset_default_sizes($arr_sizes = array()){
		
			if ( WP_ezMethods::array_pass($this->_arr_media_box_unset_default_sizes) ){
			
				foreach ( $this->_arr_media_box_unset_default_sizes as $str_unset_this ){
					unset( $arr_sizes['sizes'][$str_unset_this] );
				}
			}
			return $arr_sizes;
		}
		
		/*
		 * filter - remove specified image sizes from the media box for adding medin into posts
		 */
		public function media_box_default_settings(){
			if ( WP_ezMethods::array_pass($this->_arr_media_box_default_settings) ){
	
				foreach ( $this->_arr_media_box_default_settings as $str_key => $str_setting ){
					update_option( $str_key, $str_setting );
				}
			}
		}
		
		
		/*
		 * filter - Adds the current intermediate_image_sizes (or whatever is passed) to the Media Box select image size.
		 */
		public function media_box_default_sizes_add_custom( $arr_current_sizes){
		
			$arr_new_sizes = $arr_current_sizes;
			$arr_get_intermediate_image_sizes = get_intermediate_image_sizes();
			if ( WP_ezMethods::array_pass($arr_get_intermediate_image_sizes) ){

				$arr_new_sizes = array();
				foreach ( $arr_get_intermediate_image_sizes as $str_key => $str_value ){ 
					$arr_new_sizes[$str_value] = $str_value;
				}
				$arr_new_sizes = array_merge( $arr_new_sizes, $arr_current_sizes );
			}
			return $arr_new_sizes;
		}
		
		
		/**
		 * Unset (read: remove_action or remove_filter) a filter (which includes actions) from $GLOBALS['wp_filter'].
		 *
		 * Very helpful when you don't know the specific object.
		 */		
		public function globals_wp_filter_unset_action_or_filter(){
		
			$arr_args = $this->_arr_globals_wp_filter_unset_action_or_filter;

			// some loose validation
			if ( isset($arr_args['tag']) && isset($arr_args['function_to_remove']) && isset($arr_args['priority']) & isset($arr_args['object_name']) ) {

				// start with a specific priority
				if ( isset( $GLOBALS['wp_filter'][ $arr_args['tag'] ][ $arr_args['priority'] ] ) ){
					$arr_globals_wp_filter_priority = $GLOBALS['wp_filter'][ $arr_args['tag'] ][ $arr_args['priority'] ];	
					
					
					if ( is_array( $arr_globals_wp_filter_priority ) ){
						// loop through until you find the 'function_to_remove'
						foreach ( $arr_globals_wp_filter_priority as $str_tag => $arr_value){
						
							if ( WP_ezMethods::ends_with($str_tag, $arr_args['function_to_remove'])){
								// be safe and confirm that the 'object_name' matches.
								if ( ( get_class($arr_value['function'][0]) == $arr_args['object_name'] ) ) {
									// once we find the action / filter unset that b*tch :)
									unset ( $GLOBALS['wp_filter'][ $arr_args['tag'] ][ $arr_args['priority'] ][$str_tag] );
								}
							}	
						}
					}
				}
			}	
		}
	
	
		/**
		 * We sacked the regular Settings > Media page (so things can't be accidently changed).
		 * Now let's replace it with a "read-only" version (for fyi purposes). 
		 */
		 
		public function settings_media_add_submenu_page(){
		
			add_submenu_page('options-general.php', 'Media (View Only)', 'Media', 'install_plugins','clmd-media',array($this, 'add_submenu_page_settings_media_do'));
		}
		 
		/**
		 * A read-only reworking of the Settings > Media page. Also lists all current images (not just thumbnail, medium, large)
		 */
		public function add_submenu_page_settings_media_do(){

			$str_to_echo = '<div class="wrap">';
			$str_to_echo .= '<div id="icon-options-general" class="icon32"><br></div>';
			$str_to_echo .= '<h2>Media Settings</h2>';
			$str_to_echo .= '<p>Note: Media Setting are defined by the theme and can no longer be set directly via this page.<p>';
			$str_to_echo .= '<h3>Media Image Sizes as defined as WP Options.</h3>';

			$str_to_echo .= '<table class="form-table">';
			
			$obj_admin_methods = Class_WP_ezClasses_Images_Helper_Methods::ezc_get_instance();
			$arr_wp_image_sizes_ez = $obj_admin_methods->wp_image_sizes_ez();
			foreach ($arr_wp_image_sizes_ez as $str_key => $arr_size){
			
				$str_to_echo .= '<tr valign="top">';
				$str_to_echo .= '<th scope="row">' . $str_key . '</th>';
				$str_to_echo .= '<td>';
				$str_to_echo .= '<label for="thumbnail_size_w">Width - ';
				$str_to_echo .= $arr_size['width'] . '</label> <br>';
				$str_to_echo .= '<label for="thumbnail_size_h">Height - ';
				$str_to_echo .= $arr_size['height'] . '</label> <br >';
				if ( $str_key == 'thumbnail' ) {
					$str_to_echo .= '<label for="thumbnail_crop">' . (get_option('thumbnail_crop') ? 'True' : 'False') . ' (bool) ' . ' - Crop thumbnail to exact dimensions (normally thumbnails are proportional)</label>';
				}
				if ( isset($arr_size['crop']) ) {
					$str_to_echo .= 'Crop - ';
					$str_to_echo .= ( $arr_size['crop'] ) ? 'True (bool)' : 'False (bool)';
				}
				$str_to_echo .= '</td>';
				$str_to_echo .= '</tr>';
			
			}

			$str_to_echo .= '</table>';

			$str_to_echo .= '<h3>Uploading Files</h3>';
			$str_to_echo .= '<table class="form-table">';
			$str_to_echo .= '<tr>';
			$str_to_echo .= '<th scope="row" colspan="2" class="th-full">';
			$str_to_echo .= '<label for="uploads_use_yearmonth_folders">';
			$str_to_echo .= (get_option('uploads_use_yearmonth_folders') ? 'True' : 'False') . ' (bool) ' . ' - Organize my uploads into month- and year-based folders</label>';
			$str_to_echo .= '</th>';
			$str_to_echo .= '</tr>';
			$str_to_echo .= '</table>';
	
			echo $str_to_echo;
		}

		
	} // close: class
} // close: class_exists

?>