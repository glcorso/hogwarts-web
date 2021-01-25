<?php
/**
 * This file is part of the Lidere Sistemas (http://lideresistemas.com.br)
 *
 * Copyright (c) 2018  Lidere Sistemas (http://lideresistemas.com.br)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

/**
 * Slim - Exception da aplicação
 * Em caso de erro em produção é registrado um log e enviado um
 * email para o suporte.
 *
 * @package  Core
 * @author   Lidere Sistemas <suporte@lideresistemas.com.br>
 */

use Lidere\Core;
use Lidere\ChangeLog;
use Lidere\Models\Aplicacao;
use Lidere\Models\Auxiliares;
use Lidere\Models\Empresa;
use Lidere\Modules\Auxiliares\Models\Usuarios;

/**
 * Monitor Exception que podem ocorrer no servidor de produção
 * enviando um email para o responsável do projeto na Lidere
 */
if (ENVIRONMENT == 'prod') {
    $app->error(function (\Exception $e) use ($app) {
        $number = $e->getCode();
        $line = $e->getLine();
        $file = $e->getFile();
        $message = $e->getMessage();
        $trace = current($e->getTrace());
        try {
            $email = "
                <p>An error ($number) occurred on line
                <strong>$line</strong> and in the <strong>file: $file.</strong>
                <p> $message </p>
                <p>Session:</p><pre>".print_r($_SESSION, true)."</pre>
                <p>Server :</p><pre>".print_r($_SERVER, true)."</pre>
                <p>Error:</p><pre>".$e->getTraceAsString()."</pre>";

            $mail = new \PHPMailer();
            $mail->isSMTP();
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );
            $mail->CharSet = 'UTF-8';
            //$mail->SMTPDebug = 2;
            $mail->Debugoutput = 'html';
            $mail->SMTPAuth = true;
            $mail->SMTPSecure = 'tls';
            $mail->Host = Core::parametro('sac_smtp_host');
            $mail->Port = intval(Core::parametro('sac_smtp_porta'));
            $mail->Username = Core::parametro('sac_smtp_usuario');
            $mail->Password = Core::parametro('sac_smtp_senha');
            $mail->setFrom(Core::parametro('sac_smtp_usuario'), Core::parametro('sac_smtp_nome'));
            $mail->addAddress(Config::read('APP_EMAIL_EXCEPTIONS'), Config::read('APP_EMAIL_EXCEPTIONS'));

            $temp = tempnam(sys_get_temp_dir(), 'exception');
            file_put_contents($temp, print_r($trace, 1));
            $mail->AddAttachment($temp);

            $mail->Subject = 'Portal GRX - Exception';
            $mail->msgHTML($email);
            $mail->AltBody = $email;
            $mail->send();
            Core::insereLog(
                'portal_exception',
                'Exception:'.$email,
                !empty($_SESSION['usuario']['id']) ? $_SESSION['usuario']['id'] : 0
            );
        } catch (Exception $e) {
            Core::insereLog(
                'portal_hook_exception',
                'Exception:'.$e->getTraceAsString(),
                !empty($_SESSION['usuario']['id']) ? $_SESSION['usuario']['id'] : 0
            );
            $app->log->info(print_r($e, true));
        }

        $app->render('error.html.twig');
    });
}
