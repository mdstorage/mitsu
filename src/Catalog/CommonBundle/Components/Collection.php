<?php
namespace Catalog\CommonBundle\Components;

use Catalog\CommonBundle\Components\Interfaces\CommonInterface;
use Catalog\CommonBundle\Components\Interfaces\CollectionInterface;

class Collection implements CollectionInterface{

    private $collection;

    public function setCollection($collection, CommonInterface $object)
    {
        $this->collection = array();

        if(!empty($collection)){
            foreach($collection as $code=>$data){
                $oObject = clone $object;
                if($code){
                    $oObject->setCode($code);
                }
                if(isset($data[Constants::NAME])){
                    $oObject->setName($data[Constants::NAME]);
                }
                if(isset($data[Constants::OPTIONS])){
                    $oObject->setOptions($data[Constants::OPTIONS]);
                }
                $this->collection[$code] = $oObject;
            }
        }

        return $this;
    }

    public function getCollection()
    {
        return $this->collection;
    }
} 