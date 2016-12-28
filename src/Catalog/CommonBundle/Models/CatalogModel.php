<?php
/**
 * Created by PhpStorm.
 * User: itm
 * Date: 23.12.14
 * Time: 11:53
 */

namespace Catalog\CommonBundle\Models;


use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Translation\Translator;

abstract class CatalogModel{
    protected $conn;
    protected $requestStack;
    protected $translat;


    public function __construct(Connection $connection)
    {
        $this->conn = $connection;
    }

    abstract function getRegions();
    abstract function getModels($regionCode);
    abstract function getModifications($regionCode, $modelCode);
    abstract function getComplectations($regionCode, $modelCode, $modificationCode);

    protected function array_column($array, $column)
    {
        $return = array();

        foreach ($array as $value) {
            if (isset($value[$column])) {
                $return[] = $value[$column];
            }
        }

        return $return;
    }

    public function setRequestStack(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;

    }

    public function setTranslation(Translator $translator)
    {
        /*var_dump($translator->trans('EU_LH', array(), 'subaru')); die;*/
        $this->translat = $translator;
    }

}