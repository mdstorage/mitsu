<?php

namespace Catalog\CommonBundle\Components\Interfaces;

interface CollectionInterface {
    public function setCollection($collection, CommonInterface $object);
    public function getCollection();
} 