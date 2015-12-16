<?php
/**
 * Created by PhpStorm.
 * User: Pierre-Yves
 * Date: 16/12/2015
 * Time: 23:56
 */

namespace pygillier\Chert\Test;


use Silex\WebTestCase;

class HomeTest extends WebTestCase
{
    public function createApplication()
    {
        // app.php must return an Application instance
        return require __DIR__.'/app.php';
    }

    public function testHomePage()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/');

        //$this->assertTrue($client->getResponse()->isOk());
        $this->assertCount(1, $crawler->filter('form'));
    }
}