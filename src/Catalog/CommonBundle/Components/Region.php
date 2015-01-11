<?php
namespace Catalog\CommonBundle\Components;


use Catalog\CommonBundle\Components\Interfaces\CollectionInterface;
use Catalog\CommonBundle\Components\Interfaces\RegionInterface;
use Catalog\CommonBundle\Components\Interfaces\ModelInterface;

class Region extends CommonElement implements RegionInterface{

    private $models;

    public function setModels(CollectionInterface $collection)
    {
        $this->models = $collection->getCollection();

        return $this;
    }

    public function addModel(ModelInterface $model)
    {
        $this->models[] = $model;

        return $this;
    }

    public function getModels()
    {
        return $this->models;
    }

    public function removeModel($modelCode)
    {
        $models = $this->getModels();
        unset($models[$modelCode]);
        $this->models = $models;

        return $this;
    }
} 