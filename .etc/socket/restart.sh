#!/bin/sh
#
# Mata processo do socket para que o supervisor reinicie
DATE=$(date)
PID=$(cat /tmp/supervisord.pid)
echo "[$DATE] - Derrubando WebSocket para liberar o servidor..."
#PROC=$(ps -C php | awk 'NR==2 { print $1 }')
#/bin/kill -9 $PROC
kill -s 2 $PID
supervisord -c /var/www/portal-default/.etc/socket/supervisor.conf
echo "[$DATE] - WebSocket reiniciado"
