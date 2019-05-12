<?php

namespace App\Tests\Functional\Admin;

use App\Tests\WebTestCase;

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
        $client = $this->authenticateAdmin();

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
            '/admin/app/cashback/create',
            '/admin/app/cashback/list',
            '/admin/app/cashbackplatform/list',
            '/admin/app/user/list',
            '/admin/app/user/1/edit',
            '/admin/dashboard',
        ];
    }
}
