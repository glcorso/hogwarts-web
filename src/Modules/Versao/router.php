<?php

$app->get('/auxiliares/versao', 'Lidere\Modules\Versao\Controllers\Versao:index');
$app->get('/auxiliares/versao/pagina/:pagina', 'Lidere\Modules\Versao\Controllers\Versao:pagina');
