<?php
/**
 * Created by PhpStorm.
 * User: denis
 * Date: 29.6.16
 * Time: 17.03
 */

namespace Acme\BillingBundle\Services;

use Symfony\Component\Routing;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;


class TokenData
{
    public function getDataToken($token)
    {

        $aData = array();
        if($curl = curl_init()) {
            curl_setopt($curl, CURLOPT_URL, "http://billing.iauto.by/get/?token=".$token);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, '_server='.json_encode($_SERVER));
            $aData = json_decode(curl_exec($curl), true);

            curl_close($curl);
        }

        return $aData;

    }

    public function getStatus($token)
    {

        $aData = array();
        $aData = $this->getDataToken($token);

        return $aData['status'];

    }



}