<?php

namespace Acme\BillingBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing;
use Catalog\CommonBundle\Components\Constants;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $parameters = array();

        $token = $this->get('request')->get('token');


       /* $token = '32ab744a0b-03ac221d423c593-65ec873';*/


        $data = array();
        $data = $this->get('my_token_info')->getDataToken($token);

        if(empty($data['status'])){
            return $this->errorBilling('Сервис не оплачен');
        }

        else
        {
            return $this->redirect(
                $this->generateUrl(
                    'catalog_'.$this->get('request')->get('catalog').'_token',
                    array_merge($parameters, array(
                            'token' => $token,
                            'regionCode' => null
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
            'referer' => "http://vincat.ru"
        ));
    }

    public function errorBilling($message)
    {
        return $this->render('AcmeBillingBundle:Default:errorBilling.html.twig', array(
            'message' => $message,
            'referer' => Constants::REFERER
        ));
    }


}
