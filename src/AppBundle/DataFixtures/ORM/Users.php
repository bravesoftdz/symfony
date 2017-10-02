<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class Users extends Fixture
{
    const FAKE_COUNT = 20;

    public function load(ObjectManager $manager)
    {
        for ($i = 0; $i < self::FAKE_COUNT; $i++) {
            $user = new User();
            $encoder = $this->container->get('security.password_encoder');
            $password = $encoder->encodePassword($user, 'test');
            $user->setUsername('test user '.$i);
            $user->setEmail('test@test'.$i);
            $user->setPassword($password);
            $manager->persist($user);
        }

        $manager->flush();
    }
}