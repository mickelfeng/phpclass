安装homebrew
homebrew是mac下非常好用的包管理器，会自动安装相关的依赖包，将你从繁琐的软件依赖安装中解放出来。 安装homebrew也非常简单，只要在终端中输入:
1	ruby -e "$(curl -fsSL https://raw.github.com/Homebrew/homebrew/go/install)" homebrew的常用命令:
1	brew update #更新可安装包的最新信息，建议每次安装前都运行下
2	brew search pkg_name #搜索相关的包信息
3	brew install pkg_name #安装包 想了解更多地信息，请参看homebrew
安装nginx
安装
1	brew search nginx
2	brew install nginx 当前的最新版本是1.4.4。
配置
1	cd /usr/local/etc/nginx/
2	mkdir conf.d
3	vim nginx.conf
4	vim ./conf.d/default.conf nginx.conf内容,
01	worker_processes  1; 
02	 
03	error_log       /usr/local/var/log/nginx/error.log warn;
04	 
05	pid        /usr/local/var/run/nginx.pid;
06	 
07	events {
08	    worker_connections  256;
09	}
10	 
11	http {
12	    include       mime.types;
13	    default_type  application/octet-stream;
14	 
15	    log_format  main  '$remote_addr - $remote_user [$time_local] "$request" '
16	                      '$status $body_bytes_sent "$http_referer" '
17	                      '"$http_user_agent" "$http_x_forwarded_for"';
18	 
19	    access_log      /usr/local/var/log/nginx/access.log main;
20	    port_in_redirect off;
21	    sendfile        on;
22	    keepalive_timeout  65;
23	 
24	    include /usr/local/etc/nginx/conf.d/*.conf;
25	} default.conf文件内容,
01	server {
02	    listen       8080;
03	    server_name  localhost;
04	 
05	    root /Users/user_name/nginx_sites/; # 该项要修改为你准备存放相关网页的路径
06	 
07	    location / {
08	        index index.php;
09	        autoindex on;
10	    }  
11	 
12	    #proxy the php scripts to php-fpm 
13	    location ~ \.php$ {
14	        include /usr/local/etc/nginx/fastcgi.conf;
15	        fastcgi_intercept_errors on;
16	        fastcgi_pass   127.0.0.1:9000;
17	    }  
18	 
19	} 安装php-fpm
Mac OSX 10.9的系统自带了PHP、php-fpm，省去了安装php-fpm的麻烦。 这里需要简单地修改下php-fpm的配置，否则运行php-fpm会报错。
1	sudo cp /private/etc/php-fpm.conf.default /private/etc/php-fpm.conf
2	vim /private/etc/php-fpm.conf 修改php-fpm.conf文件中的error_log项，默认该项被注释掉，这里需要去注释并且修改为error_log = /usr/local/var/log/php-fpm.log。如果不修改该值，运行php-fpm的时候会提示log文件输出路径不存在的错误。
安装mysql
安装
1	brew install mysql 常用命令
1	mysql.server start #启动mysql服务
2	mysql.server stop #关闭mysql服务 配置 在终端运行mysql_secure_installation脚本，该脚本会一步步提示你设置一系列安全性相关的参数，包括：设置root密码，关闭匿名访问，不允许root用户远程访问，移除test数据库。当然运行该脚本前记得先启动mysql服务。
测试nginx服务
在之前nginx配置文件default.conf中设置的root项对应的文件夹下创建测试文件index.php:
1	<!-- ~/nginx_sites/index.php -->
2	<?php phpinfo(); ?> 启动nginx服务，sudo nginx； 修改配置文件，重启nginx服务，sudo nginx -s reload 启动php服务，sudo php-fpm； 在浏览器地址栏中输入localhost:8080，如果配置正确地话，应该能看到PHP相关信息的页面。 　
