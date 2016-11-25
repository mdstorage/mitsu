<?php


namespace Catalog\FordBundle\Twig\Extension;

use \Twig_Extension;

class DateConvertorExtension extends Twig_Extension{

    public function getName()
    {
        return 'ford_date_convertor.extension';
    }

    public function getFilters() {
        return array(
            'ford_date_convertor'   => new \Twig_Filter_Method($this, 'dateFordConvertor'),
        );
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction(
                'get_files',array($this, 'getFiles')
            )
        );
    }

    public function dateFordConvertor($str) {
        if ($str == "" || $str == "00000000" || $str == "99999999") {
            return "...";
        }
        return (substr( $str, 0, 4)."/".substr($str, 4, 2)."/".substr($str, 6, 2));
    }

    public function getFiles($dir) {

        $files1 = scandir($dir, 1);

        return($files1);

    }
} 