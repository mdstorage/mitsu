<?php

namespace Catalog\CommonBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Catalog\CommonBundle\Components\Factory;
use Symfony\Component\HttpFoundation\Request;

abstract class BaseController extends Controller
{
    private $filters = array();

    protected function addFilter($name, $parameters = array())
    {
        $this->filters[$name] = $parameters;
    }

    protected function getFilters()
    {
        return $this->filters;
    }

    protected function filter($object)
    {
        foreach ($this->getFilters() as $filter => $parameters) {
            if (method_exists($this, $filter)) {
                $object = $this->$filter($object, $parameters);
            }
        }
        return $object;
    }

    abstract function bundle();

    abstract function model();

    abstract function bundleConstants();

    function filters(){
        return array();
    }

    public function __construct()
    {
        Factory::setConstants($this->bundleConstants());
        foreach ($this->filters() as $name => $parameters) {
            $this->addFilter($name, $parameters);
        }
    }
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

    protected function error(Request $request, $message)
    {
        $headers = $request->server->getHeaders();
        return $this->render('CatalogCommonBundle:Catalog:error.html.twig', array(
            'message' => $message,
            'referer' => $headers['REFERER']
        ));
    }

}
