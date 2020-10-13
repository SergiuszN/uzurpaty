<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Post;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdminPostReviewType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'disabled' => true,
                'required' => true,
                'label' => 'Название',
            ])
            ->add('country', CountryType::class, [
                'disabled' => true,
                'required' => true,
                'label' => 'Страна',
            ])
            ->add('category', EntityType::class, [
                'disabled' => true,
                'class' => Category::class,
                'choice_label' => 'name',
                'label' => 'Категория',
            ])
            ->add('preview', CKEditorType::class, [
                'disabled' => true,
                'required' => true,
                'label' => 'Превью',
            ])
            ->add('content', CKEditorType::class, [
                'disabled' => true,
                'required' => true,
                'label' => 'Контент',
            ])
            ->add('note', TextareaType::class, [
                'required' => false,
                'label' => 'Заметка',
            ])
            ->add('approve', SubmitType::class, [
                'label' => 'Опубликовать',
                'attr' => [
                    'class' => 'btn btn-lg btn-success float-right'
                ]
            ])
            ->add('reject', SubmitType::class, [
                'label' => 'Отклонить',
                'attr' => [
                    'class' => 'btn btn-lg btn-danger float-left'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefault('data_class', Post::class);
        $resolver->setDefault('empty_data', new Post());
    }
}