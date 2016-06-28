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
        $parameters = array();

        $request = Request::createFromGlobals();

        $token = $this->get('request')->get('token');


       /* $token = '32ab744a0b-03ac221d423c593-65ec873';*/

        $headers = $request->server->getHeaders();

        $data = $this->getDataToken($token);


        /*catalog_bmw:
  path:         /bmw/catalog/0/{regionCode}/{token}
  defaults:     {_controller: CatalogBmwBundle:Catalog:regionsModels, regionCode: null, token: null}*/

        if(empty($data['status'])){
            return $this->error($request, 'Сервис не оплачен');
        }

        else
        {
            return $this->redirect(
                $this->generateUrl(
                    'catalog'.'_'.$this->get('request')->get('catalog'),
                    array_merge($parameters, array(
                            'token' => $token,
                            'regionCode' => null
                        )
                    )
                ), 301
            );
        }

    }
    public function getDataToken($token)
    {

        $aData = array();
        $outData = array('token' => $token, 'SERVER' => $_SERVER);

        if($curl = curl_init()) {
            curl_setopt($curl, CURLOPT_URL, "http://billing.iauto.by/get/?token=".$token);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($outData));
            $aData = json_decode(curl_exec($curl), true);

            curl_close($curl);
        }

        return $aData;

    }




    protected function error(Request $request, $message)
    {
        $headers = $request->server->getHeaders();
        return $this->render('CatalogCommonBundle:Catalog:error.html.twig', array(
            'message' => $message,
            'referer' => "http://vincat.ru/origin"
        ));
    }


}
