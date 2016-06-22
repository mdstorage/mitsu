<?php

namespace Acme\BillingBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $request = new Request();


        $ch = curl_init("http://billing.iauto.by/get/?token=32ab744a0b-03ac221d423c593-65ec873");

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $data = json_decode(curl_exec($ch), true);

        curl_close($ch);

        $parameters = array();

        if(empty($data['status'])){
            return $this->error($request, 'Сервис не оплачен');
        }

        else
        {
            return $this->redirect(
                $this->generateUrl(
                    'acme_task_homepage',
                    array_merge($parameters, array(
                            'token' => $data['token']
                        )
                    )
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
}
