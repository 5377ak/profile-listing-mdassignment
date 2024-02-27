<?php

require_once 'prevent-direct-access.php';

if (!class_exists('profileListingCommonFeatures')) {

	class profileListingCommonFeatures {

		protected function cleanCommaSeparatedValues($input) {

			// Explode the string into an array using comma as delimiter
			$values = explode(',', $input);

			// Trim each value in the array and remove empty values
			$values = array_map('trim', $values);
			$values = array_filter($values); // Remove empty values
			// Implode the array to reconstruct the string with cleaned values
			$input = implode(', ', $values);

			// Now return $input that contains the cleaned string with values separated by commas
			return $input;
		}

		// Calculate age based on date of birth
		protected function calculateAge($dateOfBirth) {
			$dob = new DateTime($dateOfBirth);
			$now = new DateTime();
			$age = $now->diff($dob)->y;
			return $age;
		}

		protected function generateStarRating($rating) {
			// Convert the rating to an integer
			$rating = intval($rating);

			// Validate the rating (should be between 0 and 5)
			if ($rating < 0 || $rating > 5) {
				$rating = 1; // Set rating to 1 if invalid rating supplied
			}

			$html = '';
			$selected_star = plugins_url('assets/img/selected-star.png', __FILE__);
			$unselected_star = plugins_url('assets/img/unselected-star.png', __FILE__);

			// Add selected stars
			for ($i = 0; $i < $rating; $i++) {
				$html .= '<img src="' . $selected_star . '" alt="Selected Star" width="20" height="20">';
			}

			// Add unselected stars for the remaining stars
			for ($i = $rating; $i < 5; $i++) {
				$html .= '<img src="' . $unselected_star . '" alt="Unselected Star" width="20" height="20">';
			}

			return $html;
		}

		// Method to get the total count of published profile posts
		protected function get_total_published_profile_posts_count($keyword, $apply_advance, $age, $rating, $jobs_completed, $years_experience, $skills, $education) {
			$args = array(
				'post_type' => 'profile',
				'post_status' => 'publish', // Only count published posts
				'posts_per_page' => -1, // Retrieve all published posts
				'fields' => 'ids', // Retrieve only ids
			);

			if (!empty($keyword) || $apply_advance == 1) {
				$final_arguments = $this->advanced_search_arguments($args, $keyword, $apply_advance, $age, $rating, $jobs_completed, $years_experience, $skills, $education);
			} else {
				$final_arguments = $args;
			}

			$profiles = get_posts($final_arguments);

			return count($profiles);
		}

		// Method to get the profile by specific sorting and or searching
		protected function apply_sorting_and_searching_for_profiles($column_index, $sorting_direction, $length, $offset, $keyword, $apply_advance, $age, $rating, $jobs_completed, $years_experience, $skills, $education) {
			switch ($column_index) {
				case 0:
					// Sort by date for the index column
					$orderby = 'date';
					break;
				case 1:
					// Sort by post title for the profile name column
					$orderby = 'title';
					break;
				case 2:
					// Sort by custom field for the Age column (Date of Birth)
					$orderby = 'meta_value';
					$meta_key = 'dob';
					break;
				case 3:
					// Sort by custom field for the third column (Years of Experience)
					$orderby = 'meta_value_num';
					$meta_key = 'years_of_experience';
					break;
				case 4:
					// Sort by custom field for the fourth column (Jobs Completed)
					$orderby = 'meta_value_num';
					$meta_key = 'jobs_completed';
					break;
				case 5:
					// Sort by custom field for the fifth column (Ratings)
					$orderby = 'meta_value_num';
					$meta_key = 'ratings';
					break;
				default:
					// Default sorting column and order
					$orderby = 'date';
			}

			// Set the sorting direction
			$order = ($sorting_direction === 'desc') ? 'DESC' : 'ASC';

			// Prepare arguments for get_posts
			$args = array(
				'post_type' => 'profile',
				'posts_per_page' => $length,
				'offset' => $offset,
				'post_status' => 'publish',
				'orderby' => $orderby,
				'order' => $order,
			);

			// If sorting by custom field, include meta query
			if (isset($meta_key)) {
				$args['meta_key'] = $meta_key;
			}

			$final_arguments = $this->advanced_search_arguments($args, $keyword, $apply_advance, $age, $rating, $jobs_completed, $years_experience, $skills, $education);

			return $final_arguments;
		}

		private function advanced_search_arguments($args, $keyword, $apply_advance, $age, $rating, $jobs_completed, $years_experience, $skills, $education) {

			// Keyword search
			if (!empty($keyword)) {
				$args['s'] = $keyword;
			}

			// Advanced search criteria
			if ($apply_advance == 1) {
				// Meta query array to hold all meta queries
				$meta_query = array('relation' => 'AND');

				// Age criteria
				if (!empty($age)) {
					$meta_query[] = array(
						'key' => 'dob',
						'value' => date('Y-m-d', strtotime("-$age years")),
						'compare' => '<=',
						'type' => 'DATE',
					);
				}

				// Rating criteria
				if (!empty($rating)) {
					$meta_query[] = array(
						'key' => 'ratings',
						'value' => $rating,
						'compare' => '>=',
						'type' => 'NUMERIC',
					);
				}

				// Jobs completed criteria
				if (!empty($jobs_completed)) {
					$meta_query[] = array(
						'key' => 'jobs_completed',
						'value' => $jobs_completed,
						'compare' => '>=',
						'type' => 'NUMERIC',
					);
				}

				// Years of experience criteria
				if (!empty($years_experience)) {
					$meta_query[] = array(
						'key' => 'years_of_experience',
						'value' => $years_experience,
						'compare' => '>=',
						'type' => 'NUMERIC',
					);
				}

				// Check whether skills or eduction are provided in search, then add tax_query to the $args
				if (!empty($skills) || !empty($education)) {
					$args['tax_query'] = array();
				}

				// Skills criteria
				if (!empty($skills)) {
					$args['tax_query'][] = array(
						'taxonomy' => 'skills',
						'field' => 'term_id',
						'terms' => $skills,
						'include_children' => false
					);
				}

				// Education criteria
				if (!empty($education)) {
					$args['tax_query'][] = array(
						'taxonomy' => 'education',
						'field' => 'term_id',
						'terms' => $education,
						'include_children' => false
					);
				}

				// Add the meta queries to the main query arguments
				$args['meta_query'] = $meta_query;
			}

			return $args;
		}

	}

}