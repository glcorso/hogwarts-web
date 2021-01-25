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
 * Queue contendo a chave para a fila de processamento
 *
 * @package Core
 * @subpackage Jobs
 * @category Queue
 * @author Ramon Barros
 */

namespace Lidere\Jobs\Queues;

class Queue
{

    /**
     * Stores our queue semaphore.
     * @var resource
     */
    private static $queue = null;

    private static $return = null;

    private static $file = null;

    /**
     * getQueue: Returns the semaphore message resource.
     *
     * @access public
     */
    public static function getQueue()
    {
        self::$file = APP_ROOT.'storage'.DS.'queue'.DS.'daemon.queue';
        if (!file_exists(self::$file)) {
            touch(self::$file);
        }
        // $msg_id = ftok($queue_file, 'r');
        # Setup the queue
        self::$queue = msg_get_queue(QUEUE_KEY);

        # Return the queue
        return self::$queue;
    }

    /**
     * getReturn: Returns the msg_stat_queue
     * @return string
     */
    public static function getReturn()
    {
        return self::$return;
    }

    /**
     * addMessage: Given a key, store a new message into our queue.
     *
     * @param $key string - Reference to the message (PK)
     * @param $data array - Some data to pass into the message
     */
    public static function addMessage($key, $data = array(), $status = 0)
    {
        # What to send
        $message = new \Lidere\Jobs\Message($key, $data, $status);
        self::getQueue();
        self::storage($message);
        # Try to send the message
        if (msg_send(self::$queue, QUEUE_TYPE_START, $message, true, true, $msg_err)) {
            self::$return = msg_stat_queue(self::$queue);
            print_r(self::$return);
            $message->setStatus(1);
        } else {
            self::$return = "Error adding to the queue [{$msg_err}]";
            $message->setStatus(2);
        }
        self::storage($message);
    }

    public static function storage($message = null)
    {
        $queues = unserialize(file_get_contents(self::$file));
        if (!empty($queues)) {
            foreach ($queues as &$queue) {
                if ($queue->getKey() == $message->getKey()) {
                    $queue = $message;
                }
            }
        } else {
            $queues[] = $message;
        }
        file_put_contents(self::$file, serialize($queues));
    }
}
