<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 28.11.2018
 * Time: 16:07
 */

declare(strict_types = 1);

namespace App\Controller;

use App\Entity\Post;
use App\Form\UpdateType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class UpdatePostController extends AbstractController
{

    /**
     * @Route("/my-posts/edit/{id}", name="edit_post")
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
                    //dump($image->temp);
                    //exit();
                    //$filename = $media->getName();

                    //$filesystem = new Filesystem();
                    //$filesystem->remove($image);
                    /*if  ( isset ($image))  {
                        $entityManager->remove($post->getImage());
                    }*/

                      if(file_exists($image)) {
                        if ($image = $this->getAbsolutePath()) {
                            unlink($image);
                        }
    }
                } catch (FileException $e) {
                    return new Response('<html><body>Error!</body></html>');
                }

                $post->setImage($imageName);
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();
            return $this->redirectToRoute('my_post');
        }

        return $this->render('posts/edit-post.html.twig', array(
            'form' => $form->createView()
        ));
    }

    public function getAbsolutePath()
    {
        return null === $this->logo ? null : $this->getUploadRootDir().'/'.$this->logo;
    }

    protected function getUploadRootDir()
    {
        return __DIR__.'/../../../../public/'.$this->getUploadDir();
    }

    protected function getUploadDir()
    {
        return 'uploads/image';
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