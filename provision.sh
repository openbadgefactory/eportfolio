##
# Shell provisioner for LAMP
# Tested on Ubuntu 12.04
##

# Detect environment
source /etc/os-release

# Set non-interactive mode
export DEBIAN_FRONTEND=noninteractive

# Update package mirrors and update base system
add-apt-repository ppa:ondrej/php
apt-get -qq update
apt-get -y -qq dist-upgrade

# Install packages
apt-get -y -qq install mysql-server mysql-client
apt-get -y -qq install apache2
apt-get -y -qq install php5.6 php5.6-mysql libapache2-mod-php5.6 php5.6-cli php5.6-xml php5.6-curl php5.6-gd php5.6-mbstring php5.6-mcrypt php5.6-xmlrpc php5.6-mcrypt
apt-get -y -qq install git tree curl htop

# Create database
if [ -z `mysql -uroot --skip-column-names --batch -e "SHOW DATABASES  LIKE 'project'"` ]
    then
        mysql -uroot -e "CREATE DATABASE project CHARACTER SET utf8 COLLATE utf8_general_ci"
        EMPTY_DB=true
fi

# Set up vhost
cat > /etc/apache2/sites-available/vagrant.conf <<'EOF'
<VirtualHost *:80>
	ServerAdmin webmaster@localhost

	DocumentRoot /vagrant/htdocs
	<Directory />
		Options FollowSymLinks
		AllowOverride All
	</Directory>
	<Directory /vagrant/htdocs/>
		Options Indexes FollowSymLinks
		AllowOverride All
		Order allow,deny
		allow from all
		Require all granted
	</Directory>
	ErrorLog ${APACHE_LOG_DIR}/error.log

	# Possible values include: debug, info, notice, warn, error, crit,
	# alert, emerg.
	LogLevel warn

	CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
EOF

# Clean other vhosts
rm /etc/apache2/sites-enabled/*

# Activate vagrant vhost
ln -s /etc/apache2/sites-available/vagrant.conf /etc/apache2/sites-enabled/000-vagrant.conf

# Change user and group for apache
sed -i '/APACHE_RUN_USER/d' /etc/apache2/envvars
sed -i '/APACHE_RUN_GROUP/d' /etc/apache2/envvars

cat >> /etc/apache2/envvars <<'EOF'

# Apache user and group
export APACHE_RUN_USER=vagrant
export APACHE_RUN_GROUP=vagrant
EOF

# Fix premissions
if [ -d /var/lock/apache2 ]
	then
		chown -R vagrant:vagrant /var/lock/apache2
fi

# Enable rewrites
a2enmod rewrite

# Clean up
apt-get clean

# Restart services
service apache2 restart

printf "\n\n"
echo "Provision complete!"
