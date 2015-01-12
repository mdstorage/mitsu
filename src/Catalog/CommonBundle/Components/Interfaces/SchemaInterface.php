<?php
namespace Catalog\CommonBundle\Components\Interfaces;

interface SchemaInterface extends CommonInterface
{
    public function setPncs(CollectionInterface $collection);
    public function getPncs();

    public function setCommonArticuls(CollectionInterface $collection);
    public function getCommonArticuls();

    public function setRefGroups(CollectionInterface $collection);
    public function getRefGroups();
}