<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Type;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

use App\Entity\Property;
use Faker\Factory;

class PropertyFixture extends Fixture
{
    /**
     * @var \Faker\Generator
     */
    protected $faker;

    public function __construct()
    {
        $this->faker = Factory::create('fr_FR');
    }
    public function load(ObjectManager $manager)
    {
        $types = $manager->getRepository(Type::class)->findAll();
        $users = $manager->getRepository(User::class)->findAll();
        $categories = $manager->getRepository(Category::class)->findAll();

        for ($i = 0; $i <= 100; $i ++) {
            $property = new Property();

            try {
                $property
                    ->setName($this->faker->sentence)
                    ->setDescription($this->faker->paragraphs(10, true))
                    ->setSurface(random_int(20, 400))
                    ->setRooms(random_int(1, 12))
                    ->setBedrooms(random_int(1, 10))
                    ->setFloor(random_int(0, 4))
                    ->setPrice(random_int(1000000, 10000000))
                    ->setHeat(random_int(0, 1))
                    ->setCity($this->faker->city)
                    ->setAddress($this->faker->address)
                    ->setPostalCode(random_int(100, 1000))
                    ->setSold(random_int(0, 1))
                    ->setCreatedAt(new \DateTime())
                    ->setType($types[random_int(0, 3)])
                    ->setIsPublished(random_int(0, 1))
                    ->setProprietary($users[random_int(0, 1)])
                    ->setCategory($categories[random_int(0, 1)]);
            } catch (\Exception $e) {
            }

            $manager->persist($property);
        }

        $manager->flush();
    }
}
