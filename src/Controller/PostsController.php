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
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class PostsController extends AbstractController
{

    /**
     * @Route("/posts", name="posts_list")
     */
    public function show()
    {
        $post = $this->getDoctrine()
            ->getRepository(Post::class)->findAll();
        return $this->render('posts/posts.html.twig', ['post' =>$post]);
    }
}