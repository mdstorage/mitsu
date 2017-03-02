<?php
namespace Catalog\MercedesBundle\Controller\Traits;

use Catalog\CommonBundle\Components\Constants;
use Catalog\CommonBundle\Components\Factory;

trait VinFilters {

    public function vinArticulFilter($oContainer, $parameters)
    {
        $vin_date = $this->get('request')->cookies->get(Constants::PROD_DATE);
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

            if (is_array($footnotes)){
                foreach ($footnotes as $index => &$value){
                    $abbr = $value['ABBR'];
                    if ($abbr == 'EAF' || $abbr == 'EBF')
                    {
                        $value['TEXT_FOR_TWIG'] = str_replace('<br>', '', $value['TEXT_FOR_TWIG']);

                        $date = substr(trim($value['TEXT_FOR_TWIG']), -8);

                        $sh = substr(trim($value['TEXT_FOR_TWIG']), 0, -8);
                        $shassi = trim(str_replace(array('Chassis', 'Date'), '', $sh));

                        if ((($abbr == 'EBF') && ($vin_date > $date)) || (($abbr == 'EAF') && ($vin_date < $date)))
                        {
                            unset ($footnotes[$index]);
                        }
                        if (count($footnotes) == 0){
                            $oContainer->getActivePnc()->removeArticul($articul->getCode());
                        }
                    }
                    unset($value);
                }
            }
            $articul->addOption('FOOTNOTES', $footnotes);
        }
        return $oContainer;
    }
} 