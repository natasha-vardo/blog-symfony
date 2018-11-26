<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 26.11.2018
 * Time: 14:03
 */

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;


/**
 * @Security("is_granted('ROLE_USER', 'ROLE_BLOGGER')")
 */
class FollowingController extends Controller
{

    /**
     * @Route("/follow/{id}", name="following_follow")
     */
    public function follow(User $userToFollow)
    {
        /** @var User $currentUser */
        $currentUser = $this->getUser();

        if ($userToFollow->getId() !== $currentUser->getId()) {

        $currentUser->getFollowing()->add($userToFollow);

            $this->getDoctrine()
                ->getManager()
                ->flush();
        }

        return $this->redirectToRoute(
            'posts_list',
            ['username' => $userToFollow->getUsername()]
        );
    }

    /**
     * @Route("/unfollow/{id}", name="following_unfollow")
     */
    public function unFollow(User $userToUnfollow)
    {
        /** @var User $currentUser */
        $currentUser = $this->getUser();
        $currentUser->getFollowing()
            ->removeElement($userToUnfollow);

        $this->getDoctrine()
            ->getManager()
            ->flush();

        return $this->redirectToRoute(
            'posts_list',
            ['username' => $userToUnfollow->getUsername()]
        );
    }
}