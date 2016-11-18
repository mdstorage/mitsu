<?php


namespace Catalog\CommonBundle\Twig\Extension;

use \Twig_Extension;
use Symfony\Component\HttpFoundation\RequestStack;


class DateConvertorExtension extends Twig_Extension{

    protected $requestStack;

    public function __construct(RequestStack $requestStack){
        $this->requestStack = $requestStack;
    }


    public function getName()
    {
        return 'date_convertor.extension';
    }

    public function getFilters() {
        return array(
            'date_convertor'   => new \Twig_Filter_Method($this, 'dateConvertor'),
            'file_exists'   => new \Twig_Filter_Method($this, 'fileExists')
        );
    }

    /*public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction(
                'set_locale',array($this, 'setLocale')
            )
        );
    }*/

    public function dateConvertor($str) {
        if ($str == "" || $str == "00000000" || $str == "99999999") {
            return "...";
        }
        return (substr( $str, 0, 4)."/".substr($str, 4, 2)."/".substr($str, 6, 2));
    }

    public function fileExists($file) {

        if (file_exists($file)) {
            return true;
        }

        return false;
    }

   /* public function setLocale($request, $locale) {

        $localeSet = ($locale == 'ru')?'ru_RU':'en_EN';

        $request->setLocale($localeSet);

    }*/

}