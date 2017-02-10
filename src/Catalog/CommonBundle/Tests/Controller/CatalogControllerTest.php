<?php

namespace Catalog\CommonBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/mazda/catalog/0');

        $this->assertTrue($crawler->filter('html:contains("Выбрать регион")')->count() > 0);

        var_dump('1'); die;
    }
}
