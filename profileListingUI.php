<?php

require_once 'prevent-direct-access.php';

if (!class_exists('profileListingUI')) {

	class profileListingUI extends profileListingCommonFeatures {

		public function __construct() {
			add_action('wp_enqueue_scripts', array($this, 'profile_listing_page_styles_scripts'));
			add_filter('the_content', array($this, 'replace_content_template'));
			add_action('rest_api_init', array($this, 'register_custom_rest_endpoint'));
		}

		public function profile_listing_page_styles_scripts() {
			global $post;
			$current_page_id = absint($post->ID);
			$profile_listing_page_id = absint(get_option('profile_listing_page', 0));
			if ($current_page_id === $profile_listing_page_id) {
				// Enqueue select2 multiselect style and script
				wp_enqueue_style('multiselect-select2', plugins_url('assets/third-party-library/select2/select2.min.css', __FILE__), array(), '4.1.0', 'all');
				wp_enqueue_script('multiselect-select2', plugins_url('assets/third-party-library/select2/select2.min.js', __FILE__), array('jquery'), '4.1.0', true);

				// Enqueue datatables style and script
				wp_enqueue_style('datatables', plugins_url('assets/third-party-library/datatables/datatables.min.css', __FILE__), array(), '2.0.0', 'all');
				wp_enqueue_script('datatables', plugins_url('assets/third-party-library/datatables/datatables.min.js', __FILE__), array('jquery'), '2.0.0', true);

				// Enqueue plugin's custom style and script
				wp_enqueue_style('profile-listing-ui', plugins_url('assets/css/profile-listing-ui.css', __FILE__), array(), '1.0.0', 'all');
				wp_enqueue_script('profile-listing-ui', plugins_url('assets/js/profile-listing-ui.js', __FILE__), array('jquery'), '1.0.0', true);

				// Getting the profile listing endpoint URL
				$profile_listing_endpoint_url = rest_url('custom/v1/profiles');

				// Localize the script and provide the profile listing API endpoint URL and nonce
				wp_localize_script('profile-listing-ui', 'profile_listing_data', array(
					'rest_endpoint_url' => $profile_listing_endpoint_url,
					'nonce' => wp_create_nonce('profiles-listing')
				));
			}
		}

		public function replace_content_template($content) {
			global $post;
			$current_page_id = absint($post->ID);
			$profile_listing_page_id = absint(get_option('profile_listing_page', 0));
			if ($current_page_id === $profile_listing_page_id) {
				// Loading the content from the profile listing template
				ob_start();
				include 'templates/plugin-listing.php';
				$custom_content = ob_get_clean();
				// Append the custom fields data to the existing content
				return $custom_content;
			} elseif (is_singular('profile')) {
				$content .= $this->get_custom_fields_table_html($current_page_id);
			}
			// Return the original content if not profile listing page or profile singular page
			return $content;
		}

		private function get_custom_fields_table_html($post_id) {
			$skills = get_the_terms($post_id, 'skills');
			if ($skills && !is_wp_error($skills)) {
				$skills_list = [];
				foreach ($skills as $skill) {
					$skills_list[] = $skill->name;
				}
				$profile_skills = implode(', ', $skills_list);
			}

			$education = get_the_terms($post_id, 'education');
			if ($education && !is_wp_error($education)) {
				$education_list = [];
				foreach ($education as $edu) {
					$education_list[] = $edu->name;
				}
				$profile_education = implode(', ', $education_list);
			}
			return '<table class="custom-fields-table">
                <tr>
                    <th>Age</th>
                    <td>' . $this->calculateAge(sanitize_text_field(get_post_meta($post_id, 'dob', true))) . ' ' . __('years', 'profile-listing-mdassignment') . '</td>
                </tr>
                <tr>
                    <th>Hobbies</th>
                    <td>' . sanitize_text_field(get_post_meta($post_id, 'hobbies', true)) . '</td>
                </tr>
                <tr>
                    <th>Interests</th>
                    <td>' . sanitize_text_field(get_post_meta($post_id, 'interests', true)) . '</td>
                </tr>
                <tr>
                    <th>Years of Experience</th>
                    <td>' . absint(get_post_meta($post_id, 'years_of_experience', true)) . '</td>
                </tr>
                <tr>
                    <th>No. of Jobs Completed</th>
                    <td>' . absint(get_post_meta($post_id, 'jobs_completed', true)) . '</td>
                </tr>
                <tr>
                    <th>Ratings</th>
                    <td>' . $this->generateStarRating((get_post_meta($post_id, 'ratings', true))) . '</td>
                </tr>
                <tr>
                    <th>Skills</th>
                    <td>' . $profile_skills . '</td>
                </tr>
                <tr>
                    <th>Education</th>
                    <td>' . $profile_education . '</td>
                </tr>
            </table>';
		}

		public function register_custom_rest_endpoint() {
			register_rest_route('custom/v1', '/profiles', array(
				'methods' => 'GET',
				'callback' => array($this, 'get_profiles_data'),
				'permission_callback' => array($this, 'check_permission_callback')
			));
		}

		public function check_permission_callback($request) {
			// Return false if nonce is not valid
			if (wp_verify_nonce($request['nonce'], 'profiles-listing')) {
				return true;
			} else {
				return false;
			}
		}

		public function get_profiles_data($request) {
			// Extracting relevent information from DataTables payload
			$column_index = absint($request['order'][0]['column']);
			$sorting_direction = sanitize_text_field($request['order'][0]['dir']);
			$draw = absint($request['draw']);
			$length = absint($request['length']);
			$offset = absint($request['start']);
			$keyword = sanitize_text_field($request['keyword']);
			$skills = array_map('intval', $request['skills']);
			$education = array_map('intval', $request['education']);
			$age = absint($request['age']);
			$rating = absint($request['rating']);
			$jobs_completed = absint($request['jobs_completed']);
			$years_experience = absint($request['years_experience']);
			$apply_advance = absint($request['apply_advance']);
			$args = $this->apply_sorting_and_searching_for_profiles($column_index, $sorting_direction, $length, $offset, $keyword, $apply_advance, $age, $rating, $jobs_completed, $years_experience, $skills, $education);

			$profiles = get_posts($args);

			$data = array();

			// Record number to send to datatables
			$records_found = 0;

			foreach ($profiles as $key => $profile) {
				// Retrieve custom fields
				$profile_name = '<a href="' . get_the_permalink($profile->ID) . '" target="_blank" rel="noopener">' . $profile->post_title . '</a> &nearr;';
				$date_of_birth = sanitize_text_field(get_post_meta($profile->ID, 'dob', true));
				$years_of_experience = absint(get_post_meta($profile->ID, 'years_of_experience', true));
				$jobs_completed = absint(get_post_meta($profile->ID, 'jobs_completed', true));
				$ratings = absint(get_post_meta($profile->ID, 'ratings', true));

				// Calculate age from date of birth
				$age = $this->calculateAge($date_of_birth);

				// Generate ratings html (stars instead of numbers)
				$ratings_html = $this->generateStarRating($ratings);

				// Prepare data for the table
				$data[] = array(
					'no' => ++$records_found,
					'profile_name' => $profile_name,
					'age' => $age,
					'years_of_experience' => $years_of_experience,
					'jobs_completed' => $jobs_completed,
					'ratings' => $ratings_html
				);
			}

			// Getting the total number of publised profiles
			$total_posts_exists = $this->get_total_published_profile_posts_count($keyword, $apply_advance, $age, $rating, $jobs_completed, $years_experience, $skills, $education);

			$result = array(
				"draw" => $draw,
				"recordsTotal" => $total_posts_exists,
				"recordsFiltered" => $total_posts_exists,
				"data" => $data
			);

			return rest_ensure_response($result);
		}

	}

	new profileListingUI();
}