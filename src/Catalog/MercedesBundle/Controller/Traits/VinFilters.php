<?php
namespace Catalog\MercedesBundle\Controller\Traits;

use Catalog\CommonBundle\Components\Constants;
use Catalog\CommonBundle\Components\Factory;

trait VinFilters {

    public function vinArticulFilter($oContainer, $parameters)
    {
        $vin = $parameters[Constants::VIN];
        $this->model()->getArticulsByVin($vin);
        return $oContainer;
    }
} 