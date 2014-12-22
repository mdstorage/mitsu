<?php
namespace Catalog\CommonBundle\Components;


use Catalog\CommonBundle\Components\Interfaces\CollectionInterface;
use Catalog\CommonBundle\Components\Interfaces\ModelInterface;
use Catalog\CommonBundle\Components\Interfaces\ModificationInterface;

class Model extends CommonElement implements ModelInterface{

    private $modifications;

    public function setModifications(CollectionInterface $collection)
    {
        $this->modifications = $collection;

        return $this;
    }

    public function addModification(ModificationInterface $modification)
    {
        $this->modifications->addCollectionItem($modification);

        return $this;
    }

    public function getModifications()
    {
        return $this->modifications->getCollection();
    }
} 