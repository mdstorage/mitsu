<?php
/**
 * Created by PhpStorm.
 * User: itm
 * Date: 14.01.15
 * Time: 14:02
 */

namespace Catalog\CommonBundle\Tests\Components;


use Catalog\CommonBundle\Components\Constants;
use Catalog\CommonBundle\Components\Factory;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateRegion()
    {
        $region = Factory::createRegion(1);
        $this->assertEquals(1, $region->getCode());
        $this->assertEquals(null, $region->getOptions());

        $region = Factory::createRegion(1, 'name', array(
            'key1'=>'value1'
        ));
        $this->assertEquals(1, $region->getCode());
        $this->assertEquals('name', $region->getName());
        $this->assertEquals('value1', $region->getOption('key1'));

        $region = Factory::createRegion(1, 'name',
        array(
            'key1' => 'value1'
        ),
        array(
            '123' => array(
                Constants::NAME     => 'modelName',
                Constants::OPTIONS  => array(
                    'option1' => 'option1Value'
                )
            )
        ));

        $this->assertEquals('modelName', $region->getModel(123)->getName());
        $this->assertEquals('option1Value', $region->getModel(123)->getOption('option1'));
    }
} 