<?php

namespace Catalog\CommonBundle\Components;


use Catalog\CommonBundle\Components\Interfaces\CollectionInterface;
use Catalog\CommonBundle\Components\Interfaces\GroupInterface;

class Group extends CommonElement implements GroupInterface{
    private $subgroups;

    public function setSubGroups(CollectionInterface $collection)
    {
        $this->subgroups = $collection->getCollection();

        return $this;
    }

    public function getSubGroups()
    {
        return $this->subgroups;
    }
} 