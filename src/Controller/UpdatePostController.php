<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 28.11.2018
 * Time: 16:07
 */

declare(strict_types = 1);

namespace App\Controller;

use App\Entity\User;
use App\Entity\Post;
use App\Form\PostType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\Request;
use App\Form\UserType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\File;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class UpdatePostController extends AbstractController
{

    /**
     * @Route("/my-posts/edit/{id}", name="edit_post")
     */
    public function editPost(Request $request, $id)
    {

        $entityManager = $this->getDoctrine()->getManager();

        $post = new Post();

        $updatePost = $this->getDoctrine()->getRepository(Post::class)->find($id);

        $form = $this->createFormBuilder($updatePost)
            ->add('title', TextType::class)
            ->add('description', TextType::class)
            ->add('content', TextareaType::class, ['attr' => ['cols' => '50', 'rows' => '7']])
            //->add('image', FileType::class)
            ->getForm();

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {

          /*  $image = $post->getImage();
            $imageName = $this->generateUniqueFileName().'.'.$image->guessExtension();

            try {
                $image->move(
                    $this->getParameter('uploads_images'),
                    $imageName
                );
            } catch (FileException $e) {
                return new Response('<html><body>Error!</body></html>');
            }

            $post->setImage($imageName);*/
            $entityManager->persist($updatePost);
            $entityManager->flush();
            return $this->redirectToRoute('my_post');
        }

        return $this->render('posts/edit-post.html.twig', array(
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
     * @Route("/my-posts/delete/{id}", name="delete_post")
     * @Method({"DELETE"})
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
}