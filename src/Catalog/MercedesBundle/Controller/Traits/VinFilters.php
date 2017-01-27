<?php
namespace Catalog\MercedesBundle\Controller\Traits;

use Catalog\CommonBundle\Components\Constants;
use Catalog\CommonBundle\Components\Factory;

trait VinFilters {

    public function vinArticulFilter($oContainer, $parameters)
    {
        $vin = $parameters[Constants::VIN];
        $vinArticulsCodes = $this->model()->getArticulsByVin($vin);
        foreach ($oContainer->getActivePnc()->getArticuls() as $articul) {
            $codeb = $articul->getOption('CODEB');
            $footnotes = $articul->getOption('FOOTNOTES');
            $codeb = str_replace('/-', '^', $codeb);
            $codebArray = str_split($codeb);
            $result = true;
            $code = array();
            if (!in_array($codeb, array('', ' ', '+', '/', '-', '', '^'))) {
                $codeString = '';
                while ($codebArray) {
                    $value = array_shift($codebArray);
                    switch ($value) {
                        case '+': $codeString .= '&&'; break;
                        case '-': $codeString .= '&&!'; break;
                        case '/': $codeString .= '||'; break;
                        case '(': $codeString .= '('; break;
                        case ')': $codeString .= ')'; break;
                        case '^': $codeString .= '||!'; break;
                        default:
                            $codeString .= 'in_array("' . $value;
                            for ($i = 0; $i < 2; $i++) {
                                $codeString .= array_shift($codebArray);

                            } $code[substr($codeString,-3,3)] = substr($codeString,-3,3);
                            $codeString .= '",$vinArticulsCodes)';
                            if ($codeString == true)
                            {

                            }
                    }
                }
                $codeString .= ';';
                if (substr($codeString, 0, 1) == "&")
                    $codeString = 'true' . $codeString;

                eval('$result = ' . $codeString);
            }

            if (!$result) {
                $oContainer->getActivePnc()->removeArticul($articul->getCode());
            }

            if (is_array($footnotes))
            {

                foreach ($footnotes as $index => $value)
                {
                    switch(substr($value['ABBR'], -2))
                    {
                        case 'BF': $prefix = 'to';break;
                        case 'AF': $prefix = 'from'; break;
                        default: $prefix = '';

                    }
                    $shassi = str_replace(' ', '', substr($value['TEXT'], 0, strlen($value['TEXT']) -8));
                    $date = substr($value['TEXT'], -8);
                    $vin_shassi = substr($vin, -7);
                    /*$value['TEXT_FOR_TWIG'] = $prefix.' Chassis '.$shassi.' '.$prefix.' Date '.$date;*/
                    $footnotes[$index] = array_merge($footnotes[$index], array('TEXT_FOR_TWIG' => stripos($prefix, 'F')?($prefix.' Chassis '.$shassi.' '.$prefix.' Date '.$date):iconv('Windows-1251', 'UTF-8', $value['TEXT'])));

                    if (($prefix == 'to' && $vin_shassi > $shassi) || ($prefix == 'from' && $vin_shassi < $shassi))
                    {
                        $oContainer->getActivePnc()->removeArticul($articul->getCode());
                    }

                }
            }
            $articul->addOption('FOOTNOTES', $footnotes);
        }
        return $oContainer;
    }
} 