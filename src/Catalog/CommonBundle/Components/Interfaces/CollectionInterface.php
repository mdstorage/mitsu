<?php

namespace Catalog\CommonBundle\Components\Interfaces;

interface CollectionInterface {
    public function setCollection($collection, CommonInterface $object);
    public function addCollectionItem(CommonInterface $object);
    public function getCollection();

    public function getCollectionCodes();
    public function getCollectionItem($code);
} 