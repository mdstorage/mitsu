<?php
namespace Catalog\CommonBundle\Components\Interfaces;


interface RegionInterface extends CommonInterface
{
    public function setModels(CollectionInterface $collection);
    public function addModel(ModelInterface $model);
}