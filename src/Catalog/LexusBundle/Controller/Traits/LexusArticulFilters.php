<?php
/**
 * Created by PhpStorm.
 * User: itm
 * Date: 16.01.15
 * Time: 17:25
 */

namespace Catalog\LexusBundle\Controller\Traits;


use Catalog\CommonBundle\Components\Constants;

trait LexusArticulFilters {

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

     /*   $articulDesc = $this->model()->getArticulDesc($parameters['articul'], $parameters['regionCode'], $parameters['modelCode'], $parameters['modificationCode']);

        $complectations = $oContainer->getActiveModification()->getComplectations();


        $aAll = array();

        $aAllarticulDesc = array();


        $minLen = 10;*/

        /*если имеют место разные ограничения для одного и того же артикула,
        то все они объединяются в один массив и учитывается каждое из них*/

      /*  foreach ($articulDesc as $index => $value)
        {
            $aAll[] = $value['REC3'];

        }
        $aAllarticulDesc = (implode(' +', $aAll));



        /*для найденных ограничений на использование артикулов в различных комплектациях (массив $articulDesc)
        производятся сравнения со всеми комплектациями (массив $complectations). Если описание применяемости для запчасти (таблица catalog, столбец REC3)
         и комплектация не совпадают, то комплектация не попадает в выдачу
         * */



      /*  foreach ($complectations as $indexC => $valueC) {

            $ct = 0;
            $schemaOptions = $this->model()->multiexplode(array(' +'), $aAllarticulDesc);




            foreach ($schemaOptions as $item) {





                if (strpos($item, ".")) {

                    $plus = explode('.', $item);


                    $plusExplode = $this->model()->multiexplode(array('.','+'), $item);



                    foreach ($plusExplode as &$item)
                    {
                        $item = trim($item, ('*()'));
                    }



                    if (count($plus) === count(array_intersect($plusExplode, explode('.', $valueC->getOptions()['OPTION9'])))) {
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


        }*/


        return $oContainer;
    }

} 