<?php

/**
 * Plugin Name: Profile Listing &ndash; Multidots assignment
 * Plugin URI: https://github.com/5377ak/profile-listing-mdassignment
 * Description: This plugin creates a custom post type &ldquo;Profile&rdquo; with custom taxonomies &ldquo;Skills&rdquo; &amp; &ldquo;Education&rdquo;.
 * Version: 1.0.0
 * Author: Akash Sharma
 * Author URI: https://#
 * License: GPL v3 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: profile-listing-mdassignment
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4.3
 */
// Requiring prevent-direct-access.php file to prevent the direct access of the plugin php files.
require_once 'prevent-direct-access.php';

// Requiring class profileListingCommonFeatures().
/* This class contains commonly used method that are shared
 *  among different other classes of this plugin.
 */
require_once 'profileListingCommonFeatures.php';

// Requiring class profileListingDashboard().
/* This class contains methods to add 'Profiles' as a custom post type
 *  with a custom fields meta box, along with taxonomies 'Skills' and 'Education'.
 */
require_once 'profileListingDashboard.php';

// Requiring class profileListingUI().
// This class contains methods to show profile listings UI on frontend.
/* Also contains the methods to show the single post page UI
 *  for the custom post type profile.
 */
require_once 'profileListingUI.php';
