<?php


namespace Catalog\ToyotaBundle\Twig\Extension;

use \Twig_Extension;

class DateConvertorExtension extends Twig_Extension{

    public function getName()
    {
        return 'toyota_date_convertor.extension';
    }

    public function getFilters() {
        return array(
            'toyota_date_convertor'   => new \Twig_Filter_Method($this, 'dateToyotaConvertor'),
            'toyota_vin_date_convertor'   => new \Twig_Filter_Method($this, 'dateConvertorVinDate'),

        );
    }

    public function dateToyotaConvertor($str) {
        if ($str && $str != 999999){
            $str = str_pad($str, 4, "0", STR_PAD_LEFT);


                return substr($str, -2) . '/' . substr($str, 0, 4);

        } else {
            return '...';
        }



    }
    public function dateConvertorVinDate($str) {
        if ($str == "" || $str == "00000000" || $str == "99999999") {
            return "...";
        }
        return (substr( $str, 0, 4)."/".substr($str, 4, 2)."/".substr($str, 6, 2));
    }
} 