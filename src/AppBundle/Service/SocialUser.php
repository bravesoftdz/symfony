<?php

namespace AppBundle\Service;

use AppBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Facebook\GraphNodes\GraphUser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\DependencyInjection\ContainerInterface as Container;

/**
 * Class SocialUser
 * @package AppBundle\Service
 */
class SocialUser
{
    /** @var Container  */
    private $container;

    /** @var EntityManager */
    private $manager;

    public function __construct(Container $container, EntityManager $manager)
    {
        $this->container = $container;
        $this->manager = $manager;
    }

    /**
     * @param GraphUser $graphUser
     * @return User
     */
    public function createUser(GraphUser $graphUser)
    {
        $realPassword = random_bytes(8);
        $user         = new User();
        $encoder      = $this->container->get('security.password_encoder');
        $password     = $encoder->encodePassword($user, $realPassword);
        $user->setUsername($graphUser['name']);
        $user->setEmail($graphUser['email']);
        $user->setPassword($password);
        $this->manager->persist($user);
        $this->manager->flush();

//        if ($user) {
//            Mail::to($user)->queue(new setUserPassword($user));
//        }
        return $user;
    }

    /**
     * User auth
     * @param User $user
     * @return boolean
     */
    public function authorizeUser(User $user, Request $request)
    {
        /* Social User auth in Symfony */
        $token = new UsernamePasswordToken($user, $user->getPassword(), 'auth', $user->getRoles());
        $this->container->get('security.token_storage')->setToken($token);

        // Fire the login event
        // Logging the user in above the way we do it doesn't do this automatically
        $event = new InteractiveLoginEvent($request, $token);
        $this->container->get('event_dispatcher')->dispatch('security.interactive_login', $event);

        return true;
    }

}