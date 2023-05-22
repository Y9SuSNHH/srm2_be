### Laravel-Swoole
download https://github.com/swooletw/laravel-swoole

### Dependent library updates
```
composer install
```

### Laravel initial settings
rename file .env.example to .env
```
php artisan migrate
php artisan db:seed --class=UserSeeder
```

### run
```
php artisan swoole:http start &
php artisan swoole:http restart &
php artisan swoole:http stop
```

---------
use supervisor run laravel queue

```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
directory=/path-to-project
command=php artisan queue:work --sleep=3 --tries=3 --daemon
autostart=true
autorestart=true
numprocs=3
redirect_stderr=true
stdout_logfile=/path-to-project/storage/logs/worker.log
```

---------
##backlog service
create a linux service that handles the backlog in the background

*/etc/backlog_sh_dir*
```
/var/www/html/storage/app/backlogs
```

*/usr/local/bin/backlog*
```shell
#!/bin/sh
BASE_PATH=`cat /etc/backlog_sh_dir`
while true
do
  echo "[start listening backlog at `date +%Y-%m-%d\ %T`] " >> $BASE_PATH/time.log

  inotifywait -m -e close_write -e create $BASE_PATH/. | while read path action file
  do
    sh $BASE_PATH/*.sh
  done
done
```
**sudo chmod +x /usr/local/bin/backlog**

*/etc/systemd/system/backlog.service*

```ini
[Unit]
Description=Backlog sh Script
After=network.target

[Service]
ExecStart=/usr/local/bin/backlog
User=
Group=

[Install]
WantedBy=default.target
```

**sudo systemctl daemon-reload**

**sudo systemctl enable backlog.service**

**sudo systemctl start backlog.service**

**sudo systemctl status backlog.service**

**sudo journalctl -u backlog**

