<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 30.11.2018
 * Time: 12:05
 */

declare(strict_types = 1);

namespace App\Controller;

use App\Entity\Post;
use App\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class LikesController extends Controller
{
    /**
     * @Route("/like/{id}", name="likes_like")
     */
    public function like(Post $post)
    {
        /** @var User $currentUser */
        $currentUser = $this->getUser();

        if (!$currentUser instanceof User) {
            return new JsonResponse([], Response::HTTP_UNAUTHORIZED);
        }

        $post->like($currentUser);
        $this->getDoctrine()->getManager()->flush();

        return new JsonResponse([
            'count' => $post->getLikedBy()->count()
        ]);
    }

    /**
     * @Route("/unlike/{id}", name="likes_unlike")
     */
    public function unlike(Post $post)
    {
        /** @var User $currentUser */
        $currentUser = $this->getUser();

        if (!$currentUser instanceof User) {
            return new JsonResponse([], Response::HTTP_UNAUTHORIZED);
        }

        $post->getLikedBy()->removeElement($currentUser);
        $this->getDoctrine()->getManager()->flush();

        return new JsonResponse([
            'count' => $post->getLikedBy()->count()
        ]);
    }
}