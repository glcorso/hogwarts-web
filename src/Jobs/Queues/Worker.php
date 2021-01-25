<?php
/**
 * This file is part of the Lidere Sistemas (http://lideresistemas.com.br)
 *
 * Copyright (c) 2018  Lidere Sistemas (http://lideresistemas.com.br)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Lidere\Jobs\Queues;

/**
 * Worker que processa a fila
 *
 * @package Core
 * @subpackage Jobs
 * @category Worker
 * @author Ramon Barros
 */
class Worker
{
    /**
     * Store the semaphore queue handler.
     * @var resource
     */
    private $queue = null;
    /**
     * Store an instance of the read Message
     * @var Message
     */
    private $message = null;
    /**
     * Constructor: Setup our enviroment, load the queue and then
     * process the message.
     */
    public function __construct()
    {
        # Get the queue
        $this->queue = Queue::getQueue();
        # Now process
        $this->process();
    }

    private function log($msg = null)
    {
        echo $msg."\n";
        dlog('info', $msg);
    }

    private function process()
    {
        $messageType = null;
        $messageMaxSize = 1024;
        $this->log('Iniciado fila de processamento '.QUEUE_KEY.'...');
        # Loop over the queue
        while (msg_receive($this->queue, QUEUE_TYPE_START, $messageType, $messageMaxSize, $this->message)) {
            # We have the message, fire back
            $this->complete($messageType, $this->message);
            # Reset the message state
            $messageType = null;
            $this->message = null;
        }
    }
    /**
     * complete: Handle the message we read from the queue
     *
     * @param $messageType int - The type we actually got, not what we desired
     * @param $message Message - The actual object
     */
    private function complete($messageType, Message $message)
    {
        # Generic method
        $uniqid = $message->getKey();
        $msg = $message->getData();
        $this->log('Processo '.$uniqid.':'.$messageType.' em execução...');
        if (is_object($msg)) {
            if (!empty($msg->class)) {
                $class = $msg->class;
                $method = $msg->method;
                $newClass = new $class;
                $newClass->uniqid = $uniqid;
                $newClass->messageType = $messageType;
                $params = $msg->params;
                if (method_exists($newClass, $method)) {
                    $error = call_user_func_array(array($newClass, $method), $params);
                    $message->setStatus($error ? 3 : 4);
                } else {
                    echo "Not found method [{$method}]\n";
                }
            } else {
                echo "Not found class [{$msg->class}]!\n";
            }
        } elseif (is_string($msg)) {
            echo $msg."\n";
        }
        $this->log('Processo '.$uniqid.':'.$messageType.' executado.');
        $this->storage($message);
    }

    public function storage($message = null)
    {
        $file = APP_ROOT.'storage'.DS.'queue'.DS.'daemon.queue';
        $queues = unserialize(file_get_contents($file));
        if (!empty($queues)) {
            foreach ($queues as &$queue) {
                if ($queue->getKey() == $message->getKey()) {
                    $queue = $message;
                }
            }
        } else {
            $queues[] = $message;
        }
        file_put_contents($file, serialize($queues));
    }
}
