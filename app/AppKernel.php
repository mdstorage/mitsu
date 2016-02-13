<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Symfony\Bundle\AsseticBundle\AsseticBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new Catalog\MitsubishiBundle\CatalogMitsubishiBundle(),
            new Catalog\MazdaBundle\CatalogMazdaBundle(),
            new Catalog\CommonBundle\CatalogCommonBundle(),
            new Catalog\SubaruBundle\CatalogSubaruBundle(),
            new Catalog\MercedesBundle\CatalogMercedesBundle(),
            new Acme\TaskBundle\AcmeTaskBundle(),
            new Catalog\SuzukiBundle\CatalogSuzukiBundle(),
            new Catalog\HondaBundle\CatalogHondaBundle(),
            new Catalog\HondaEuropeBundle\CatalogHondaEuropeBundle(),
            new Catalog\HuyndaiBundle\CatalogHuyndaiBundle(),
            new Catalog\KiaBundle\CatalogKiaBundle(),
            new Catalog\BmwBundle\CatalogBmwBundle(),
            new Catalog\MiniBundle\CatalogMiniBundle(),
            new Catalog\RollsRoyceBundle\CatalogRollsRoyceBundle(),
            new Catalog\SaabBundle\CatalogSaabBundle(),
            new Catalog\FordBundle\CatalogFordBundle(),
            new Catalog\AudiBundle\CatalogAudiBundle(),
            new Catalog\VolkswagenBundle\CatalogVolkswagenBundle(),
            new Catalog\SkodaBundle\CatalogSkodaBundle(),
            new Catalog\SeatBundle\CatalogSeatBundle(),
            new Catalog\FiatBundle\CatalogFiatBundle(),


        );

        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
            $bundles[] = new Acme\DemoBundle\AcmeDemoBundle();
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
        }

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/config_'.$this->getEnvironment().'.yml');
    }

    public function init()
    {
    	date_default_timezone_set( 'Europe/Minsk' );
	parent::init();
    }
}
