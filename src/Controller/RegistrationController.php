<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 18.11.2018
 * Time: 14:17
 */

declare(strict_types = 1);

namespace App\Controller;

use App\Form\UserType;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class RegistrationController extends AbstractController
{

    private $mailer;

    public function __construct(\Swift_Mailer $mailer)
    {
        $this->mailer = $mailer;
    }


    /**
     * @Route("/register", name="user_registration")
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $password = $passwordEncoder->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($password);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            $firstname_form = $form->get("firstname")->getData();
            $lastname_form = $form->get("lastname")->getData();
            $email_form = $form->get("email")->getData();
            $chech = $form->get("blogger")->getData();

            $message = (new \Swift_Message('Hello Email'))
                ->setFrom('blog@example.com')
                ->setTo($email_form)
                ->setBody(
                    $this->renderView(
                        'emails/registration.html.twig',
                        array('firstname' => $firstname_form,
                            'lastname' => $lastname_form)
                    ),
                    'text/html'
                );

            $this->mailer->send($message);

            if($chech == true) {
                $message = (new \Swift_Message('Hello Email'))
                    ->setFrom($email_form)
                    ->setTo(['mod1@mail.ru', 'mod2@mail.ru'])
                    ->setBody(
                        $this->renderView(
                            'emails/be-blogger.html.twig',
                            array('firstname' => $firstname_form,
                                'lastname' => $lastname_form,
                                'email' => $email_form)
                        ),
                        'text/html'
                    );

                $this->mailer->send($message);
            }

            return $this->redirectToRoute('login');
        }

        return $this->render(
            'registration/register.html.twig',
            array('form' => $form->createView())
        );
    }

}