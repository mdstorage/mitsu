<?php

namespace Catalog\CommonBundle\Components;


use Catalog\CommonBundle\Components\Interfaces\CollectionInterface;
use Catalog\CommonBundle\Components\Interfaces\SchemaInterface;

class Schema extends CommonElement implements SchemaInterface{
    private $pncs = array();
    private $commonArticuls = array();
    private $refGroups = array();

    public function setPncs(CollectionInterface $collection)
    {
        $this->pncs = $collection->getCollection();

        return $this;
    }

    public function getPncs()
    {
        return $this->pncs;
    }

    public function setActivePnc($code)
    {
        $this->pncs[$code]->onActive();

        return $this;
    }

    public function setCommonArticuls(CollectionInterface $collection)
    {

        $this->commonArticuls = $collection->getCollection();

        return $this;
    }

    public function getCommonArticuls()
    {
        return $this->commonArticuls;
    }

    public function setRefGroups(CollectionInterface $collection)
    {
        $this->refGroups = $collection->getCollection();

        return $this;
    }

    public function getRefGroups()
    {
        return $this->refGroups;
    }
} 