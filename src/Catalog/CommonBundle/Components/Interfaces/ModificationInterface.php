<?php
namespace Catalog\CommonBundle\Components\Interfaces;


interface ModificationInterface extends CommonInterface
{
    public function setComplectations(CollectionInterface $collection);
    public function addComplectation(ComplectationInterface $model);
}