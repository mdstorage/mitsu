<?php


namespace Catalog\BmwBundle\Twig\Extension;

use \Twig_Extension;

class DateConvertorExtension extends Twig_Extension{

    public function getName()
    {
        return 'bmw_date_convertor.extension';
    }

    public function getFilters() {
        return array(
            'bmw_date_convertor'   => new \Twig_Filter_Method($this, 'dateBmwConvertor'),
            'bmw_date_month_convertor'   => new \Twig_Filter_Method($this, 'dateToMonthBmwConvertor'),
        );
    }

    public function dateBmwConvertor($str) {
        if ($str == "" || $str == "00000000" || $str == "99999999") {
            return "...";
        }
        return (substr( $str, 0, 4)."/".substr($str, 4, 2)."/".substr($str, 6, 2));
    }

    public function dateToMonthBmwConvertor($str) {
        switch ($str)
        {
            case '01': return ('Январь');
            case '02': return ('Февраль');
            case '03': return ('Март');
            case '04': return ('Апрель');
            case '05': return ('Май');
            case '06': return ('Июнь');
            case '07': return ('Июль');
            case '08': return ('Август');
            case '09': return ('Сентябрь');
            case '10': return ('Октябрь');
            case '11': return ('Ноябрь');
            case '12': return ('Декабрь');

        }

    }
} 