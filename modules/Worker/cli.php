<?php

// Job de envio de email
$app->get('/worker/envia-emails', 'Lidere\Modules\Worker\Controllers\Email:queue');

$app->get('/worker/gera-email-rel-montadoras', 'Lidere\Modules\Worker\Controllers\Email:retornaRelVisitasMontadorasNaoEnviados');