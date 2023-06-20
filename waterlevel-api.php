<?php
/*
Plugin Name: Waterlevel API
Description: Receives and provides the current waterlevel of surfwelleaugsburg
*/

require_once(ABSPATH.'wp-admin/includes/file.php');

$upload_dir = wp_upload_dir();
$wl_dir = trailingslashit($upload_dir['basedir']).'waterlevel/'; 

add_action('rest_api_init', function () {
  global $wp_filesystem, $wl_dir;

  register_rest_route('api/v1', '/waterlevel', array(
    'methods' => 'POST',
    'callback' => function ($request) {
      global $wp_filesystem, $wl_dir;

      $date = date('Y-m-d', strtotime('now'));
      $time = date('H:i:s', strtotime('now'));
      $wl_filename = "waterlevel-$date.json";
      
      WP_Filesystem(); 
      if (!is_file($wl_dir.$wl_filename)) {
        if(!$wp_filesystem->is_dir($wl_dir) )
        {
          $wp_filesystem->mkdir($wl_dir); 
        }
        $wp_filesystem->put_contents($wl_dir.$wl_filename, '{}', 0644);
      }

      $wl_entries = json_decode($wp_filesystem->get_contents($wl_dir.$wl_filename), true);
      $wl_entry = json_decode($request->get_body(), true);
      $wl_entries[$time] = $wl_entry;
  
      $result = $wp_filesystem->put_contents($wl_dir.$wl_filename, json_encode($wl_entries), 0644);
      return array("status" => $result ? "ok" : "error");
    },
    'permission_callback' => function ($request) {
      //$options = get_option('waterlevel_api_plugin_options');
      $options = array('api_key' => "test");

      // Get the actual key from the request
      $actualApiKey = $request->get_header('KEY');

      // Verify the request key
      if ($options['api_key'] === $actualApiKey) {
        return true; // successful
      }

      // not allowed
      return false;
    }
  ));
  register_rest_route('api/v1', '/waterlevel', array(
    'methods' => 'GET',
    'callback' => function ($request) {
      global $wp_filesystem, $wl_dir;

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
      // always allowed
      return true; 
    }
  ));
});


/*
add_action( 'admin_menu', function() {
  add_options_page(
    'Waterlevel API Settings', 
    'Waterlevel API', 
    'manage_options', 
    'waterlevel-api', 
    function () {
      ?>
      <h2>Waterlevel API Settings</h2>
      <form action="options.php" method="post">
          <?php 
          settings_fields('waterlevel_api_plugin');
          do_settings_sections('waterlevel_api_plugin'); ?>
          <script>
          function generateRandomKey(){
              var dt = new Date().getTime();
              var uuid = 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx'.replace(/[x]/g, function(c) {
                  var r = (dt + Math.random()*16)%16 | 0;
                  dt = Math.floor(dt/16);
                  return (c=='x' ? r :(r&0x3|0x8)).toString(16);
              });
              document.querySelector("#waterlevel_api_plugin_setting_api_key").value = uuid;
          }
          </script>
          <input name="generateRandomKey" class="button button-secondary" type="button" onclick="generateRandomKey()" value="<?php esc_attr_e('Generate'); ?>" />
          <?php submit_button('Save'); ?>
      </form>
      <?php
  });
});

add_action('admin_init', function() {
  register_setting(
    'waterlevel_api_plugin', 
    'waterlevel_api_plugin_options', 
    array(
      "type" => "string",
      "description " => "API key for waterlevel api",
      "sanitize_callback" => function ($input) {
        return sprintf("%s", bin2hex($input['api_key']));
      },
      "show_in_rest" => true
    )
  );

  add_settings_section(
    'api_settings', 
    'API Settings', 
    function() {
      echo '<p>Here you can set all the options for using the Waterlevel API</p>';
    },
    'waterlevel_api_plugin'
  );

  add_settings_field(
    'waterlevel_api_plugin_setting_api_key', 
    'API Key', 
    function() {
      $options = get_option('waterlevel_api_plugin_options') ?? [];
      echo "<input id='waterlevel_api_plugin_setting_api_key' name='waterlevel_api_plugin_options[api_key]' type='text' value='" . esc_attr($options['api_key'] ?? "") . "' />";
    }, 
    'waterlevel_api_plugin', 
    'api_settings'
  );
});

*/