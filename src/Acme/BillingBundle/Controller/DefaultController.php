<?php

namespace Acme\BillingBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing;

class DefaultController extends Controller
{
    public function indexAction()
    {
        /*$request = Request::createFromGlobals();

        $ch = curl_init("http://billing.iauto.by/get/?token=32ab744a0b-03ac221d423c593-65ec873");

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $data = json_decode(curl_exec($ch), true);

        curl_close($ch);*/



        $request = Request::createFromGlobals();
        $outData = array('token' => '32ab744a0b-03ac221d423c593-65ec873', 'SERVER' => $_SERVER);


        if($curl = curl_init()) {
            curl_setopt($curl, CURLOPT_URL, $router->generate(
                'acme_billing_output'
            ));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($outData));
            $out = curl_exec($curl);
            var_dump($out); die;
            curl_close($curl);
        }



        $parameters = array();

        if(empty($data['status'])){
            return $this->error($request, 'Сервис не оплачен');
        }

        else
        {
            return $this->redirect(
                $this->generateUrl(
                    'catalog_bmw',
                    array('token' => $data['token'])
                ), 301
            );
        }

    }

    protected function error(Request $request, $message)
    {
        $headers = $request->server->getHeaders();
        return $this->render('CatalogCommonBundle:Catalog:error.html.twig', array(
            'message' => $message,
            'referer' => 'http://billing.iauto.by/get/?token=32ab744a0b-03ac221d423c593-65ec872'
        ));
    }

    public function outputAction()
    {

        $ch = curl_init('acme_billing_homepage');

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $data = json_decode(curl_exec($ch), true);

        curl_close($ch);

        var_dump($data); die;

    }
}
