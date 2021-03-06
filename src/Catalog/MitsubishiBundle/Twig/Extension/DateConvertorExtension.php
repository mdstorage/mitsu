<?php


namespace Catalog\MitsubishiBundle\Twig\Extension;

use \Twig_Extension;

class DateConvertorExtension extends Twig_Extension{

    public function getName()
    {
        return 'mitsubishi_date_convertor.extension';
    }

    public function getFilters() {
        return array(
            'mitsubishi_date_convertor'   => new \Twig_Filter_Method($this, 'dateMitsubishiConvertor'),
            'mitsubishi_vin_date_convertor'   => new \Twig_Filter_Method($this, 'dateConvertorVinDate'),

        );
    }

    public function dateMitsubishiConvertor($str) {
        if ($str == "" || $str == "00000000" || $str == "99999999") {
            return "...";
        }

            return (substr( $str, 4, 2)."/".substr($str, 0, 4));




    }
    public function dateConvertorVinDate($str) {
        if ($str == "" || $str == "00000000" || $str == "99999999") {
            return "...";
        }
        return (substr( $str, 0, 4)."/".substr($str, 4, 2)."/".substr($str, 6, 2));
    }
} 