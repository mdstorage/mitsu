<?php

namespace Catalog\TecDocBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $client = new \SoapClient('http://webservice-cs.tecdoc.net/pegasus-2-0/wsdl/TecdocToCatWL');

      //$client->addDynamicAddress('93.85.95.12', 20078, 4);
        $result = $client->getVehicleManufacturers2(array('in0' =>array(
            'carType' => '1',
            'countriesCarSelection' => 'ru',
            'countryGroupFlag' => false,
            'evalFavor' => false,
            'favouredList' => 1,
            'lang' => 'ru',
            'provider' => 20078))
        );

var_dump($result);die;
        return $this->render('CatalogTecDocBundle:Default:index.html.twig', array('name' => $result));
    }
}
