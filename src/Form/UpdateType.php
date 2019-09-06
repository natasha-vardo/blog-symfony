<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 03.12.2018
 * Time: 15:08
 */


namespace App\Form;

use App\Entity\Post;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class UpdateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class)
            ->add('description', TextType::class)
            ->add('content', TextareaType::class, ['attr' => ['cols' => '50', 'rows' => '7']])
            ->add('image', FileType::class, ['label' => 'Image', 'mapped' => false, 'constraints'=>[new File(['mimeTypes' => ['image/*']])]])
        ;

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Post::class,
        ));
    }
}