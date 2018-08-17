<?php

declare(strict_types=1);

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Client as SymfonyClient;

/**
 * Class Client.
 */
class Client extends SymfonyClient
{
    /**
     * @return int
     */
    public function getResponseCode()
    {
        return parent::getResponse()->getStatusCode();
    }
}
