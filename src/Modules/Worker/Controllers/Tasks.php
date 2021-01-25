<?php

namespace Lidere\Modules\Worker\Controllers;

use Lidere\Core;
use Lidere\Controllers\Cli;
use Lidere\Modules\Tasks\Models\Task;
use Lidere\Console\Scheduling\Schedule;
use Illuminate\Container\Container;

/**
 * Tasks - Verifica jobs para execução
 *
 * @package Lidere\Modules
 * @subpackage Worker\Controllers
 * @author Ramon Barros
 * @copyright 2018 Lidere Sistemas
 */
class Tasks extends Cli
{
    public function index()
    {
        $this->log(null, "Controller/Tasks::index() - Buscando jobs");
        $tasks = Task::all();
        if (!empty($tasks)) {
            $schedule = new Schedule();
            $env = ENVIRONMENT;

            foreach ($tasks as $task) {
                $this->log(null, "task_id:$task->id job:$task->job");
                $this->log(null, "$task->minute $task->hour $task->day $task->month $task->weekday");
                $schedule->command("settingnoqueue=true env=\"$env\" $task->job")
                         ->cron("$task->minute $task->hour $task->day $task->month $task->weekday")
                         ->after(function () use($task) {
                             echo "Task is complete... task_id:$task->id job:$task->job\n";
                             $task->running_at = date('Y-m-d H:i:s');
                             $task->save();
                         })
                         ->withoutOverlapping();
            }

            $events = $schedule->dueEvents();

            foreach ($events as $event)
            {
                $this->log(null, "Execução do comando agendado: ".$event->getSummaryForDisplay());

                $container = new Container();
                $container->bind('app', $this->app);
                $event->run($container);
            }

            if (count($events) === 0)
            {
                $this->log(null, "Nenhum comando programado está pronto para ser executado.");
            }
        }
        $this->log(null, "Controller/Tasks::index() - Finalizado busca por jobs");
    }
}
