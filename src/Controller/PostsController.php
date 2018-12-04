<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 18.11.2018
 * Time: 14:16
 */

declare(strict_types = 1);

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\File;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use App\Form\UserType;
use App\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;


class PostsController extends Controller
{

    /**
     * @Route("/posts", name="posts_list")
     */
    public function show(Request $request)
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

    /**
     * @Route("/posts/{id}", name="post_one", requirements={"id":"[0-9]+"})
     */
    public function showPost($id)
    {
        $post = $this->getDoctrine()
            ->getRepository(Post::class)->find($id);
        if(!$post){
            throw $this->createNotFoundException('Post not found');
        }

        $id = $post->getAuthor()->getId();

        $user = $this->getDoctrine()
            ->getRepository(User::class)->find($id);

        return $this->render('posts/one-post.html.twig', ['post' =>$post, 'user'=>$user]);
    }

    /**
     * @Security("is_granted('ROLE_BLOGGER')")
     * @Route("/add-post", name="add_post")
     */
    public function bloggerPost(Request $request, TokenStorageInterface $tokenStorage)
    {
        $user = $tokenStorage->getToken()
            ->getUser();
        $post = new Post();

        $post->setAuthor($user);
        $form = $this->createForm(PostType::class, $post);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $image = $post->getImage();
            $imageName = $this->generateUniqueFileName().'.'.$image->guessExtension();

            try {
                $image->move(
                    $this->getParameter('uploads_images'),
                    $imageName
                );
            } catch (FileException $e) {
                return new Response('<html><body>Error!</body></html>');
            }

            $post->setImage($imageName);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($post);
            $entityManager->flush();

            return $this->redirectToRoute('posts_list');
        }
        return $this->render(
            'posts/add-post.html.twig',
            array('form' => $form->createView())
        );
    }

    /**
     * @return string
     */
    private function generateUniqueFileName()
    {

        return md5(uniqid());
    }

    /**
     * @Route("/pop-posts", name="pop-posts")
     */
    public function showPopular(Request $request)
    {
        $postdata = $this->getDoctrine()
            ->getRepository(Post::class)->findSortedByLikes();

        $paginator  = $this->get('knp_paginator');

        $post = $paginator->paginate(
            $postdata,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('posts/pop-posts.html.twig', ['post' =>$post]);
    }

    /**
     * @Route("/pop-posts/{id}", name="pop_one_post", requirements={"id":"[0-9]+"})
     */
    public function showOnePopular($id)
    {
        $post = $this->getDoctrine()
            ->getRepository(Post::class)->find($id);
        if(!$post){
            throw $this->createNotFoundException('Post not found');
        }

        $id = $post->getAuthor()->getId();

        $user = $this->getDoctrine()
            ->getRepository(User::class)->find($id);

        return $this->render('posts/pop-one-post.html.twig', ['post' =>$post, 'user'=>$user]);
    }

    /**
     * @Route("/my-posts", name="my_post")
     */
    public function showMyPosts(Request $request, TokenStorageInterface $tokenStorage)
    {
        $username = $tokenStorage->getToken()->getUser()->getUsername();

        $postdata = $this->getDoctrine()
                ->getRepository(Post::class)->findMyPosts($username);

        $paginator  = $this->get('knp_paginator');

        $post = $paginator->paginate(
            $postdata,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('posts/my-post.html.twig', ['post' =>$post]);
    }

    /**
     * @Route("/my-posts/{username}", name="blogger_post")
     */
    public function showBloggerPosts($username)
    {
        $post = $this->getDoctrine()
            ->getRepository(Post::class)->findBloggerPosts($username);

        $user = $this->getDoctrine()
            ->getRepository(User::class)->findOneBy(['username' => $username]);

        return $this->render('posts/blogger-posts.html.twig', ['post' =>$post, 'user' =>$user]);
    }

    /**
     * @Security("is_granted('ROLE_USER', 'ROLE_BLOGGER')")
     * @Route("/preferences", name="preferences")
     */
    public function preferences(TokenStorageInterface $tokenStorage, Request $request)
    {
        $currentUser = $tokenStorage->getToken()
            ->getUser();

        $usersToFollow = [];

        if ($currentUser instanceof User) {
            $postdata = $this->getDoctrine()
                ->getRepository(Post::class)->findPreferences(
                $currentUser->getFollowing()
            );

            $paginator  = $this->get('knp_paginator');

        } else {
            $postdata = $this->getDoctrine()
                ->getRepository(Post::class)->findBy(
                [],
                ['created' => 'DESC']
            );

            $paginator  = $this->get('knp_paginator');
        }

        $post = $paginator->paginate(
            $postdata,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render(
            'posts/preferences.html.twig',
            [
                'post' => $post,
                'usersToFollow' => $usersToFollow,
            ]
        );
    }

    /**
     * @Route("/posts-admin", name="posts_admin_list")
     */
    public function showAdminPosts(Request $request)
    {
        $postdata = $this->getDoctrine()
            ->getRepository(Post::class)->findByDateAdmin();

        $paginator  = $this->get('knp_paginator');

        $post = $paginator->paginate(
            $postdata,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('posts/admin-posts.html.twig', ['post' =>$post]);
    }

    /**
     * @Route("/posts-admin/{id}", name="post_admin_one", requirements={"id":"[0-9]+"})
     */
    public function showAdminOnePost($id)
    {
        $post = $this->getDoctrine()
            ->getRepository(Post::class)->find($id);
        if(!$post){
            throw $this->createNotFoundException('Post not found');
        }

        $id = $post->getAuthor()->getId();

        $user = $this->getDoctrine()
            ->getRepository(User::class)->find($id);

        return $this->render('posts/admin-post-one.html.twig', ['post' =>$post, 'user'=>$user]);
    }


}