<?php

namespace App\Tests\Functional\Admin;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class PagesAvailabilityTest.
 */
class PagesAvailabilityTest extends WebTestCase
{
    /**
     * @test
     */
    public function pagesAreAvailable()
    {
        $client = static::createClient();

        foreach ($this->urlsToTest() as $url) {
            $client->request('GET', $url);
            $this->assertTrue($client->getResponse()->isSuccessful(), sprintf('Url %s is unavailable', $url));
        }
    }

    /**
     * @return array
     */
    private function urlsToTest(): array
    {
        return [
            '/admin/app/user/list',
            '/admin/dashboard',
        ];
    }
}
