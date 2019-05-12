<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\DBAL\Types\Enum\CashBackStatusEnumType;
use App\Entity\CashBack;
use App\Entity\CashBackImage;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\File\File;

class CashbackFixtures extends Fixture
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $cashbackImage = $this->generateImage();

        $cashback = new Cashback();

        $cashback->setActive(true);
        $cashback->setStatus(CashBackStatusEnumType::APPROVED_PARTNERSHIP);
        $cashback->setExternalId(6115);
        $cashback->setRating(10);
        $cashback->setTitle('Aliexpress WW');
        $cashback->setSlug('aliexpress-ww');
        $cashback->setDescription('descr');
        $cashback->setCondition('cond');
        $cashback->setUrl('https://alitems.com/g/1e8d114494e30bb36dd816525dc3e8/');
        $cashback->setSiteUrl('http://aliexpress.com/');
        $cashback->setCash('6%');
        $cashback->setCashBackImage($cashbackImage);

        $manager->persist($cashback);
        $manager->persist($cashbackImage);
        $manager->flush();
    }

    private function generateImage(): CashBackImage
    {
        $localPath = 'public/static/images/'.uniqid('img', true).'.jpg';

        $im = imagecreate(100, 100);
        imagecolorallocate($im, 255, 255, 0);
        imagepng($im, $localPath);

        $file = new File($localPath);

        $image = new CashBackImage();
        $image->setFile($file);

        return $image;
    }
}
