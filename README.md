# WebSolarLog
PHP Charting for Inverters

Started as an fork off the 123Aurora project.
However it became an full rewrite.

Still many thanks for the developers of the 123Aurora project.

Made by: Marco Frijmann, Martin Diphoorn

## Continuation

This is a fork of the original project from sourceforge continued by me 
Andreas Fendt.

## Installation

This installation guide is written for debian and ubuntu platforms, but
directory's and packages should be similar.

### Preparation

Install the webserver, sqlite and php services with your package
manager:

    sudo apt-get install php5-common php5-cli php5-fpm nginx php5-cgi \
                         php5-svn php5-sqlite php5-mcrypt libjs-jquery \
                         php5-curl
                         
If you don't have git installed install it:

    sudo apt-get install git

### Configure nginx

Configure the nginx site. For example the default profile:

    /etc/nginx/sites-available/default
 
Add the include of the php.conf file, activate the listen statement, add
index.php to the index files and comment the `try_files` line:

    server {
        listen 80; ## listen for ipv4; this line is default and implied
        #listen [::]:80 default_server ipv6only=on; ## listen for ipv6
        
        root /usr/share/nginx/www;
        index index.php index.html index.htm;
        
        #Make site accessible from http://localhost/
        server_name localhost;
        
        location / {
            #First attempt to serve request as file, then
            #as directory, then fall back to displaying a 404.
            #try_files $uri $uri/ /index.html;
            #Uncomment to enable naxsi on this location
            #include /etc/nginx/naxsi.rules
        }
        location /doc/ {
            alias /usr/share/doc/;
            autoindex on;
            allow 127.0.0.1;
            allow ::1;
            deny all;
        }
        
        include php.conf;
    }

Create or edit the `php.conf` file inside the
`/etc/nginx/sites-available` directory: 

    fastcgi_intercept_errors on;
    # this will allow Nginx to intercept 4xx/5xx error codes
    # Nginx will only intercept if there are error page rules defined
    # -- This is better placed in the http {} block as a default
    # -- so that in the case of wordpress, you can turn it off specifically
    # -- in that virtual host's server block
     
    location ~ \.php {
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        # A handy function that became available in 0.7.31 that breaks down
        # The path information based on the provided regex expression
        # This is handy for requests such as file.php/some/paths/here/
        fastcgi_param PATH_INFO $fastcgi_path_info;
        fastcgi_param PATH_TRANSLATED $document_root$fastcgi_path_info;
        fastcgi_param QUERY_STRING $query_string;
        fastcgi_param REQUEST_METHOD $request_method;
        fastcgi_param CONTENT_TYPE $content_type;
        fastcgi_param CONTENT_LENGTH $content_length;
        fastcgi_param SCRIPT_NAME $fastcgi_script_name;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param REQUEST_URI $request_uri;
        fastcgi_param DOCUMENT_URI $document_uri;
        fastcgi_param DOCUMENT_ROOT $document_root;
        fastcgi_param SERVER_PROTOCOL $server_protocol;
        fastcgi_param GATEWAY_INTERFACE CGI/1.1;
        fastcgi_param SERVER_SOFTWARE nginx;
        fastcgi_param REMOTE_ADDR $remote_addr;
        fastcgi_param REMOTE_PORT $remote_port;
        fastcgi_param SERVER_ADDR $server_addr;
        fastcgi_param SERVER_PORT $server_port;
        fastcgi_param SERVER_NAME $server_name;
        fastcgi_pass unix:/var/run/php5-fpm.sock;
        #fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
    }

After that restart the services:

    sudo service php5-fpm restart 
    sudo service nginx restart

### Insert Project

After the configuration of nginx you can download this project and 
install it:

    $ cd /usr/share/nginx/www
    $ git clone https://github.com/HyP3r-/WebSolarLog.git
    $ mv WebSolarLog websolarlog
    $ chown -R www-data:www-data websolarlog
    
### Configure Service 

To record the performance of the inverter continuously a service must
be installed.

For a System V environment create a script inside the `/etc/init.d` 
directory (`websolarlog`):

    #!/bin/sh
    ### BEGIN INIT INFO
    # Provides:         websolarlog
    # Required-Start:   $local_fs $network
    # Required-Stop:    $local_fs $network
    # Default-Start:    2 3 4 5
    # Default-Stop:     0 1 6
    # Description:      WebSolarLog Server
    ### END INIT INFO
    
    USER="www-data"
    DIR="/usr/share/nginx/www/websolarlog/scripts"
    
    ###### WebSolarLog server start/stop script ######
    
    case "$1" in
        start)
            su $USER -c "${DIR}/wsl.sh start"
        ;;
        stop)
            su $USER -c "${DIR}/wsl.sh stop"
        ;;
        status)
            su $USER -c "${DIR}/wsl.sh status"
        ;;
        *)
            echo "Usage: {start|stop|restart|status}" >&2
            exit 1
        ;;
    esac
    exit 0
    
After that run the update script and start the service:

    update-rc.d websolarlog defaults
    service websolarlog start

And for a systemd environment create a websolarlog.service:

    [Unit]
    Description=WebSolarLog
    After=ngnix.service
    
    [Service]
    ExecStart=/usr/bin/php /usr/share/nginx/www/websolarlog/scripts/server.php
    Restart=always
    User=www-data
    Group=www-data
    
    [Install]
    WantedBy=multi-user.target
    
After that you can start the service:

    systemctl enable websolarlog.service
    systemctl start websolarlog

### Visit the Admin Webpage

Go to the Admin to configure WebSolarLog:

    http://ip_address_of_the_device/websolarlog/admin/
    
You should see a login page.
Now login with:
username: `admin`
password: `admin`

## Links

For more information, visit the WebSolarLog page

http://www.websolarlog.com

http://sourceforge.net/p/websolarlog/wiki/Home/

https://github.com/HyP3r-/WebSolarLog

