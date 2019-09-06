<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 18.11.2018
 * Time: 12:52
 */

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
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
            ->add('blogger', CheckboxType::class, [
                'mapped' => false,
                'label' => 'I want to be a blogger',
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => User::class,
        ));
    }
}