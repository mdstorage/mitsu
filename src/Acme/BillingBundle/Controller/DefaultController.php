<?php

namespace Acme\BillingBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $request = new Request();


        $ch = curl_init("http://billing.iauto.by/get/?token=32ab744a0b-03ac221d423c593-65ec873");
        $intReturnCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $data = curl_exec($ch);


        curl_close($ch);
        var_dump($data); die;



        return $this->render('AcmeBillingBundle:Default:index.html.twig', array('name' => $request));
    }
}
