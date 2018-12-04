<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 01.12.2018
 * Time: 14:24
 */

declare(strict_types = 1);

namespace App\Controller;

use App\Form\PostType;
use App\Form\UpdateType;
use App\Form\UserType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Entity\Post;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class AdministrationController extends Controller
{

    /**
     * @Route("/admin-page", name="admin_page")
     */
    public function showPage()
    {
        return $this->render('admin/admin-page.html.twig');
    }

    /**
     * @Security("is_granted('ROLE_MODERATOR', 'ROLE_ADMIN')")
     * @Route("/admin-page-posts", name="admin_page_posts")
     */
    public function showPosts(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $sql = "SELECT p FROM App\Entity\Post p";
        $query = $em->createQuery($sql);

        $paginator  = $this->get('knp_paginator');

        $post = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('admin/admin-page-posts.html.twig', ['post' =>$post]);
    }

    /**
     * @Route("/admin-page-posts/delete/{id}", name="admin_delete_post")
     * @Method({"DELETE"})
     * @Security("is_granted('ROLE_MODERATOR', 'ROLE_ADMIN')")
     */
    public function deletePost(Request $request, $id)
    {
        $post = $this->getDoctrine()->getRepository(Post::class)->find($id);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($post);
        $entityManager->flush();

        $response = new Response();
        $response->send();
    }

    /**
     * @Security("is_granted('ROLE_MODERATOR', 'ROLE_ADMIN')")
     * @Route("/admin-page-posts/blocked/{id}", name="admin_blocked_posts")
     */
    public function blockedPosts(Request $request, $id)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $blockPost = $this->getDoctrine()->getRepository(Post::class)->find($id);

        $blockPost->setIsActive(0);
        $entityManager->persist($blockPost);
        $entityManager->flush();

        $postdata = $this->getDoctrine()
            ->getRepository(Post::class)->findAll();

        $paginator  = $this->get('knp_paginator');

        $post = $paginator->paginate(
            $postdata,
            $request->query->getInt('page', 1),
            10
        );
        return $this->render('admin/admin-page-posts.html.twig', ['post' =>$post]);
    }

    /**
     * @Security("is_granted('ROLE_MODERATOR', 'ROLE_ADMIN')")
     * @Route("/admin-page-posts/unblocked/{id}", name="admin_unblocked_posts")
     */
    public function unblockedPosts(Request $request, $id)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $unblockPost = $this->getDoctrine()->getRepository(Post::class)->find($id);

        $unblockPost->setIsActive(1);
        $entityManager->persist($unblockPost);
        $entityManager->flush();

        $postdata = $this->getDoctrine()
            ->getRepository(Post::class)->findAll();

        $paginator  = $this->get('knp_paginator');

        $post = $paginator->paginate(
            $postdata,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('admin/admin-page-posts.html.twig', ['post' =>$post]);
    }

    /**
     * @Security("is_granted('ROLE_MODERATOR', 'ROLE_ADMIN')")
     * @Route("/admin-page-posts/edit/{id}", name="admin_edit_posts")
     */
    public function editPost(Request $request, $id)
    {

        $entityManager = $this->getDoctrine()->getManager();

        $post = $this->getDoctrine()->getRepository(Post::class)->find($id);

        $form = $this->createForm(UpdateType::class, $post);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {

            $image = $form->get('image')->getData();

            if ($image instanceof UploadedFile) {
                $imageName = $this->generateUniqueFileName().'.'.$image->guessExtension();

                try {
                    $image->move(
                        $this->getParameter('uploads_images'),
                        $imageName
                    );
                    //todo: delete current image
                } catch (FileException $e) {
                    return new Response('<html><body>Error!</body></html>');
                }

                $post->setImage($imageName);
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();

            $postdata = $this->getDoctrine()
                ->getRepository(Post::class)->findAll();

            $paginator  = $this->get('knp_paginator');

            $post = $paginator->paginate(
                $postdata,
                $request->query->getInt('page', 1),
                10
            );
            return $this->redirectToRoute('admin_page_posts', ['post' =>$post]);
        }

        return $this->render('admin/admin-page-edit-posts.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @return string
     */
    private function generateUniqueFileName()
    {

        return md5(uniqid());
    }

    /**
     * @Security("is_granted('ROLE_MODERATOR', 'ROLE_ADMIN')")
     * @Route("/admin-page-users", name="admin_page_users")
     */
    public function showUsers(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $sql = "SELECT u FROM App\Entity\User u";
        $query = $em->createQuery($sql);

        $paginator  = $this->get('knp_paginator');

        $user = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('admin/admin-page-users.html.twig', ['user' =>$user]);
    }

    /**
     * @Route("/admin-page-users/delete/{id}", name="admin_delete_user")
     * @Method({"DELETE"})
     * @Security("is_granted('ROLE_MODERATOR', 'ROLE_ADMIN')")
     */
    public function deleteUser(Request $request, $id)
    {
        $user = $this->getDoctrine()->getRepository(User::class)->find($id);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($user);
        $entityManager->flush();

        $response = new Response();
        $response->send();

    }

    /**
     * @Security("is_granted('ROLE_MODERATOR', 'ROLE_ADMIN')")
     * @Route("/admin-page-users/blocked/{id}", name="admin_blocked_users")
     */
    public function blockedUsers(Request $request, $id)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $blockUser = $this->getDoctrine()->getRepository(User::class)->find($id);

        $blockUser->setIsActive(0);
        $entityManager->persist($blockUser);
        $entityManager->flush();

        $userdata = $this->getDoctrine()
            ->getRepository(User::class)->findAll();

        $paginator  = $this->get('knp_paginator');

        $user = $paginator->paginate(
            $userdata,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('admin/admin-page-users.html.twig', ['user' =>$user]);
    }

    /**
     * @Security("is_granted('ROLE_MODERATOR', 'ROLE_ADMIN')")
     * @Route("/admin-page-users/unblocked/{id}", name="admin_unblocked_users")
     */
    public function unblockedUsers(Request $request, $id)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $unblockUser = $this->getDoctrine()->getRepository(User::class)->find($id);

        $unblockUser->setIsActive(1);
        $entityManager->persist($unblockUser);
        $entityManager->flush();

        $userdata = $this->getDoctrine()
            ->getRepository(User::class)->findAll();

        $paginator  = $this->get('knp_paginator');

        $user = $paginator->paginate(
            $userdata,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('admin/admin-page-users.html.twig', ['user' =>$user]);
    }

    /**
     * @Security("is_granted('ROLE_MODERATOR', 'ROLE_ADMIN')")
     * @Route("/admin-page-users/edit/{id}", name="admin_edit_users")
     */
    public function editUsers(Request $request, $id) {

        $entityManager = $this->getDoctrine()->getManager();

        $updateUser = $this->getDoctrine()->getRepository(User::class)->find($id);

        $form = $this->createFormBuilder($updateUser)
            ->add('email', EmailType::class)
            ->add('username', TextType::class, ['label' => 'Login',
                'attr' => ['class' =>'simple']])
            ->add('firstname', TextType::class)
            ->add('lastname', TextType::class,[
                'constraints' =>[
                    new Callback([
                        'callback' => function($lastname, ExecutionContextInterface $context){
                            $firstname = $context->getRoot()->get('firstname')->getData();
                            if($firstname == $lastname){
                                $context->buildViolation('Firstname and lastname mustn`t be same. ')
                                    ->atPath('lastname')
                                    ->addViolation();
                            }
                        }
                    ])
                ]
            ])
            ->add('plainPassword', RepeatedType::class, array(
                'type' => PasswordType::class,
                'first_options'  => array('label' => 'Password'),
                'second_options' => array('label' => 'Repeat Password'),
            ))
            ->add('user_description', TextareaType::class, ['label' => 'About me: ', 'attr' => ['cols' => '50', 'rows' => '4']])
            ->getForm();

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($updateUser);
            $entityManager->flush();
            $userdata = $this->getDoctrine()
                ->getRepository(User::class)->findAll();

            $paginator  = $this->get('knp_paginator');

            $user = $paginator->paginate(
                $userdata,
                $request->query->getInt('page', 1),
                10
            );
            return $this->redirectToRoute('admin_page_users', ['user' =>$user]);
        }

        return $this->render('admin/admin-page-edit.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Security("is_granted('ROLE_MODERATOR', 'ROLE_ADMIN')")
     * @Route("/posts-admin/blocked/{id}", name="admin_block_posts")
     */
    public function blockedPostsAllPosts(Request $request, $id)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $blockPost = $this->getDoctrine()->getRepository(Post::class)->find($id);

        $blockPost->setIsActive(0);
        $entityManager->persist($blockPost);
        $entityManager->flush();

        $postdata = $this->getDoctrine()
            ->getRepository(Post::class)->findAll();

        $paginator  = $this->get('knp_paginator');

        $post = $paginator->paginate(
            $postdata,
            $request->query->getInt('page', 1),
            10
        );
        return $this->render('posts/admin-posts.html.twig', ['post' =>$post]);
    }

    /**
     * @Security("is_granted('ROLE_MODERATOR', 'ROLE_ADMIN')")
     * @Route("/posts-admin/unblocked/{id}", name="admin_unblock_posts")
     */
    public function unblockedPostsAllPosts(Request $request, $id)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $unblockPost = $this->getDoctrine()->getRepository(Post::class)->find($id);

        $unblockPost->setIsActive(1);
        $entityManager->persist($unblockPost);
        $entityManager->flush();

        $postdata = $this->getDoctrine()
            ->getRepository(Post::class)->findAll();

        $paginator  = $this->get('knp_paginator');

        $post = $paginator->paginate(
            $postdata,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('posts/admin-posts.html.twig', ['post' =>$post]);
    }

    /**
     * @Security("is_granted('ROLE_MODERATOR', 'ROLE_ADMIN')")
     * @Route("/admin-page-users/make-blogger/{id}", name="make_blogger")
     */
    public function makeBlogger(Request $request, $id)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $blockUser = $this->getDoctrine()->getRepository(User::class)->find($id);

        $blockUser->setRoles(['ROLE_BLOGGER']);
        $entityManager->flush();

        return $this->redirectToRoute('admin_page_users');
    }
}