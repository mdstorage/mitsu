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
            $codebArray = str_split($codeb);
            $result = true;
            if (!in_array($codeb, array('', ' ', '+', '/', '-', ''))) {
                $codeString = '';
                while ($codebArray) {
                    $value = array_shift($codebArray);
                    switch ($value) {
                        case '+': $codeString .= '&&'; break;
                        case '-': $codeString .= '&&!'; break;
                        case '/': $codeString .= '||'; break;
                        case '(': $codeString .= '('; break;
                        case ')': $codeString .= ')'; break;
                        default:
                            $codeString .= 'in_array("' . $value;
                            for ($i = 0; $i < 2; $i++) {
                                $codeString .= array_shift($codebArray);
                            }
                            $codeString .= '",$vinArticulsCodes)';
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
        }
        return $oContainer;
    }
} 