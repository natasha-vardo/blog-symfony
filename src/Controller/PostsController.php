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
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;


class PostsController extends AbstractController
{

    /**
     * @Route("/posts", name="posts_list")
     */
    public function show()
    {
        $post = $this->getDoctrine()
            ->getRepository(Post::class)->findByDate();

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
     * @Route("/pop-posts", name="pop-posts")
     */
    public function showPopular()
    {
        $post = $this->getDoctrine()
            ->getRepository(Post::class)->findByLikes();

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

        $post = $this->getDoctrine()
                ->getRepository(Post::class)->findMyPosts($username);

        return $this->render('posts/my-post.html.twig', ['post' =>$post]);
    }

    /**
     * @Route("/my-posts/{username}", name="blogger_post")
     */
    public function showBloggerPosts($username)
    {
        $post = $this->getDoctrine()
            ->getRepository(Post::class)->findBloggerPosts($username);

        return $this->render('posts/blogger-posts.html.twig', ['post' =>$post]);
    }
}