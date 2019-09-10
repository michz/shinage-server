<?php
declare(strict_types=1);

/*
 * Licensed under MIT. See file /LICENSE.
 */

namespace shinage\server\behat\CLI;

use Behat\Behat\Context\Context;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpKernel\KernelInterface;

class ScreenAlarmingContext implements Context
{
    /** @var KernelInterface */
    private $kernel;

    /** @var Application */
    private $application;

    /** @var BufferedOutput */
    private $output;

    public function __construct(
        KernelInterface $kernel
    ) {
        $this->application = new Application($kernel);
        $this->output = new BufferedOutput();
        $this->kernel = $kernel;
    }

    /**
     * @When I run the alarming cron job
     */
    public function iRunTheAlarmingCronJob()
    {
        $command = 'screens:check-and-alarm';
        $input = new ArgvInput(['console', $command, '--env=test']);
        $this->application->doRun($input, $this->output);
    }
}
