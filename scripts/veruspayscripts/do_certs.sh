CERTBOT_NO=$(expect -c "
set timeout 10
spawn certbot --apache -d $domain
expect \"Enter email address (used for urgent renewal and security notices)\"
send \"$email\r\"
expect \"Please read the Terms of Service\"
send \"A\r\"
expect \"Would you be willing to share your email address with the Electronic Frontier\"
send \"N\r\"
expect \"Select the appropriate number\"
send \"2\r\"
expect eof
")
CERTBOT_WWW=$(expect -c "
set timeout 10
spawn certbot --apache -d $domain -d $subdomain$domain
expect \"Enter email address (used for urgent renewal and security notices)\"
send \"$email\r\"
expect \"Please read the Terms of Service\"
send \"A\r\"
expect \"Would you be willing to share your email address with the Electronic Frontier\"
send \"N\r\"
expect \"Select the appropriate number\"
send \"2\r\"
expect eof
")
if [[ $subdomain == "www." ]]
then
    echo "$CERTBOT_WWW"
else 
    echo "$CERTBOT_NO"
fi