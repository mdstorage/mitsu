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

        $request = Request::createFromGlobals();

        $token = $request->query->all()['token'];

       /* $token = '32ab744a0b-03ac221d423c593-65ec873';*/
        $outData = array('token' => $token, 'SERVER' => $_SERVER);

        if($curl = curl_init()) {
            curl_setopt($curl, CURLOPT_URL, "http://billing.iauto.by/get/?token=".$token);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($outData));
            $data = json_decode(curl_exec($curl), true);

            curl_close($curl);
        }

        $headers = $request->server->getHeaders();

        if(empty($data['status'])){
            return $this->error($request, 'Сервис не оплачен');
        }

        else
        {
            return $this->redirect(
                $headers['REFERER'], 301
            );
        }

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
