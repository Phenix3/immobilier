<?php

namespace App\DataFixtures;

use App\Entity\Setting;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class SettingFixture extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $settings = [
            (new Setting())
            ->setName('site_contact_email')
            ->setValue('contact@immobilier.com'),
            (new Setting())
            ->setName('site_contact_phone')
            ->setValue('+237696423326'),
            (new Setting())
            ->setName('site_location')
            ->setValue('Cameroun, Extreme-Nord, Maroua, Lougueo')
        ];

        foreach ($settings as $setting) {
            $manager->persist($setting);
        }

        $manager->flush();
    }
}
