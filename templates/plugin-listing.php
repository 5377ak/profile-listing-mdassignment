<?php
if (!defined('ABSPATH')):
    require_once '../prevent-direct-access.php';
endif;

$unselected_start_image_url = plugins_url('assets/img/unselected-star.png', __DIR__);
$skills = get_terms(array(
    'taxonomy' => 'skills', // Taxonomy slug to retrieve terms from
    'hide_empty' => false, // Getting non attached terms as well
        ));
$education = get_terms(array(
    'taxonomy' => 'education', // Taxonomy slug to retrieve terms from
    'hide_empty' => false, // Getting non attached terms as well
        ));
?>
<div class="profile-listing-container">
    <form class="ajax-form" id="profile-listing-search-form" method="post">
        <div class="form-border">
            <!-- Search Bar -->
            <div class="row position-relative">
                <label for="keyword"><?php esc_html_e('Keyword:', 'profile-listing-mdassignment'); ?></label>
                <input type="text" id="keyword" name="keyword" maxlength="70" minlength="3" class="full-width">
            </div>

            <div class="row buttons-align-to-left-right">
                <!-- Advanced Search Toggle Button -->
                <button type="button" id="toggleAdvancedSearch"><?php esc_html_e('Advanced Search', 'profile-listing-mdassignment'); ?></button>
                <button type="submit" class="search-button" id="search-profiles"><?php esc_html_e('Click To Search', 'profile-listing-mdassignment'); ?>&excl;</button>
            </div>

            <!-- Advanced Search Fields (Initially Hidden) -->
            <div id="advancedSearchFields" style="display: none;">
                <div class="row">
                    <div class="half-width">
                        <label for="skills"><?php esc_html_e('Skills:', 'profile-listing-mdassignment'); ?></label>
                        <select id="skills" name="skills[]" class="select2" multiple="multiple">
                            <option value="" disabled><?php esc_html_e('Select Skill', 'profile-listing-mdassignment'); ?></option>
<?php
// Loop through each Skill terms
if (!empty($skills) && !is_wp_error($skills)) {
    foreach ($skills as $term) {
        echo '<option value = "' . $term->term_id . '">' . $term->name . '</option>';
    }
}
?>
                        </select>
                    </div>
                    <div class="half-width">
                        <label for="education"><?php esc_html_e('Education:', 'profile-listing-mdassignment'); ?></label>
                        <select id="education" name="education[]" class="select2" multiple="multiple">
                            <option value="" disabled><?php esc_html_e('Select Education', 'profile-listing-mdassignment'); ?></option>
<?php
// Loop through each Eduation terms
if (!empty($education) && !is_wp_error($education)) {
    foreach ($education as $term) {
        echo '<option value = "' . $term->term_id . '">' . $term->name . '</option>';
    }
}
?>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="half-width">
                        <label for="age"><?php esc_html_e('Age (minimum):', 'profile-listing-mdassignment'); ?> <span id="ageValue">18</span> <?php esc_html_e('years', 'profile-listing-mdassignment'); ?></label>
                        <input type="range" id="age" name="age" min="18" max="50" value="18">
                    </div>
                    <div class="half-width">
                        <label for="selectedRating"><?php esc_html_e('Rating (minimum):', 'profile-listing-mdassignment'); ?></label>
                        <div class="star-rating" id="rating">
<?php
for ($i = 1; $i < 6; $i++) {
    echo '<img src="' . esc_url($unselected_start_image_url) . '" alt="' . esc_attr__('Unselected Star', 'profile-listing-mdassignment') . '" class="star selected" data-value="' . esc_attr($i) . '">';
}
?>
                        </div>
                        <input type="number" id="selectedRating" name="rating" value="5" class="hidden">
                    </div>
                </div>

                <div class="row">
                    <div class="half-width">
                        <label for="jobs_completed"><?php esc_html_e('No: of jobs completed (minimum):', 'profile-listing-mdassignment'); ?></label>
                        <input type="number" id="jobs_completed" max="5000" min="0" name="jobs_completed">
                    </div>
                    <div class="half-width">
                        <label for="years_experience"><?php esc_html_e('Years of experience (minimum):', 'profile-listing-mdassignment'); ?></label>
                        <input type="number" id="years_experience" max="50" min="0" name="years_experience">
                    </div>
                </div>
            </div>
        </div>
        <input type="hidden" id="apply_advance" name="apply_advance" value="0">
    </form>

    <!-- Table to display profile listings -->
    <table id="profile-listings" class="display" style="width:100%">
        <thead>
            <tr>
                <th>No:</th>
                <th>Profile Name</th>
                <th>Age</th>
                <th>Years of experience</th>
                <th>No: of jobs completed</th>
                <th>Ratings</th>
            </tr>
        </thead>
        <tbody>
            <!-- Table body will be populated dynamically -->
        </tbody>
    </table>
</div>
