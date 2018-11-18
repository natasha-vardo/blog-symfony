<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 18.11.2018
 * Time: 13:29
 */

declare(strict_types = 1);

namespace App\Controller;

use App\Entity\Post;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends Controller
{
    /**
     * @Route("/login", name="login")
     */
    public function login(Request $request, AuthenticationUtils $authenticationUtils)
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', array(
            'last_username' => $lastUsername,
            'error'         => $error,
        ));
    }

    /**
     * @Route("/admin", name="admin_page")
     */

    public function users()
    {
        return new Response('<html><body>Admin Page</body></html>');
    }

    /**
     * @Route("/forgot-pass", name="forgot_pass")
     */
    public function forgot()
    {
        return new Response('<html><body>You have a problem, man.</body></html>');
    }

    /**
     * @Route("/user", name="user_page")
     */
    public function homepage()
    {
        return $this->render('users/uspage.html.twig');
    }

    /**
     * @Route("/", name="start_page")
     */
    public function startpage()
    {
        return $this->render('start/start-page.html.twig');
    }
}