<?php


namespace Catalog\VolvoBundle\Twig\Extension;

use \Twig_Extension;

class DateConvertorExtension extends Twig_Extension{

    public function getName()
    {
        return 'volvo_date_convertor.extension';
    }

    public function getFilters() {
        return array(
            'volvo_date_convertor'   => new \Twig_Filter_Method($this, 'dateVolvoConvertor'),
            'volvo_vin_date_convertor'   => new \Twig_Filter_Method($this, 'dateConvertorVinDate'),

        );
    }

    public function dateVolvoConvertor($str) {
        if ($str == "" || $str == "00000000" || $str == "99999999") {
            return "...";
        }
        if (strlen($str) == 4)
        {
            return (substr( $str, 0, 2)."/".substr($str, 2, 2));
        }
        else
        {
            return (substr($str, 0, 1)."/".substr($str, 1, 2));
        }



    }
    public function dateConvertorVinDate($str) {
        if ($str == "" || $str == "00000000" || $str == "99999999") {
            return "...";
        }
        return (substr( $str, 0, 4)."/".substr($str, 4, 2)."/".substr($str, 6, 2));
    }
} 