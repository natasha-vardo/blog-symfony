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
        $error = $authenticationUtils->getLastAuthenticationError();

        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', array(
            'last_username' => $lastUsername,
            'error'         => $error,
        ));
    }

    /**
     * @Route("/forgot-pass", name="forgot_pass")
     */
    public function forgot()
    {
        return new Response('<html><body>You have a problem, man.</body></html>');
    }

    /**
     * @Route("/", name="all_posts")
     */
    public function homepage(Request $request)
    {
        $postdata = $this->getDoctrine()
            ->getRepository(Post::class)->findByDate();

        $paginator  = $this->get('knp_paginator');

        $post = $paginator->paginate(
            $postdata,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('posts/posts.html.twig', ['post' =>$post]);
    }


}