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
                $this->collection[] = $oObject;
            }
        }

        return $this;
    }

    public function addCollectionItem(CommonInterface $object)
    {
        $this->collection[] = $object;

        return $this;
    }

    public function getCollection()
    {
        return $this->collection;
    }

    public function getCollectionCodes()
    {
        $codes = array();
        foreach($this->collection as $item){
            $codes[] = $item->getCode();
        }

        return $codes;
    }

    public function getCollectionItem($code)
    {
        foreach($this->collection as $item){
            if($code === $item->getCode()){
                return $item;
            }
        }
        return null;
    }
} 