<?php
/**
 * Created by PhpStorm.
 * User: itm
 * Date: 10.01.15
 * Time: 13:31
 */

namespace Catalog\CommonBundle\Models;

use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\RequestStack;

abstract class VinModel{
    protected $conn;
    protected $requestStack;

    public function __construct(Connection $connection)
    {
        $this->conn = $connection;
    }
    public function setRequestStack(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;

    }
} 