<?php

/**
 * The Sameday API functionality of the plugin.
 *
 * @link       https://github.com/mnestorov/smarty-sameday-lockers-locator
 * @since      1.0.0
 *
 * @package    Smarty_Sameday_Locator
 * @subpackage Smarty_Sameday_Locator/includes/classes
 * @author     Smarty Studio | Martin Nestorov
 */

class Smarty_Sameday_API {

    /**
     * Fetches the authentication token from the API.
     * 
     * @since      1.0.0
     * @throws Exception If the authentication fails.
     * @return string The fetched authentication token.
     */
    public static function get_auth_token() {
        $auth_data = get_option('smarty_sameday_auth_token');
    
        if (!empty($auth_data['token']) && !empty($auth_data['expire_at'])) {
            if (strtotime($auth_data['expire_at']) > time()) {
                return $auth_data['token'];
            }
        }
    
        $options = get_option('smarty_sameday_settings');
        $username = $options['smarty_sameday_field_username'] ?? '';
        $password = $options['smarty_sameday_field_password'] ?? '';
    
        $curl = curl_init('https://api.sameday.bg/api/authenticate');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query([
            'remember_me' => true
        ]));
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Accept: application/json',
            'Content-Type: application/x-www-form-urlencoded',
            'X-Auth-Username: ' . $username,
            'X-Auth-Password: ' . $password
        ));
    
        $response = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    
        _sll_write_logs("Auth response: $response");
    
        if (curl_errno($curl)) {
            throw new Exception('Auth CURL Error: ' . curl_error($curl));
        }
    
        $data = json_decode($response, true);
    
        if ($status !== 200 || empty($data['token'])) {
            throw new Exception('Auth failed: ' . $response);
        }
    
        update_option('smarty_sameday_auth_token', array(
            'token'     => $data['token'],
            'expire_at' => $data['expire_at'],
        ));
    
        return $data['token'];
    }    

    /**
     * Calls the Sameday API with the given parameters.
     * 
     * @since      1.0.0
     * @param string $param The API endpoint to call.
     * @param array $json_data The JSON data to send with the request.
     * @param bool $parse_response Whether to parse the response as JSON.
     * @return mixed The parsed JSON response or the raw response.
     */
    public static function call_sameday_api($param, $query_params = array(), $parse_response = true) {
        $endpoint = 'https://api.sameday.bg/';
        $url = rtrim($endpoint, '/') . '/' . ltrim($param, '/');
    
        if (!empty($query_params)) {
            $url .= '?' . http_build_query($query_params);
        }
    
        //_sll_write_logs('API Endpoint: ' . $url);
    
        // Get stored token or authenticate
        $token = self::get_auth_token();
    
        // Retrieve login credentials (required as headers)
        $options = get_option('smarty_sameday_settings');
        $username = $options['smarty_sameday_field_username'] ?? '';
        $password = $options['smarty_sameday_field_password'] ?? '';
    
        // Prepare headers
        $headers = array(
            'Accept: application/json',
            'Content-Type: application/x-www-form-urlencoded',
            'X-Auth-Username: ' . $username,
            'X-Auth-Password: ' . $password,
            'X-Auth-Token: ' . $token
        );
    
        // Initialize CURL
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    
        // Execute request
        $response_body = curl_exec($curl);
        $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        //_sll_write_logs('HTTP Status Code: ' . $http_status);
        //_sll_write_logs('Raw API Response: ' . $response_body);
    
        if (curl_errno($curl)) {
            throw new Exception('Curl Error: ' . curl_error($curl));
        }
    
        if (!$parse_response) {
            return $response_body;
        }
    
        $response = json_decode($response_body);
    
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('JSON Decode Error: ' . json_last_error_msg());
        }
    
        if (isset($response->error->message)) {
            throw new Exception('API Error: ' . $response->error->message);
        }
    
        return $response;
    }    

    /**
     * Fetches Sameday lockers from the API.
     * 
     * @since      1.0.0
     * @throws Exception If an error occurs during the API call.
     * @return array|null The fetched lockers or null if an error occurred.
     */
    public function fetch_sameday_lockers() {
        try {
            $options = get_option('smarty_sameday_settings');
            $country_code = $options['smarty_sameday_field_country_code'] ?? 'RO'; // Ensure this fetches the correct country code
            //_sll_write_logs('Country Code for API Call: ' . $country_code);

            $lockers = array();
            $page = 1;
            $has_more = true;

            while ($has_more) {
                // API call with country_id
                $response = self::call_sameday_api('api/client/lockers', array(
                    'countryCode' => $country_code,
                    'page'        => $page
                ));
                //_sll_write_logs('API Response: ' . print_r($response, true));
    
                if (empty($response->data)) {
                    break;
                }
    
                foreach ((array)$response->data as $locker) {
                    $full_address = sprintf(
                        '%s, %s, %s',
                        $locker->city ?? '',
                        $locker->postalCode ?? '',
                        $locker->address ?? ''
                    );

                    $lockers[] = array(
                        'locker_id'    => $locker->lockerId ?? 0,
                        'name'          => sanitize_text_field($locker->name),
                        'country'       => sanitize_text_field($locker->country ?? ''),
                        'city_name'     => sanitize_text_field($locker->city),
                        'post_code'     => sanitize_text_field($locker->postalCode ?? ''),
                        'address'       => sanitize_text_field($locker->address ?? ''),
                        'full_address'  => sanitize_text_field($full_address),
                        'updated_at'    => current_time('mysql'),
                    );
                }
    
                $page++;
                $has_more = $page <= ($response->pages ?? 1);
            }
            return $lockers;
        } catch (Exception $e) {
            //_sll_write_logs('API Fetch Error: ' . $e->getMessage());
            return null;
        }
    }     

    /**
     * Inserts or updates Sameday lockers in the database.
     * 
     * @since      1.0.0
     * @global wpdb $wpdb WordPress database object.
     * @return void
     */
    public function insert_sameday_lockers() {
        if (!current_user_can('manage_options')) {
            return;
        }

        global $wpdb;
        $locker_table = $wpdb->prefix . SAMEDAY_DB_PREFIX . SAMEDAY_LOCKER_TABLE;

        // Truncate the table before inserting new records
        $wpdb->query("TRUNCATE TABLE $locker_table");

        $all_lockers = self::fetch_sameday_lockers();

        if (!$all_lockers) {
            return;
        }

        foreach ($all_lockers as $locker) {
            $result = $wpdb->replace(
                $locker_table,
                array(
                    'locker_id'   => $locker['locker_id'],
                    'name'         => $locker['name'],
                    'country'      => $locker['country'],
                    'city_name'    => $locker['city_name'],
                    'post_code'    => $locker['post_code'],
                    'address'      => $locker['address'],
                    'full_address' => $locker['full_address'],
                    'updated_at'   => $locker['updated_at'],
                ),
                array('%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
            );
        
            //_sll_write_logs("Insert result for locker {$locker['locker_id']}: $result");
        
            if ($result === false) {
                _sll_write_logs('Database Error: ' . $wpdb->last_error);
            }
        }

        //_sll_write_logs('Lockers updated at: ' . current_time('mysql'));
    }
    
    /**
     * Fetches Sameday lockers from the database.
     * 
     * @since      1.0.0
     * @param string $country The country code to filter by.
     * @param string $city The city name to filter by.
     * @param string $post_code The postal code to filter by.
     * @global wpdb $wpdb WordPress database object.
     * @return array The fetched lockers.
     */
    public function query_sameday_lockers($country = '', $city = '', $post_code = '') {
        global $wpdb;
        $locker_table = $wpdb->prefix . SAMEDAY_DB_PREFIX . SAMEDAY_LOCKER_TABLE;
    
        // Build the query with dynamic parameters
        $query_params = array();
        $query = "SELECT * FROM {$locker_table} WHERE 1=1";
        
        if (!empty($city)) {
            $query .= " AND city_name = %s";
            $query_params[] = $city;
        }
        
        if (!empty($country)) {
            $query .= " AND country = %s";
            $query_params[] = $country;
        }
    
        if (!empty($post_code)) {
            $query .= " AND post_code = %s";
            $query_params[] = $post_code;
        }
    
        $query .= " ORDER BY address";
        
        if (!empty($query_params)) {
            $query = $wpdb->prepare($query, $query_params);
        }
    
        $results = $wpdb->get_results($query, 'ARRAY_A');
        return $results;
    }    
}
