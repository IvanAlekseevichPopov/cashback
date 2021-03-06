<?php

namespace App\Tests\Functional\Pub;

use App\Tests\WebTestCase;

class PagesAvailabilityTest extends WebTestCase
{
    /**
     * @test
     */
    public function pagesAreAvailable(): void
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
            '/',
            '/catalog',
            '/register/',
            '/login',
            '/login',
            '/faq',
            '/policy',
            '/conditions',
        ];
    }
}
