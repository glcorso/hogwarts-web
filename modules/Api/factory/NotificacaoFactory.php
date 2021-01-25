<?php

class NotificacaoFactory { 

    public function criaPorAtividadeNegocio($atividade){
        
        $notificacao = new Notificacao();

        
        return $notificacao;
        
    }

}