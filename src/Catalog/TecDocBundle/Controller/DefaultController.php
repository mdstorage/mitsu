<?php

namespace Catalog\TecDocBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $client = new \SoapClient('http://webservice-cs.tecdoc.net/pegasus-2-0/wsdl/TecdocToCatWL');

        $client->addDynamicAddress('93.85.95.12', 20078, 4);
        //$result = $client->getVehicleManufacturers3(1, 'ru', true, true, 1, 'ru', 20078);
        $result = $client->call('getVehicleManufacturers3', array(1, 'ru', true, true, 1, 'ru', 20078));
var_dump($result);die;
        return $this->render('CatalogTecDocBundle:Default:index.html.twig', array('name' => $result));
    }
}
