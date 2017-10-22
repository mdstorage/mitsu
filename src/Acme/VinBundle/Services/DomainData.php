<?php
/**
 * Created by PhpStorm.
 * User: denis
 * Date: 29.6.16
 * Time: 17.03
 */

namespace Acme\VinBundle\Services;

class DomainData
{
    public function getDomainData($domain)
    {
        $url = "http://icar.by/get/vin/?domain=".$domain;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        curl_close($ch);
        $outputCollection = json_decode($output);
        return $outputCollection;
    }
}
