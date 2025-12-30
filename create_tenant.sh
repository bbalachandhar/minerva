#!/bin/bash

# --- Tenant Creation Script ---
# Automates the creation of a new tenant for the Minerva application.
#
# This script incorporates all fixes and best practices identified
# during the setup of the mce.beebasoft.com tenant.
#
# USAGE:
# sudo ./create_tenant.sh <tenant_id> <domain_name> <db_name> <db_user> "<db_password>" <admin_email>
#
# EXAMPLE:
# sudo ./create_tenant.sh amacedu amacedu.beebasoft.com amacedu_db db_user "complex-pass-!@#$" admin@example.com

# --- Configuration Variables ---
# Update these if your application or tenants directories change locations
TENANTS_BASE_DIR="/var/www/tenants"
APP_DOC_ROOT="/var/www/minerva"
APACHE_CONF_DIR="/etc/httpd/conf.d"

# --- --- --- SCRIPT LOGIC --- --- ---

# 1. Argument Validation
if [ "$#" -ne 6 ]; then
    echo "ERROR: Incorrect number of arguments."
    echo "Usage: sudo $0 <tenant_id> <domain_name> <db_name> <db_user> \"<db_password>\" <admin_email>"
    exit 1
fi

if [ "$(id -u)" -ne 0 ]; then
  echo "This script must be run as root. Please use sudo." >&2
  exit 1
fi

# 2. Assign Arguments to Variables
TENANT_ID="$1"
DOMAIN_NAME="$2"
DB_NAME="$3"
DB_USER="$4"
DB_PASS="$5"
ADMIN_EMAIL="$6"
TENANT_DIR="$TENANTS_BASE_DIR/$TENANT_ID"

echo "--- Starting new tenant creation for '$TENANT_ID' ---"

# 3. Check if tenant already exists
if [ -d "$TENANT_DIR" ]; then
    echo "Error: Tenant directory '$TENANT_DIR' already exists. Aborting."
    exit 1
fi

# 4. Create Directory Structure
echo "--> Creating directory structure at $TENANT_DIR..."
mkdir -p "$TENANT_DIR"/{config,cache,logs,sessions,uploads,temp,backup}
if [ $? -ne 0 ]; then echo "Failed to create directories. Aborting."; exit 1; fi

# 5. Set Permissions and Ownership
echo "--> Setting permissions and ownership..."
chown -R ec2-user:apache "$TENANT_DIR"
find "$TENANT_DIR/" -type d -exec chmod 755 {} \;
find "$TENANT_DIR/" -type f -exec chmod 644 {} \;
if [ $? -ne 0 ]; then echo "Failed to set basic permissions. Aborting."; exit 1; fi

# 5b. Grant group write permissions to specific writable directories (THIS IS THE FIX)
echo "--> Granting group write permissions to writable directories..."
chmod 775 "$TENANT_DIR"/{cache,logs,sessions,uploads,temp,backup}
if [ $? -ne 0 ]; then echo "Failed to set 775 permissions. Aborting."; exit 1; fi


# 6. Set SELinux Contexts
echo "--> Setting SELinux contexts..."
# Note: semanage fcontext -a will warn if context already exists, which is harmless for re-runs.
semanage fcontext -a -t httpd_sys_rw_content_t "$TENANT_DIR/cache(/.*)?"
semanage fcontext -a -t httpd_sys_rw_content_t "$TENANT_DIR/logs(/.*)?"
semanage fcontext -a -t httpd_sys_rw_content_t "$TENANT_DIR/sessions(/.*)?"
semanage fcontext -a -t httpd_sys_rw_content_t "$TENANT_DIR/uploads(/.*)?"
semanage fcontext -a -t httpd_sys_rw_content_t "$TENANT_DIR/temp(/.*)?"
semanage fcontext -a -t httpd_sys_rw_content_t "$TENANT_DIR/backup(/.*)?"
restorecon -R -v "$TENANT_DIR"
if [ $? -ne 0 ]; then echo "Failed to set SELinux contexts. Aborting."; exit 1; fi

# 7. Create Tenant Config Files from Templates
echo "--> Creating tenant-specific config files..."

# Create config.php
cat > "$TENANT_DIR/config/config.php" << EOF
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

\$config['base_url'] = 'https://$DOMAIN_NAME';
\$config['encryption_key'] = 'bf8a3b1e9d5c4f2a0e8d6c7b5a4f3e2d';
\$config['cookie_domain'] = '.$DOMAIN_NAME';
\$config['cookie_path'] = '/';
\$config['cookie_secure'] = TRUE;

# Tenant-specific session cookie name to prevent conflicts
\$config['sess_cookie_name'] = '${TENANT_ID}_ci_session';
 
# Paths that use TENANT_ROOT
\$config['log_path'] = TENANT_ROOT . 'logs/';
\$config['cache_path'] = TENANT_ROOT . 'cache/';
\$config['sess_save_path'] = TENANT_ROOT . 'sessions/';

# Default session driver and expiration
\$config['sess_driver'] = 'files';
\$config['sess_expiration'] = 7200;

# Enable full logging by default for debugging
\$config['log_threshold'] = 4;
EOF

# Create database.php
cat > "$TENANT_DIR/config/database.php" << EOF
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$active_group = 'default';
$query_builder = TRUE;

$db['default'] = array(
    'dsn'          => '',
    'hostname'     => 'localhost',
    'username'     => '$DB_USER',
    'password'     => '$DB_PASS',
    'database'     => '$DB_NAME',
    'dbdriver'     => 'mysqli',
    'dbprefix'     => '',
    'pconnect'     => FALSE,
    'db_debug'     => (ENVIRONMENT !== 'production'),
    'cache_on'     => FALSE,
    'cachedir'     => '',
    'char_set'     => 'utf8',
    'dbcollat'     => 'utf8_general_ci',
    'swap_pre'     => '',
    'encrypt'      => FALSE,
    'compress'     => FALSE,
    'stricton'     => FALSE,
    'failover'     => array(),
    'save_queries' => TRUE
);
EOF

# 8. Create Apache Virtual Host for Port 80
echo "--> Creating Apache virtual host for $DOMAIN_NAME..."
APACHE_CONF_FILE="$APACHE_CONF_DIR/$TENANT_ID.conf"
cat > "$APACHE_CONF_FILE" << EOF
<VirtualHost *:80>
    ServerName $DOMAIN_NAME
    DocumentRoot $APP_DOC_ROOT
    ErrorLog /var/log/httpd/${TENANT_ID}_error.log
    CustomLog /var/log/httpd/${TENANT_ID}_access.log combined
    # Certbot will add its own rewrite rule for HTTP to HTTPS
</VirtualHost>
EOF

# 9. Obtain SSL Certificate with Certbot
echo "--> Obtaining SSL certificate using Certbot..."
certbot --apache --non-interactive --agree-tos --email "$ADMIN_EMAIL" -d "$DOMAIN_NAME"
if [ $? -ne 0 ]; then echo "Certbot failed. Aborting. Please check certbot logs."; exit 1; fi

# 10. Add crucial <Directory /var/www/minerva> and Alias blocks to SSL Virtual Host (THIS IS THE FIX)
echo "--> Adding crucial <Directory $APP_DOC_ROOT> and Alias blocks to SSL configuration..."
SSL_CONF_FILE="$APACHE_CONF_DIR/$(basename "$APACHE_CONF_FILE" .conf)-le-ssl.conf"

# --- Content for the full insertion ---
FULL_APACHE_BLOCK="
    # Main application directory settings
    <Directory $APP_DOC_ROOT>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    # Alias for tenant-specific uploads
    Alias /uploads $TENANT_DIR/uploads

    <Directory $TENANT_DIR/uploads>
        Options Indexes FollowSymLinks
        AllowOverride None
        Require all granted
    </Directory>
"

if [ -f "$SSL_CONF_FILE" ]; then
    # Create a temporary file with the content to insert
    echo "$FULL_APACHE_BLOCK" > /tmp/full_apache_block_temp.conf
    
    # Insert this content after the DocumentRoot line in the SSL config
    sed -i "/DocumentRoot/r /tmp/full_apache_block_temp.conf" "$SSL_CONF_FILE"
    if [ $? -ne 0 ]; then echo "Warning: Failed to add blocks to SSL config. You may need to add it manually."; fi
    
    # Clean up the temporary file
    rm /tmp/full_apache_block_temp.conf
else
    echo "Warning: SSL config file not found at $SSL_CONF_FILE. Could not add blocks."
fi

# 11. Restart Apache
echo "--> Restarting Apache to apply all changes..."
systemctl restart httpd
if [ $? -ne 0 ]; then echo "Failed to restart Apache. Please restart it manually."; exit 1; fi

echo ""
echo "--- Tenant '$TENANT_ID' created successfully! ---"
echo "DNS for https://$DOMAIN_NAME should be pointed to this server's IP address."
echo "Also ensure you have created the database '$DB_NAME' and granted access to '$DB_USER'."