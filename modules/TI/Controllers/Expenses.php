<?php
/**
 * This file is part of the Lidere Sistemas (http://Lideresistemas.com.br)
 *
 * Copyright (c) 2017  Lidere Sistemas (http://Lideresistemas.com.br)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */
namespace Lidere\Modules\TI\Controllers;

use lib\Core;
use Lidere\Controllers\Controller;

class Expenses extends Controller 
{
    public $url = 'ti/type-expenses';

    public function index() {
        $expenseObj = new \Lidere\Modules\TI\Models\TypeExpense();
        $ticketObj = new \Lidere\Modules\TI\Models\Ticket();

        $expenses = $expenseObj->getExpenses();
        if(count($expenses) > 0){
            foreach($expenses as &$expense){
                $expense['tickets'] = $ticketObj->getTicketsByExpenseId($expense['id']);
            }
        }

        $this->app->render('type-expense/index.html.twig', array('expenses' => $expenses));
    }

    public function form($id = null) {
        $expenseObj = new \Lidere\Modules\TI\Models\TypeExpense();
        $applicationObj = new \Lidere\Models\Aplicacao();

        // POST: inclusão/edição
        if ( $this->app->request->isPost() ) {

            $data = $this->app->request()->post();

            if ( !empty($data['name']) ) {

                if ( !isset($data['id']) ) { // inclusão

                    $data['created_at'] = date('Y-m-d H:i:s');
                    $id = $applicationObj->insert('expense', $data);
                    $message = 'incluída';

                } else { // edição

                    $id = $data['id'];
                    unset($data['id']);
                    $applicationObj->update('expense', $id, $data);
                    $message = 'alterada';
                }
                $this->app->flash('success', 'Tipo de despesa '.$message.' com sucesso!');
                $this->app->redirect('/ti/type-expenses');
            }
        }

        $expense = null;
        if ( $id ) {
            $expense = $expenseObj->getExpense($id);
        }

        $this->app->render('type-expense/form.html.twig', array('expense' => $expense));
    }

    public function delete() {
        $id = $this->app->request()->post('id');

        $success = false;
        if ( $id ) {
            $applicationObj = new \Lidere\Models\Aplicacao();
            $success = $applicationObj->delete('expense', $id);
        }

        if ( $success ) {
            $this->app->flash('success', 'Tipo de despesa excluída com sucesso!');
        } else {
            $this->app->flash('error', 'Falha ao excluir o tipo de despesa!');
        }
        $this->app->redirect('/ti/type-expenses');

    }
}
