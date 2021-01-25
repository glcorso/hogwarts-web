<?php
// Não mexer aqui
// Adicione os crons em modules/Worker/cli.php

/**
 * Executa as tarefas agendadas do cron
 */
$app->get('/schedule', 'Lidere\Modules\Worker\Controllers\Tasks:index');

/**
 * Aqui você define as tarefas para execução
 */
$app->get('/worker/env', 'Lidere\Modules\Worker\Controllers\Env:index');
