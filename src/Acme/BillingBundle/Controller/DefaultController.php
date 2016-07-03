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

        $token = $this->get('request')->get('token');


       /* $token = '32ab744a0b-03ac221d423c593-65ec873';*/


        $data = array();
        $data = $this->get('my_token_info')->getDataToken($token);

        if(empty($data['status'])){
            return $this->error($this->get('request'), 'Сервис не оплачен');
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

    protected function error(Request $request, $message)
    {
        $headers = $request->server->getHeaders();
        return $this->render('CatalogCommonBundle:Catalog:error.html.twig', array(
            'message' => $message,
            'referer' => "http://vincat.ru/origin"
        ));
    }


}
