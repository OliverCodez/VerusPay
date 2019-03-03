CONFIG_MYSQL=$(expect -c "
set timeout 3
spawn mysql
expect \"mysql>\"
send \"SELECT user,authentication_string,plugin,host FROM mysql.user;\r\"
expect \"mysql>\"
send \"ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY '$rootpass';\r\"
expect \"mysql>\"
send \"CREATE DATABASE $wpdb DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;\r\"
expect \"mysql>\"
send \"GRANT ALL ON $wpdb.* TO '$wpuser'@'localhost' IDENTIFIED BY '$wppass';\r\"
expect \"mysql>\"
send \"FLUSH PRIVILEGES;\r\"
expect \"mysql>\"
send \"exit;\r\"
expect eof
")
SECURE_MYSQL=$(expect -c "
set timeout 3
spawn mysql_secure_installation
expect \"VALIDATE PASSWORD PLUGIN can be used to test\"
send \"n\r\"
expect \"Please set the password for root here.\"
send \"y\r\"
expect \"New password:\"
send \"$rootpass\r\"
expect \"Re-enter new password:\"
send \"$rootpass\r\"
expect \"Remove anonymous users?\"
send \"y\r\"
expect \"Disallow root login remotely?\"
send \"y\r\"
expect \"Remove test database and access to it?\"
send \"y\r\"
expect \"Reload privilege tables now?\"
send \"y\r\"
expect eof
")
echo "$SECURE_MYSQL"
echo "$CONFIG_MYSQL"