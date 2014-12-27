<?php

namespace Catalog\CommonBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class BaseController extends Controller
{
    /*
     * Usage example: $this->getActionParams(__CLASS__, __FUNCTION__, func_get_args());
     * @return Array of function parameters in [$name=>$value] format
     */
    protected function getActionParams($class, $function, $funcArgsArray)
    {
        $result = array();
        $reflection = new \ReflectionMethod($class, $function);
        $reflectionArray = $reflection->getParameters();

        foreach($funcArgsArray as $code=>$funcArg){
            $result[$reflectionArray[$code]->getName()] = $funcArg;
        }

        return $result;
    }
}
