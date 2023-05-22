#!/bin/sh
BASE_PATH=/var/www/html
echo "[start listening backlog at `date +%Y-%m-%d\ %T`] " >> $BASE_PATH/storage/logs/backlog.log
sh $BASE_PATH/storage/app/backlogs/*.sh

while inotifywait -r -e close_write -e create "$BASE_PATH/storage/app/backlogs/"
do
  sh $BASE_PATH/storage/app/backlogs/*.sh
done
