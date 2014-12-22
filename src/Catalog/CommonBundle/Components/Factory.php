<?php
namespace Catalog\CommonBundle\Components;

use Catalog\CommonBundle\Components\Interfaces\CommonInterface;

class Factory {

    public static function createContainer()
    {
        $oContainer = new Container();

        return $oContainer;
    }

    public static function createRegion($code=null, $name=null, $options=null, $models=array())
    {
        $oRegion = new Region();
        self::setParameters($oRegion, $code, $name, $options);
        if(!empty($models)){
            $oRegion->setModels(self::createCollection($models, self::createModel()));
        }

        return $oRegion;
    }

    public static function createModel($code=null, $name=null, $options=null, $modifications=array())
    {
        $oModel = new Model();
        self::setParameters($oModel, $code, $name, $options);
        if(!empty($modifications)){
            $oModel->setModifications(self::createCollection($modifications, self::createModification()));
        }

        return $oModel;
    }

    public static function createModification($code=null, $name=null, $options=null, $complectations=array())
    {
        $oModification = new Modification();

        self::setParameters($oModification, $code, $name, $options);
        if(!empty($complectations)){
            $oModification->setComplectations(self::createCollection($complectations, self::createComplectation()));
        }

        return $oModification;
    }

    public static function createComplectation($code=null, $name=null, $options=null)
    {
        $oComplectation = new Complectation();
        self::setParameters($oComplectation, $code, $name, $options);

        return $oComplectation;
    }

    public static function createGroup($code=null, $name=null, $options=null)
    {
        $oGroup = new Group();
        self::setParameters($oGroup, $code, $name, $options);

        return $oGroup;
    }

    public static function createSchema($code=null, $name=null, $options=null)
    {
        $oSchema = new Schema();
        self::setParameters($oSchema, $code, $name, $options);

        return $oSchema;
    }

    public static function createPnc($code=null, $name=null, $options=null)
    {
        $oPnc = new Pnc();
        self::setParameters($oPnc, $code, $name, $options);

        return $oPnc;
    }

    public static function createArticul($code=null, $name=null, $options=null)
    {
        $oArticul = new Articul();
        self::setParameters($oArticul, $code, $name, $options);

        return $oArticul;
    }

    public static function createCollection($collection, CommonInterface $object)
    {
        $oCollection = new Collection();
        $oCollection->setCollection($collection, $object);
        return $oCollection;
    }

    private static function setParameters(CommonInterface $object, $code=null, $name=null, $options=null)
    {
        if($code){
            $object->setCode($code);
        }
        if($name){
            $object->setName($name);
        }
        if($options){
            $object->setOptions($options);
        }
    }
} 