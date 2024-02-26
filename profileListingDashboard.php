<?php
require_once 'prevent-direct-access.php';

if (!class_exists('profileListingDashboard')) {

	class profileListingDashboard extends profileListingCommonFeatures {

		private $allow_metabox_style_in_pages = array('post.php', 'post-new.php');

		public function __construct() {
			// Registering an action hook to initialize the registration of the custom post type 'Profile'.
			add_action('init', array($this, 'register_custom_post_type'));

			// Registering an action hook to initialize the registration of taxonomies 'Skills' and 'Education' associated with the custom post type 'Profile'.
			add_action('init', array($this, 'register_taxonomies'));

			add_action('add_meta_boxes', array($this, 'add_custom_fields_meta_box'));
			add_action('save_post', array($this, 'save_custom_fields_data'));

			add_action('admin_menu', array($this, 'add_profile_listing_menu_page'));
			add_action('admin_post_save_profile_listing_settings', array($this, 'save_profile_listing_settings'));

			add_action('admin_notices', array($this, 'admin_notice_profile_listing_page'));

			add_action('admin_enqueue_scripts', array($this, 'add_scripts_and_styling'));
		}

		public function register_custom_post_type() {
			$labels = array(
				'name' => _x('Profiles', 'post type general name', 'profile-listing-mdassignment'),
				'singular_name' => _x('Profile', 'post type singular name', 'profile-listing-mdassignment'),
				'menu_name' => _x('Profiles', 'admin menu', 'profile-listing-mdassignment'),
				'name_admin_bar' => _x('Profile', 'add new on admin bar', 'profile-listing-mdassignment'),
				'add_new' => _x('Add New', 'profile', 'profile-listing-mdassignment'),
				'add_new_item' => __('Add New Profile', 'profile-listing-mdassignment'),
				'new_item' => __('New Profile', 'profile-listing-mdassignment'),
				'edit_item' => __('Edit Profile', 'profile-listing-mdassignment'),
				'view_item' => __('View Profile', 'profile-listing-mdassignment'),
				'all_items' => __('All Profiles', 'profile-listing-mdassignment'),
				'search_items' => __('Search Profiles', 'profile-listing-mdassignment'),
				'parent_item_colon' => __('Parent Profiles:', 'profile-listing-mdassignment'),
				'not_found' => __('No profiles found.', 'profile-listing-mdassignment'),
				'not_found_in_trash' => __('No profiles found in Trash.', 'profile-listing-mdassignment')
			);

			$args = array(
				'labels' => $labels,
				'public' => true,
				'publicly_queryable' => true,
				'show_ui' => true,
				'show_in_menu' => true,
				'query_var' => true,
				'rewrite' => array('slug' => 'profile'),
				'capability_type' => 'post',
				'has_archive' => true,
				'hierarchical' => false,
				'menu_position' => 20,
				'supports' => array('title', 'editor', 'thumbnail', 'excerpt')
			);

			register_post_type('profile', $args);
		}

		public function register_taxonomies() {
			// Registering 'Skills' taxonomy
			$labels = array(
				'name' => _x('Skills', 'taxonomy general name', 'profile-listing-mdassignment'),
				'singular_name' => _x('Skill', 'taxonomy singular name', 'profile-listing-mdassignment'),
				'search_items' => __('Search Skills', 'profile-listing-mdassignment'),
				'all_items' => __('All Skills', 'profile-listing-mdassignment'),
				'parent_item' => __('Parent Skill', 'profile-listing-mdassignment'),
				'parent_item_colon' => __('Parent Skill:', 'profile-listing-mdassignment'),
				'edit_item' => __('Edit Skill', 'profile-listing-mdassignment'),
				'update_item' => __('Update Skill', 'profile-listing-mdassignment'),
				'add_new_item' => __('Add New Skill', 'profile-listing-mdassignment'),
				'new_item_name' => __('New Skill Name', 'profile-listing-mdassignment'),
				'menu_name' => __('Skills', 'profile-listing-mdassignment'),
			);

			$args = array(
				'hierarchical' => false,
				'labels' => $labels,
				'show_ui' => true,
				'show_admin_column' => true,
				'query_var' => true,
				'rewrite' => array('slug' => 'skills'),
			);

			register_taxonomy('skills', 'profile', $args);

			// Registering 'Education' taxonomy
			$labels = array(
				'name' => _x('Education', 'taxonomy general name', 'profile-listing-mdassignment'),
				'singular_name' => _x('Education', 'taxonomy singular name', 'profile-listing-mdassignment'),
				'search_items' => __('Search Education', 'profile-listing-mdassignment'),
				'all_items' => __('All Education', 'profile-listing-mdassignment'),
				'parent_item' => __('Parent Education', 'profile-listing-mdassignment'),
				'parent_item_colon' => __('Parent Education:', 'profile-listing-mdassignment'),
				'edit_item' => __('Edit Education', 'profile-listing-mdassignment'),
				'update_item' => __('Update Education', 'profile-listing-mdassignment'),
				'add_new_item' => __('Add New Education', 'profile-listing-mdassignment'),
				'new_item_name' => __('New Education Name', 'profile-listing-mdassignment'),
				'menu_name' => __('Education', 'profile-listing-mdassignment'),
			);

			$args = array(
				'hierarchical' => false,
				'labels' => $labels,
				'show_ui' => true,
				'show_admin_column' => true,
				'query_var' => true,
				'rewrite' => array('slug' => 'education'),
			);

			register_taxonomy('education', 'profile', $args);
		}

		public function add_custom_fields_meta_box() {
			add_meta_box(
					'profile_custom_fields_meta_box',
					__('Profile Custom Fields', 'profile-listing-mdassignment'),
					array($this, 'render_custom_fields_meta_box'),
					'profile',
					'normal',
					'high'
			);
		}

		public function render_custom_fields_meta_box($post) {
			// Retrieve the existing values of the custom fields
			$dob = sanitize_text_field(get_post_meta($post->ID, 'dob', true));
			$hobbies = sanitize_text_field(get_post_meta($post->ID, 'hobbies', true));
			$interests = sanitize_text_field(get_post_meta($post->ID, 'interests', true));
			$years_of_experience = absint(get_post_meta($post->ID, 'years_of_experience', true));
			$ratings = absint(get_post_meta($post->ID, 'ratings', true));
			$jobs_completed = absint(get_post_meta($post->ID, 'jobs_completed', true));

			// Output the HTML for the custom fields
			?>
			<div class="profile-custom-fields-container">
				<div>
					<label for="dob" class="required-field"><?php _e('Date of Birth (Day-Month-Year):', 'profile-listing-mdassignment'); ?></label>
					<input type="date" id="dob" name="dob" max="<?php echo date('Y-m-d', strtotime('-18 years')); ?>" min="<?php echo date('Y-m-d', strtotime('-50 years')); ?>" value="<?php echo esc_attr($dob); ?>" pattern="\d{1,2}-\d{1,2}-\d{4}" placeholder="dd-mm-yyyy" required />
				</div>
				<div>
					<label for="hobbies"><?php _e('Hobbies:', 'profile-listing-mdassignment'); ?></label>
					<input type="text" id="hobbies" name="hobbies" value="<?php echo esc_attr($hobbies); ?>" placeholder="<?php _e('Comma separated values', 'profile-listing-mdassignment'); ?>" />
				</div>
				<div>
					<label for="interests"><?php _e('Interests:', 'profile-listing-mdassignment'); ?></label>
					<input type="text" id="interests" name="interests" value="<?php echo esc_attr($interests); ?>" placeholder="<?php _e('Comma separated values', 'profile-listing-mdassignment'); ?>" />
				</div>
				<div>
					<label for="years_of_experience" class="required-field"><?php _e('Years of Experience:', 'profile-listing-mdassignment'); ?></label>
					<input type="number" id="years_of_experience" name="years_of_experience" max="50" min="0" value="<?php echo esc_attr($years_of_experience); ?>" required />
				</div>
				<div>
					<label for="ratings" class="required-field"><?php _e('Ratings:', 'profile-listing-mdassignment'); ?></label>
					<input type="number" id="ratings" name="ratings" max="5" min="1" value="<?php echo esc_attr($ratings); ?>" required />
				</div>
				<div>
					<label for="jobs_completed" class="required-field"><?php _e('Number of Jobs Completed:', 'profile-listing-mdassignment'); ?></label>
					<input type="number" id="jobs_completed" name="jobs_completed" max="5000" min="0" value="<?php echo esc_attr($jobs_completed); ?>" required />
				</div>
			</div>
			<?php
		}

		public function save_custom_fields_data($post_id) {
			if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
				return;
			}

			if (!current_user_can('edit_post', $post_id)) {
				return;
			}

			// Save custom field data
			$fields = array('dob', 'hobbies', 'interests', 'years_of_experience', 'ratings', 'jobs_completed');
			foreach ($fields as $field) {
				if (isset($_POST[$field])) {
					if ($field === 'hobbies' || $field === 'interests') {
						$value = sanitize_text_field($_POST[$field]);
						$_POST[$field] = $this->cleanCommaSeparatedValues($value);
					}
					update_post_meta($post_id, $field, sanitize_text_field($_POST[$field]));
				}
			}
		}

		public function add_profile_listing_menu_page() {
			add_menu_page(
					__('Profile Listings', 'profile-listing-mdassignment'), // Page title
					__('Profile Listings', 'profile-listing-mdassignment'), // Menu title
					'manage_options', // Capability required to access the page
					'profile-listing-mdassignment', // Menu slug
					array($this, 'render_profile_listing_menu_page'), // Callback function to display the page content
					'dashicons-admin-generic', // Icon (dashicon) for the menu item 
					21 // Position of the menu item in the admin menu
			);
		}

		public function render_profile_listing_menu_page() {
			// Get all existing pages
			$pages = get_pages();

			// Get existing profile listing page
			$current_page_id = absint(get_option('profile_listing_page', 0));

			// Display admin page content
			?>
			<div class="profile-listing-menu-page">
				<h1><?php esc_html_e('Profile Listings', 'profile-listing-mdassignment'); ?></h1>
				<p><?php esc_html_e('Select a page to set as the Profile Listing Page:', 'profile_listing_page'); ?></p>

				<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
					<input type="hidden" name="action" value="save_profile_listing_settings">
					<?php wp_nonce_field('profile_listing_page_nonce', 'profile_listing_page_nonce'); ?>
					<label for="profile_listing_page"><?php esc_html_e('Select Page:', 'profile_listing_page'); ?></label>
					<select name="profile_listing_page" id="profile_listing_page" required>
						<option value="" selected disabled>--Select a page for profile listing--</option>
						<?php foreach ($pages as $page) : ?>
							<option value="<?php echo esc_attr($page->ID); ?>" <?php selected($page->ID, $current_page_id); ?>><?php echo esc_html($page->post_title); ?></option>
						<?php endforeach; ?>
					</select>
					<br>
					<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php esc_attr_e('Save', 'profile-listing-mdassignment'); ?>">
				</form>
			</div>
			<?php
		}

		public function save_profile_listing_settings() {
			// Check nonce and user capabilities
			if (
					isset($_POST['profile_listing_page_nonce']) && wp_verify_nonce(sanitize_text_field($_POST['profile_listing_page_nonce']), 'profile_listing_page_nonce') && current_user_can('manage_options')
			) {
				// Sanitize and save the selected page ID
				$page_id = isset($_POST['profile_listing_page']) ? intval($_POST['profile_listing_page']) : 0;
				update_option('profile_listing_page', $page_id);

				// Redirect back to the admin page
				wp_redirect(admin_url('admin.php?page=profile-listing-mdassignment'));
				exit;
			}
		}

		public function admin_notice_profile_listing_page() {
			$current_page_id = absint(get_option('profile_listing_page', 0));
			if ($current_page_id === 0) {

				// Check if the current page slug is the plugin settings page slug
				$current_page_slug = isset($_GET['page']) ? sanitize_text_field($_GET['page']) : '';
				if ($current_page_slug === 'profile-listing-mdassignment') {
					return; // Do not display the notice on the plugin settings page
				}
				?>
				<div class="notice notice-warning is-dismissible">
					<p><?php esc_html_e('Thank you for installing &ldquo;Profile Listing &ndash; Multidots assignment&rdquo; plugin. Please select the profile listing page to complete the setup. Thank you.', 'profile-listing-mdassignment'); ?></p>
					<p><a href="<?php echo esc_url(admin_url('admin.php?page=profile-listing-mdassignment')); ?>" class="button"><?php esc_html_e('Go to Settings', 'profile-listing-mdassignment'); ?></a></p>
				</div>
				<?php
			}
		}

		public function add_scripts_and_styling($hook) {
			global $post;
			// Adding style for the post type 'Profile' custom fields meta box.
			if (in_array($hook, $this->allow_metabox_style_in_pages) && $post->post_type === 'profile') {
				wp_enqueue_style('profile-listing-custom-fields-meta-box', plugins_url('assets/css/profile-listing-custom-fields-meta-box.css', __FILE__), array(), '1.0.0', 'all');
			} elseif (strpos($hook, 'profile-listing-mdassignment') !== false) {
				wp_enqueue_style('profile-listing-menu-page', plugins_url('assets/css/profile-listing-menu-page.css', __FILE__), array(), '1.0.0', 'all');
			}
		}

	}

	new profileListingDashboard();
}