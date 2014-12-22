<?php
namespace Catalog\CommonBundle\Components;


use Catalog\CommonBundle\Components\Interfaces\CollectionInterface;
use Catalog\CommonBundle\Components\Interfaces\PncInterface;

class Pnc extends CommonElement implements PncInterface{
    private $active;

    public function onActive()
    {
        $this->active = true;
    }

    public function offActive()
    {
        $this->active = false;
    }

    public function isActive()
    {
        return $this->active;
    }

    private $articuls=array();

    public function setArticuls(CollectionInterface $collection)
    {
        $this->articuls = $collection->getCollection();

        return $this;
    }

    public function getArticuls()
    {
        return $this->articuls;
    }
} 