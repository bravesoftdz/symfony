<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Helpers\FacebookDriver;
use AppBundle\Repository\UserRepository;
use AppBundle\Service\SocialUser;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

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
        /** @var FacebookDriver $fb */
        $fb = new FacebookDriver($this->getParameter('fb_id'), $this->getParameter('fb_secret_id'));
        $graphUser = $fb->getResponce()->getGraphUser();

        /** @var UserRepository $userRepository */
        $userRepository = $this->getDoctrine()->getRepository(User::class);

        /** @var User $user */
        $user = $userRepository->getUserByEmail($graphUser['email']);

        /** @var SocialUser $socialUserService */
        $socialUserService = $this->container->get(SocialUser::class);

        if (is_null($user)) {
            $user = $socialUserService->createUser($graphUser);
        }

        if (!$user->getFbId()){
            $userRepository->updateUserByFacebookId($user, $graphUser['id']);
        }

        $socialUserService->authorizeUser($user, $request);

        return $this->redirectToRoute('homepage');
    }

}