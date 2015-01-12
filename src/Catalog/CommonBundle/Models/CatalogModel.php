<?php
/**
 * Created by PhpStorm.
 * User: itm
 * Date: 23.12.14
 * Time: 11:53
 */

namespace Catalog\CommonBundle\Models;


use Doctrine\DBAL\Connection;

abstract class CatalogModel{
    protected $conn;

    public function __construct(Connection $connection)
    {
        $this->conn = $connection;
    }

    abstract function getRegions();
    abstract function getModels($regionCode);
    abstract function getModifications($regionCode, $modelCode);
    abstract function getComplectations($regionCode, $modelCode, $modificationCode);
    abstract function getGroups($regionCode, $modelCode, $modificationCode);
}