<?php
namespace Catalog\CommonBundle\Components\Interfaces;


interface ModelInterface extends CommonInterface
{
    public function setModifications(CollectionInterface $collection);
    public function addModification(ModificationInterface $model);
}