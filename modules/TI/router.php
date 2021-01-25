<?php

/** Tickets **/

$app->map('/ti/tickets/', 'Lidere\Modules\TI\Controllers\Tickets:index')->via('GET', 'POST');
$app->map('/ti/tickets/form(/:id)', 'Lidere\Modules\TI\Controllers\Tickets:form')->via('GET', 'POST');
$app->post('/ti/tickets/delete', 'Lidere\Modules\TI\Controllers\Tickets:delete');
$app->post('/ti/tickets/add/called', 'Lidere\Modules\TI\Controllers\Tickets:addCalled');
$app->post('/ti/tickets/update/called', 'Lidere\Modules\TI\Controllers\Tickets:updateCalled');
$app->post('/ti/tickets/delete/called', 'Lidere\Modules\TI\Controllers\Tickets:deleteCalled');
$app->map('/ti/tickets/print/:id', 'Lidere\Modules\TI\Controllers\Tickets:print')->via('GET', 'POST');
$app->post('/ti/tickets/add/expense', 'Lidere\Modules\TI\Controllers\Tickets:addExpense');
$app->post('/ti/tickets/delete/expense', 'Lidere\Modules\TI\Controllers\Tickets:deleteExpense');
$app->post('/ti/tickets/add/file', 'Lidere\Modules\TI\Controllers\Tickets:addFile');
$app->post('/ti/tickets/delete/file', 'Lidere\Modules\TI\Controllers\Tickets:deleteFile');
$app->get('/ti/tickets/download(/:link)', 'Lidere\Modules\TI\Controllers\Tickets:download');
$app->post('/ti/tickets/get/users', 'Lidere\Modules\TI\Controllers\Tickets:getUsers');

/** Download **/

$app->get('/ti/Download', 'Lidere\Modules\TI\Controllers\Download:download');

/** Companies **/

$app->map('/ti/companies/', 'Lidere\Modules\TI\Controllers\Companies:index')->via('GET','POST');
$app->map('/ti/companies/form(/:id)', 'Lidere\Modules\TI\Controllers\Companies:form')->via('GET','POST');
$app->post('/ti/companies/delete', 'Lidere\Modules\TI\Controllers\Companies:delete');
$app->post('/ti/companies/project/create',  'Lidere\Modules\TI\Controllers\Companies:projectCreate');
$app->post('/ti/companies/project/edit', 'Lidere\Modules\TI\Controllers\Companies:projectEdit');
$app->get('/ti/companies/project/delete/:company/:id', 'Lidere\Modules\TI\Controllers\Companies:projectDelete');
$app->post('/ti/companies/hour/edit', 'Lidere\Modules\TI\Controllers\Companies:hourEdit');
$app->get('/ti/companies/ranking', 'Lidere\Modules\TI\Controllers\Companies:ranking');


/** Types **/ 

$app->map('/ti/type-expenses/', 'Lidere\Modules\TI\Controllers\Expenses:index')->via('GET','POST');
$app->map('/ti/type-expenses/form(/:id)', 'Lidere\Modules\TI\Controllers\Expenses:form')->via('GET','POST');
$app->post('/ti/type-expenses/delete', 'Lidere\Modules\TI\Controllers\Expenses:delete');
$app->get('/ti/type-expenses/pagina/:pagina', 'Lidere\Modules\TI\Controllers\Expenses:pagina');

$app->map('/ti/type-hours/', 'Lidere\Modules\TI\Controllers\Hours:index')->via('GET','POST');
$app->map('/ti/type-hours/form(/:id)', 'Lidere\Modules\TI\Controllers\Hours:form')->via('GET','POST');
$app->post('/ti/type-hours/delete', 'Lidere\Modules\TI\Controllers\Hours:delete');


/** Panel */

$app->get('/ti/panel', 'Lidere\Modules\TI\Controllers\Panels:index');

/* Attendance */


$app->get('/ti/attendance/', 'Lidere\Modules\TI\Controllers\Attendances:index');