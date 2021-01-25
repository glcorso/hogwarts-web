<?php
/**
 * This file is part of the Lidere Sistemas (http://lideresistemas.com.br)
 *
 * Copyright (c) 2018  Lidere Sistemas (http://lideresistemas.com.br)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 *
 * PHP Version 7
 *
 * @category Modules
 * @package  Lidere
 * @author   Lidere Sistemas <suporte@lideresistemas.com.br>
 * @license  Copyright (c) 2018
 * @link     https://www.lideresistemas.com.br/license.md
 */
namespace Lidere\Modules\Worker\Controllers;

use Lidere\Core;
use Lidere\Models\Aplicacao;
use Lidere\Controllers\Cli;
use Lidere\Modules\Comercial\Services\RelatorioVisitasMontadoras as RelatorioVisitasService;

/**
 * Email
 *
 * @category   Modules
 * @package    Lidere\Modules
 * @subpackage Worker\Controllers\Email
 * @author     Ramon Barros <ramon@lideresistemas.com.br>
 * @copyright  2018 Lidere Sistemas
 * @license    Copyright (c) 2018
 * @link       https://www.lideresistemas.com.br/license.md
 */
class Email extends Cli
{
    public function queue()
    {
        $this->log(null, "Controller/Email::queue() - Buscando emails");
        $aplicacaoObj = new Aplicacao();
        $emails = $aplicacaoObj->buscaEmailsNaoEnviados();
        if (!empty($emails)) {
            foreach ($emails as $email) {

                $_SESSION['empresa']['id'] = $email['empresa_id'];
                $titulo = $email['titulo'];
                $mensagem = $email['conteudo'];
                $usuario = unserialize($email['destinatario']);
                $bcc = unserialize($email['destinatario_oculto']);
                $files = unserialize($email['arquivos']);

                $send = false;
                if ($email['tipo'] == 'portal') {
                    $send = Core::enviaEmailPortal($titulo, $mensagem, $usuario, $bcc, $files);
                } elseif ($email['tipo'] == 'sac') {
                    $send = Core::enviaEmailSac($titulo, $mensagem, $usuario, $bcc);
                }

                if ($send) {
                    $this->log(null, "Enviando email: ".$titulo." ".print_r($usuario, true), print_r($bcc, true), print_r($files, true), true);
                    $aplicacaoObj->update('tenvio_emails', $email['id'], array('enviado' => Core::now()));
                }
                
            }
        $this->log(null, "Finalizado envio de emails");
        }
    }



    public function retornaRelVisitasMontadorasNaoEnviados()
    {

        $service  = new RelatorioVisitasService();
        $data = $service->retornaNaoEnviados();

        if(!empty($data['registro'])){
            foreach ($data['registro'] as $rel) {

                $html = $this->app->view->render(
                    'imprimir-email.html.twig',
                    array(
                        'registro' => $rel
                    )
                );
                
                Core::enviaEmailCoordenacaoMontadoras($html);

                $service->atualizaEnvioRelatorio($rel['id']);

            }
        }
    }
}
