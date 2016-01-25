<?php


namespace Catalog\AudiBundle\Twig\Extension;

use \Twig_Extension;

class DateConvertorExtension extends Twig_Extension{

    public function getName()
    {
        return 'audi_date_convertor.extension';
    }

    public function getFilters() {
        return array(
            'audi_date_convertor'   => new \Twig_Filter_Method($this, 'dateAudiConvertor'),
        );
    }

    public function dateAudiConvertor($str) {
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
} 