<?php
/*
Plugin Name: Waterlevel API
*/

require_once(ABSPATH.'wp-admin/includes/file.php');
global $wp_filesystem;

$upload_dir = wp_upload_dir();
$wl_dir = trailingslashit($upload_dir['basedir']).'waterlevel/'; 

add_action('rest_api_init', function () {
  register_rest_route('api/v1', '/waterlevel', array(
    'methods' => 'POST',
    'callback' => function ($request) {
      $date = date('Y-m-d', strtotime('now'));
      $time = date('H:i:s', strtotime('now'));
      $wl_filename = "waterlevel-$date.json";

      WP_Filesystem(); 
      if (!is_file($wl_dir.$wl_filename)) {
        if(!$wp_filesystem->is_dir($dir) )
        {
          $wp_filesystem->mkdir($dir); 
        }
        $wp_filesystem->put_contents($wl_dir.$wl_filename, '{}', 0644);
      }

      $wl_entries = json_decode($wp_filesystem->get_contents($wl_dir.$wl_filename), true);
      $wl_entry = json_decode($request->get_body(), true);
      $wl_entries[$time] = $wl_entry;
  
      $result = $wp_filesystem->put_contents($wl_dir.$wl_filename, json_encode($wlEntries), 0644);
      return array("status" => $result ? "ok" : "error")
    },
    'permission_callback' => function () {
        return true; 
    }
  ));
  register_rest_route('api/v1', '/waterlevel', array(
    'methods' => 'GET',
    'callback' => function ($request) {
      $date = date('Y-m-d', strtotime('now'));
      $time = date('H:i:s', strtotime('now'));
      $wl_filename = "waterlevel-$date.json";

      WP_Filesystem(); 
      if (!is_file($wl_dir.$wl_filename)) {
        return '{}';
      }

      $wl_entries = json_decode($wp_filesystem->get_contents($wl_dir.$wl_filename), true);
      return $wl_entries;
    },
    'permission_callback' => function () {
        return true; 
    }
  ));
});
