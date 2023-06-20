<?php
/*
Plugin Name: Push API Integration
Description: Integration of Push API for receiving and displaying push messages
*/

// Schritte 1 und 2: Daten speichern und verarbeiten
add_action('rest_api_init', function () {
  register_rest_route('push/v1', '/message', array(
    'methods' => 'POST',
    'callback' => 'receive_push_message',
    'permission_callback' => function ($request) {
      $apiKey = '<REPLACE_ME>';

      // Get the actual key from the request
      $actualApiKey = $request->get_header('KEY');

      // Verify the request key
      if ($apiKey === $actualApiKey) {
        return true; // successful
      }

      // not allowed
      return false;
    }
  ));
});

function receive_push_message($request)
{
  global $wpdb; // Zugriff auf die WordPress-Datenbankklasse

  $message = $request->get_body(); // Den empfangenen Nachrichtentext erhalten

  // Hier kannst du den Nachrichtentext weiterverarbeiten
  // Speichere den Wert zusammen mit dem Zeitstempel in der Datenbank oder führe andere Aktionen aus

  $table_name = $wpdb->prefix . 'pegel_ow'; // Name der benutzerdefinierten Tabelle
  $data = array(
    'timestamp' => current_time('mysql'), // Aktuelles Zeitstempel
    'value' => $message // Wert aus der Nachricht
  );

  $wpdb->insert($table_name, $data); // Daten in die Tabelle einfügen

  return 'Push message received: ' . $message;
}

// Schritt 3: Tabellarische Darstellung
add_shortcode('push_integration_table', 'display_push_integration_table');

function display_push_integration_table()
{
  global $wpdb; // Zugriff auf die WordPress-Datenbankklasse

  $table_name = $wpdb->prefix . 'pegel_ow'; // Name der benutzerdefinierten Tabelle

  // Abfrage zum Abrufen der gespeicherten Werte mit Zeitstempeln (neueste 10 Einträge)
  $query = "SELECT timestamp, value FROM $table_name ORDER BY timestamp DESC LIMIT 10";

  $results = $wpdb->get_results($query, ARRAY_A);

  // Generieren der HTML-Tabelle
  $table_html = '<table>';
  $table_html .= '<thead><tr><th>Zeit</th><th>Swell</th></tr></thead>';
  $table_html .= '<tbody>';
  foreach ($results as $result) {
    $table_html .= '<tr><td>' . $result['timestamp'] . '</td><td>' . $result['value'] . '</td></tr>';
  }
  $table_html .= '</tbody>';
  $table_html .= '</table>';

  // Gib den HTML-Code für die Tabelle zurück
  return $table_html;
}
