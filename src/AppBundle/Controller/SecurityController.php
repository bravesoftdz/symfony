<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Helpers\FacebookDriver;
use Doctrine\Common\Persistence\ObjectManager;
use Facebook\GraphNodes\GraphUser;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class SecurityController extends Controller
{
    /**
     * @Route("/login", name="login")
     */
    public function loginAction(Request $request, AuthenticationUtils $authUtils)
    {

        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException('You are already logged in');
        }

        // get the login error if there is one
        $error = $authUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authUtils->getLastUsername();

        //facebook uri generate
        $callbackUrl = $this->get('router')->generate('login_facebook', [], Router::ABSOLUTE_URL);
        $fb = new FacebookDriver($this->getParameter('fb_id'), $this->getParameter('fb_secret_id'));

        return $this->render('security/login.html.twig', [
            'last_username'    => $lastUsername,
            'error'            => $error,
            'facebookLoginUrl' => $fb->generateUrl($callbackUrl)
        ]);
    }

    /**
     * @Route("/login/facebookLogin", name="login_facebook")
     */
    public function facebookLoginAction(Request $request)
    {
        $fb = new FacebookDriver($this->getParameter('fb_id'), $this->getParameter('fb_secret_id'));
        $response = $fb->getResponce();
        $graphUser = $response->getGraphUser();
        $repository = $this->getDoctrine()->getRepository(User::class);

        $query = $repository->createQueryBuilder('u')
            ->where('u.email = :email')
            ->setParameter('email', $graphUser['email'])
            ->getQuery();

        /** @var User $user */
        $user = $query->getOneOrNullResult();
        if (is_null($user)) {
            $this->_createUser($graphUser);
        }

        if (!$user->getFbId()){
            $user->setFbId($graphUser['id']);
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
        }

        $this->_authorizeUser($user, $request);

//        $token = new UsernamePasswordToken($user, null, 'secured_area', $user->getRoles());
//        $this->get('security.context')->setToken($token);
//        $this->get('session')->set('_security_secured_area', serialize($token));

        return $this->redirectToRoute('homepage');
    }


    /**
     * @param GraphUser $graphUser
     * @return User
     */
    private function _createUser(GraphUser $graphUser)
    {
        $realPassword = random_bytes(8);
        $manager      = $this->getDoctrine()->getManager();
        $user         = new User();
        $encoder      = $this->container->get('security.password_encoder');
        $password     = $encoder->encodePassword($user, $realPassword);
        $user->setUsername($graphUser['name']);
        $user->setEmail($graphUser['email']);
        $user->setPassword($password);
        $manager->persist($user);
        $manager->flush();

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
    protected function _authorizeUser(User $user, Request $request)
    {
        /* Social User auth in Symfony */
        $token = new UsernamePasswordToken($user, $user->getPassword(), 'auth', $user->getRoles());
        $this->get('security.token_storage')->setToken($token);

        // Fire the login event
        // Logging the user in above the way we do it doesn't do this automatically
        $event = new InteractiveLoginEvent($request, $token);
        $this->get('event_dispatcher')->dispatch('security.interactive_login', $event);

        return true;
    }

}