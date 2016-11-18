<?php


namespace Catalog\CommonBundle\Twig\Extension;

use \Twig_Extension;

class setLocaleExtension extends Twig_Extension{

    public function getName()
    {
        return 'set_locale.extension';
    }

    public function getFilters() {
        return array(
            'set_locale'   => new \Twig_Filter_Method($this, 'setLocale')
        );
    }

    public function setLocale($locale) {
        setlocale(LC_ALL, 'en_EN');
    }

} 