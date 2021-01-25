##
# This file is part of the Lidere Sistemas (http://lideresistemas.com.br)
#
# Copyright (c) 2018  Lidere Sistemas (http://lideresistemas.com.br)
#
# For the full copyright and license information, please view
# the file license.txt that was distributed with this source code.
#/
##
# Script para inciar o processo de Queue em background.
#
# @package Core
# @subpackage Jobs
# @category Worker
# @author Ramon Barros
# @copyright 2018 Lidere Sistemas
#/
#!/bin/sh
# filename: src/Jobs/jobs.sh

# Diretório do servidor /var/www/html
WWW=/var/www/html

# Usuário do apache
USERAPACHE= #sudo -u www-data

# Diretório do projeto
PROJECT=portal-default

# Ambiente dev ou prod
ENV=dev

# Número de processo que deve ter
PROCESSORS=1;
x=0

while [ "$x" -lt "$PROCESSORS" ];
do
        PROCESS_COUNT=`pgrep -f queue.php | wc -l`
        if [ $PROCESS_COUNT -ge $PROCESSORS ]; then
                exit 0
        fi
        x=`expr $x + 1`
        DIR=$(pwd)
        $USERAPACHE php -f $WWW/$PROJECT/src/Jobs/queue.php env="$ENV" >> $WWW/$PROJECT/storage/logs/queue.log &
done
exit 0
