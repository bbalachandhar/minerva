<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/*
 * Auto-detect base URL so this config works on local dev and all prod servers.
 * The API lives at {root}/api/ — base_url should point to {root}/ (parent).
 * e.g. http://192.168.1.4/minerva/api/ → base_url = http://192.168.1.4/minerva/
 *      https://mce.beebasoft.com/api/  → base_url = https://mce.beebasoft.com/
 */
if (isset($_SERVER['HTTP_HOST'])) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $host     = $_SERVER['HTTP_HOST'];
    // SCRIPT_NAME is e.g. /minerva/api/index.php → parent is /minerva/
    $api_dir    = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\'); // /minerva/api
    $parent_dir = rtrim(dirname($api_dir), '/\\');                 // /minerva
    if ($parent_dir === '.' || $parent_dir === '') {
        $parent_dir = '';
    }
    $config['base_url'] = $protocol . $host . $parent_dir . '/';
} else {
    $config['base_url'] = 'http://localhost/minerva/';
}

$config['index_page']    = '';
$config['uri_protocol']  = 'REQUEST_URI';

$is_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443);
$config['url_suffix']    = '';
$config['language']      = 'english';
$config['charset']       = 'UTF-8';
$config['enable_hooks']  = FALSE;
$config['subclass_prefix']     = 'MY_';
$config['composer_autoload']   = FALSE;
$config['permitted_uri_chars'] = 'a-z 0-9~%.:_\-';
$config['allow_get_array']     = TRUE;
$config['enable_query_strings'] = FALSE;
$config['controller_trigger']  = 'c';
$config['function_trigger']    = 'm';
$config['directory_trigger']   = 'd';
$config['log_threshold']       = 0;
$config['log_path']            = '';
$config['log_file_extension']  = '';
$config['log_file_permissions'] = 0644;
$config['log_date_format']     = 'Y-m-d H:i:s';
$config['error_views_path']    = '';
$config['cache_path']          = '';
$config['cache_query_string']  = FALSE;
$config['encryption_key']      = '';
$config['sess_driver']         = 'files';
$config['sess_cookie_name']    = 'ci_session';
$config['sess_samesite']       = $is_https ? 'None' : 'Lax';
$config['sess_expiration']     = 7200;
$config['sess_save_path']      = APPPATH . 'sessions';
$config['sess_match_ip']       = FALSE;
$config['sess_time_to_update'] = 300;
$config['sess_regenerate_destroy'] = FALSE;
$config['cookie_prefix']       = '';
$config['cookie_domain']       = '';
$config['cookie_path']         = '/';
$config['cookie_secure']       = $is_https;
$config['cookie_httponly']     = FALSE;
$config['cookie_samesite']     = $is_https ? 'None' : 'Lax';
$config['standardize_newlines'] = FALSE;
$config['global_xss_filtering'] = FALSE;
$config['csrf_protection']     = FALSE;
$config['csrf_token_name']     = 'school_csrf_name';
$config['csrf_cookie_name']    = 'school_cookie_name';
$config['csrf_expire']         = 7200;
$config['csrf_regenerate']     = TRUE;
$config['csrf_exclude_uris']   = array();
$config['compress_output']     = FALSE;
$config['time_reference']      = 'local';
$config['rewrite_short_tags']  = FALSE;
$config['proxy_ips']           = '';
