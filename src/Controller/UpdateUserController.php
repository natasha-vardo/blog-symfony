<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 24.11.2018
 * Time: 19:41
 */

declare(strict_types = 1);

namespace App\Controller;

use App\Entity\User;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\HttpFoundation\Request;
use App\Form\UserType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class UpdateUserController extends AbstractController
{

    /**
     * @Route("/my-profile", name="edit_my_profile")
     */
    public function edit(Request $request, TokenStorageInterface $tokenStorage) {

        $entityManager = $this->getDoctrine()->getManager();
        $userId = $tokenStorage->getToken()->getUser()->getId();
        $id = (int)$userId;

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
            //->add('password', TextType::class)
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
            return $this->redirectToRoute('edit_my_profile');
        }

        return $this->render('users/edit-my-profile.html.twig', array(
            'form' => $form->createView()
        ));
    }
}