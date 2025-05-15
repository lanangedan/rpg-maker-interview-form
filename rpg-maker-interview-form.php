/*
Plugin Name: RPG Maker Interview Form
Plugin URI: https://github.com/lananedan/rpg-maker-interview-form
Description: Plugin RPG Form dengan chapter & whitelist
Version: 2.0.1
Author: Lanang
Author URI: https://tombolstart.com
License: GPL2

GitHub Plugin URI: https://github.com/lanangedan/rpg-maker-interview-form
GitHub Branch: main
*/

if (!defined('ABSPATH')) exit;

// 🔁 Load CPT & metabox editor (admin only)
if (is_admin()) {
  require_once plugin_dir_path(__FILE__) . 'admin/ui-editor.php';
}

// 🎮 Load public game renderer
require_once plugin_dir_path(__FILE__) . 'public/game-render.php';

// Hapus meta box yang tidak perlu
add_action('add_meta_boxes_rpg_chapter', function() {
  remove_meta_box('monsterinsights_dashboard_widget', 'rpg_chapter', 'normal');
  remove_meta_box('aiosp', 'rpg_chapter', 'normal');
  remove_meta_box('litespeed_meta_box', 'rpg_chapter', 'normal');
  remove_meta_box('rank_math_metabox', 'rpg_chapter', 'normal');
  remove_meta_box('wpseo_meta', 'rpg_chapter', 'normal'); // Yoast
  remove_meta_box('slugdiv', 'rpg_chapter', 'normal'); // Slug
}, 99);

//Konsistensi Branding
add_filter('gettext', function($translated, $text, $domain) {
  global $pagenow;

  // Di halaman daftar Interview Chapters
  if ($pagenow === 'edit.php' && isset($_GET['post_type']) && $_GET['post_type'] === 'rpg_chapter') {
    if ($text === 'Add Post') return 'Add Chapter';
  }

  // Di halaman editor Interview Chapter
  if ($pagenow === 'post-new.php' && isset($_GET['post_type']) && $_GET['post_type'] === 'rpg_chapter') {
    if ($text === 'Add title') return 'Judul Chapter';
    if ($text === 'Add new %s') return 'Add new Chapter';
    if ($text === 'Edit %s') return 'Edit Chapter';
  }

  return $translated;
}, 10, 3);

add_filter('rest_endpoints', function($endpoints) {
  return $endpoints; // just to force REST initialization
});

add_filter('register_post_type_args', function($args, $post_type) {
  if ($post_type === 'rpg_chapter') {
    $args['show_in_rest'] = true;
    $args['rest_base'] = 'rpg_chapter';
    $args['rest_controller_class'] = 'WP_REST_Posts_Controller';
  }
  return $args;
}, 10, 2);

add_action('init', function () {
  register_post_type('test_cpt', [
    'label' => 'Test CPT',
    'public' => true,
    'show_in_rest' => true,
    'rest_base' => 'test_cpt',
    'supports' => ['title']
  ]);
});

add_action('rest_api_init', function () {
  $types = get_post_types([], 'objects');
  foreach ($types as $type => $obj) {
    if ($obj->show_in_rest) {
      error_log("REST API enabled for: " . $type . " | route: " . $obj->rest_base);
    }
  }
});


