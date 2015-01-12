<?php
/**
 * Created by PhpStorm.
 * User: itm
 * Date: 10.01.15
 * Time: 13:31
 */

namespace Catalog\CommonBundle\Models;

use Doctrine\DBAL\Connection;
abstract class VinModel {
    protected $conn;

    public function __construct(Connection $connection)
    {
        $this->conn = $connection;
    }
} 