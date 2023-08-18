<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Tag;
use App\Entity\Type;
use App\Entity\User;
use App\Util\Slugger;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    /**
     * @var UserPasswordEncoderInterface
     */
    protected $encoder;

    /**
     * @var \Faker\Generator
     */
    protected $faker;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
        $this->faker = Factory::create('fr_FR');
    }

    public function load(ObjectManager $manager)
    {
        $this->loadUser($manager);
        $this->loadCategory($manager);
        for ($i = 0; $i < 5; $i++) {
            $name = $this->faker->word();
            $type = new Type();

            $type
                ->setName($name)
                ->setSlug(Slugger::slugify($name))
                ->setDescription($this->faker->paragraph);

            $manager->persist($type);

            $tag = new Tag();

            $tag
                ->setName($this->faker->word)
                ;

            $manager->persist($tag);
        }
        $manager->flush();
    }

    public function loadUser(ObjectManager $manager)
    {
        $user = new User();
        $hash = $this->encoder->encodePassword($user, '123456');
        $user
            ->setUsername('Ibrahim')
            ->setEmail('admin@email.com')
            ->setPassword($hash)
            ->setRoles(['ROLE_ADMIN'])
            ->setEmailValidatedAt(new \DateTime());
        // $product = new Product();
        $manager->persist($user);

        $user2 = new User();
        $hash2 = $this->encoder->encodePassword($user, '123456');
        $user2
            ->setUsername('Phenix')
            ->setEmail('user@email.com')
            ->setPassword($hash2)
            ->setRoles(['ROLE_USER'])
            ->setEmailValidatedAt(new \DateTime());
        $manager->persist($user2);
    }

    public function loadCategory(ObjectManager $manager)
    {
        $categories = [];
        $categories[] = (new Category())
            ->setName('Achat')
            ->setSlug('achat')
            ->setDescription('Une petite description de la categorie');
        /*$categories[] = (new Category())
            ->setName('Vente')
            ->setSlug('vente')
            ->setDescription('Une petite description de la categorie');*/
        $categories[] = (new Category())
            ->setName('Location')
            ->setSlug('location')
            ->setDescription('Une petite description de la categorie');

        foreach ($categories as $category) {
            $manager->persist($category);
        }

    }
}
