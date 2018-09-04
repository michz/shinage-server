<?php

namespace shinage\serverApp;

use mztx\ShinageOnlinePlayerBundle\ShinageOnlinePlayerBundle;
use mztx\ShinagePlayerBundle\ShinagePlayerBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;
use AppBundle\DependencyInjection\Compiler\PresentationBuilderPass;

class AppKernel extends Kernel
{
    public function __construct($environment, $debug)
    {
        parent::__construct($environment, $debug);
        date_default_timezone_set("Europe/Berlin");
    }


    public function registerBundles()
    {
        // configure Annotations
        \Doctrine\Common\Annotations\AnnotationReader::addGlobalIgnoredName('time');
        \Doctrine\Common\Annotations\AnnotationReader::addGlobalIgnoredName('date');

        $bundles = [
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new \Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new \Symfony\Bundle\TwigBundle\TwigBundle(),
            new \Symfony\Bundle\MonologBundle\MonologBundle(),
            new \Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new \FOS\UserBundle\FOSUserBundle(),
            new \Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new \Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new \Rollerworks\Bundle\PasswordStrengthBundle\RollerworksPasswordStrengthBundle(),
            new \Knp\Bundle\MenuBundle\KnpMenuBundle(),
            new \JMS\SerializerBundle\JMSSerializerBundle(),

            new \AppBundle\AppBundle(),

            new ShinageOnlinePlayerBundle(),
            new ShinagePlayerBundle(),
        ];

        if (in_array($this->getEnvironment(), ['dev', 'test'], true)) {
            $bundles[] = new \Symfony\Bundle\WebServerBundle\WebServerBundle();
            $bundles[] = new \Symfony\Bundle\DebugBundle\DebugBundle();
            $bundles[] = new \Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new \Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new \Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
            $bundles[] = new \Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle();
        }
        if ('dev' === $this->getEnvironment()) {
            $bundles[] = new \Onurb\Bundle\YumlBundle\OnurbYumlBundle();
        }

        return $bundles;
    }

    public function getRootDir()
    {
        return __DIR__;
    }

    public function getCacheDir()
    {
        return dirname(__DIR__) . '/var/cache/'.$this->getEnvironment();
    }

    public function getLogDir()
    {
        return dirname(__DIR__) . '/var/logs';
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load($this->getRootDir() . '/config/config_' . $this->getEnvironment() . '.yml');
    }

    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new PresentationBuilderPass());
    }
}
