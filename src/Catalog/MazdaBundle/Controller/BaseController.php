<?php
/**
 * Created by PhpStorm.
 * User: itm
 * Date: 24.11.14
 * Time: 16:30
 */

namespace Catalog\MazdaBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class BaseController extends Controller{
    public $params = array();

    public function getParams()
    {
        return $this->params;
    }

    protected function addParam($name, $value)
    {
        $params = $this->getParams();
        $params[$name] = $value;
        $this->params = $params;
    }

    protected function clearParams()
    {
        $this->params = array();
    }
} 