<?php

namespace Catalog\SaabBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CatalogControllerTest extends WebTestCase
{
    public function testRegionsmodels()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/saab/catalog/0/{regionCode}');
    }

}
