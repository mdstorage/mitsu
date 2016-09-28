<?php

use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

$collection = new RouteCollection();

$collection->add('acme_task_homepage', new Route('/',
    array(
    '_controller' => 'AcmeTaskBundle:Default:index',
),
    array(
        '_scheme' => 'https'
    )));

return $collection;
