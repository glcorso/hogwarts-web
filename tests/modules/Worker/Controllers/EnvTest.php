<?php

use Lidere\Modules\Worker\Controllers\Env;

/**
 * Teste da Classe Core
 */
class EnvTest extends AbstractTest
{
    private $instance;

    public static function setUpBeforeClass()
    {
    }

    public static function tearDownAfterClass()
    {
    }

    public function assertPreConditions()
    {
        $this->assertTrue(
            class_exists($class = 'Lidere\Modules\Worker\Controllers\Env'),
            'Class not found: '.$class
        );
    }

    public function testeInicial()
    {
        $this->assertTrue(true);
    }

    public function testeWorkerCron()
    {
        ob_start();
        $last_line = system('php .etc/cron/cron.php', $retval);
        $last_line = ob_get_contents();
        ob_end_clean();
        $command = '* * * * * /usr/bin/php /var/www/portal-default/.etc/cron/cron.php schedule env="prod"';
        $strpos = strpos($last_line, $command);
        $this->assertTrue(!empty($last_line));
        $this->assertTrue($strpos !== false);
        ob_start();
        $last_line = system('php .etc/cron/cron.php cli', $retval);
        $last_line = trim(ob_get_contents());
        ob_end_clean();
        $command = 'cli ok!';
        $strpos = strpos($last_line, $command);
        $this->assertTrue(!empty($last_line));
        $this->assertTrue($strpos !== false);
        ob_start();
        $last_line = system('php .etc/cron/cron.php cli/1', $retval);
        $last_line = trim(ob_get_contents());
        ob_end_clean();
        $command = 'cli ok! 1';
        $strpos = strpos($last_line, $command);
        $this->assertTrue(!empty($last_line));
        $this->assertTrue($strpos !== false);

        ob_start();
        $last_line = system('php .etc/cron/cron.php worker/env', $retval);
        $last_line = trim(ob_get_contents());
        ob_end_clean();
        $this->assertTrue(file_exists('/tmp/env.output'));
        $this->assertEquals(filemtime('/tmp/env.output'), time());
    }
}
