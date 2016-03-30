<?php


namespace Catalog\InfinitiBundle\Twig\Extension;

use \Twig_Extension;

class DateConvertorExtension extends Twig_Extension{

    public function getName()
    {
        return 'infiniti_date_convertor.extension';
    }

    public function getFilters() {
        return array(
            'infiniti_date_convertor'   => new \Twig_Filter_Method($this, 'dateInfinitiConvertor'),
            'infiniti_vin_date_convertor'   => new \Twig_Filter_Method($this, 'dateConvertorVinDate'),

        );
    }

    public function dateInfinitiConvertor($str) {
        if ($str && $str != 65535){
            $str = str_pad($str, 4, "0", STR_PAD_LEFT);

            if ($str[0]==0 || $str[0]==1)
                return substr($str, -2) . '/20' . substr($str, 0, 2);
            else  return substr($str, -2) . '/19' . substr($str, 0, 2);

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