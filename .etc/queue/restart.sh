##
# This file is part of the Lidere Sistemas (http://lideresistemas.com.br)
#
# Copyright (c) 2018  Lidere Sistemas (http://lideresistemas.com.br)
#
# For the full copyright and license information, please view
# the file license.txt that was distributed with this source code.
#/
##
# Script para reiniciar o processo de Queue em background.
#
# @package Core
# @subpackage Jobs
# @category Worker
# @author Ramon Barros
# @copyright 2018 Lidere Sistemas
#/
#!/bin/sh
#
# Mata processo do queue para que o supervisor reinicie
DATE=$(date)
PID=$(cat /tmp/supervisord.pid)
echo "[$DATE] - Derrubando Queue para liberar o servidor..."
#PROC=$(ps -C php | awk 'NR==2 { print $1 }')
#/bin/kill -9 $PROC
kill -s 2 $PID
supervisord -c /var/www/portal-default/src/Jobs/supervisor.conf
echo "[$DATE] - Queue reiniciado"
