<?php
namespace Catalog\CommonBundle\Components\Interfaces;


interface GroupInterface extends CommonInterface
{
    public function setSubGroups(CollectionInterface $collection);
    public function getSubGroups();
}