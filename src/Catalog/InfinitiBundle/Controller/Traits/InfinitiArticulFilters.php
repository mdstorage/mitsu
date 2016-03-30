<?php
/**
 * Created by PhpStorm.
 * User: itm
 * Date: 16.01.15
 * Time: 17:25
 */

namespace Catalog\InfinitiBundle\Controller\Traits;


use Catalog\CommonBundle\Components\Constants;

trait InfinitiArticulFilters {

    public function prodDateFilter($oContainer, $parameters)
    {

        return $oContainer;
    }
    
    public function articulDescFilter($oContainer, $parameters)
    {


        return $oContainer; 
    }


    public function CFilter($oContainer, $parameters)
    {

       $articulDesc = $this->model()->getArticulDesc($parameters['articul'], $parameters['regionCode'], $parameters['modelCode'], $parameters['modificationCode']);

        $complectations = $oContainer->getActiveModification()->getComplectations();


        $aAll = array();
        $minLen = 10;

        /*если имеют место разные ограничения для одного и того же артикула,
        то выбирается ограничение с меньшей длиной строки. это спорно, но пока так*/

        foreach ($articulDesc as $index => $value)
        {
            if (strlen($value['REC3']) < $minLen)
            {
                $minLen = strlen($value['REC3']);
            }

        }

        foreach ($articulDesc as $index => $value)
        {
            if (strlen($value['REC3']) != $minLen)
            {
                unset ($articulDesc[$index]);
            }
        }

        /*для найденных ограничений на использование артикулов в различных комплектациях (массив $articulDesc)
        производятся сравнения со всеми комплектациями (массив $complectations). Если описание применяемости для запчасти (таблица catalog, столбец REC3)
         и комплектация не совпадают, то комплектация не попадает в выдачу
         * */

        foreach ($complectations as $indexC => $valueC) {


            foreach ($articulDesc as $index => $value) {
                $ct = 0;
                $schemaOptions = $this->model()->multiexplode(array('+', ' +', '+ '), $value['REC3']);


                foreach ($schemaOptions as $item) {

                    $item = trim($item, ('*()'));
                    if (strpos($item, ".")) {
                        $plus = explode('.', $item);


                        if (count($plus) == count(array_intersect($plus, explode('.', $valueC->getOptions()['OPTION9'])))) {
                            $ct = $ct + 1;
                        }


                    } else {

                        if (in_array($item, explode('.', $valueC->getOptions()['OPTION9']))) {
                            $ct = $ct + 1;
                        }

                    }


                }


                if ($ct === 0) {
                    $oContainer->getActiveModification()->removeComplectation($indexC);
                }

            }
        }


        return $oContainer;
    }



} 