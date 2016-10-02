<?php


namespace Catalog\CommonBundle\Twig\Extension;

use \Twig_Extension;

class DateConvertorExtension extends Twig_Extension{

    public function getName()
    {
        return 'date_convertor.extension';
    }

    public function getFilters() {
        return array(
            'date_convertor'   => new \Twig_Filter_Method($this, 'dateConvertor'),
            'file_exists'   => new \Twig_Filter_Method($this, 'fileExists'),
        );
    }

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
} 