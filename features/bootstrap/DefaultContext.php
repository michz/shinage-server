<?php

namespace shinage\server\behat;

use Behat\MinkExtension\Context\MinkContext;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @author   :  Michael Zapf <m.zapf@mztx.de>
 * @date     :  21.11.17
 * @time     :  06:58
 */

class DefaultContext extends MinkContext implements KernelAwareContext
{
    /**
     * @var KernelInterface
     */
    protected $kernel;

    /**
     * @BeforeScenario
     */
    public function prepareData()
    {
        $this->clearData();
    }

    /**
     * {@inheritdoc}
     */
    public function setKernel(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * Clear Database for clean test setup.
     */
    protected function clearData()
    {
        $purger = new ORMPurger($this->getService('doctrine')->getManager());
        $purger->setPurgeMode(ORMPurger::PURGE_MODE_DELETE);
        $purger->purge();
    }

    /**
     * Returns Container instance.
     *
     * @return ContainerInterface
     */
    protected function getContainer(): ContainerInterface
    {
        return $this->kernel->getContainer();
    }

    /**
     * Get service by id.
     *
     * @param string $id
     *
     * @return object
     */
    protected function getService($id)
    {
        return $this->getContainer()->get($id);
    }
}
