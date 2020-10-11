<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Post;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdminPostCreateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'required' => true,
                'label' => 'Название',
            ])
            ->add('country', CountryType::class, [
                'required' => true,
                'label' => 'Страна',
                'choice_translation_locale' => 'ru',
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'label' => 'Категория',
            ])
            ->add('preview', TextareaType::class, [
                'required' => true,
                'label' => 'Превью',
            ])
            ->add('content', TextareaType::class, [
                'required' => true,
                'label' => 'Контент',
            ])
            ->add('status', ChoiceType::class, [
                'required' => true,
                'label' => 'Статус',
                'choices' => [
                    'Новый' => Post::STATUS_NEW,
                    'Ожидает' => Post::STATUS_AWAIT,
                    'Требуется исправление' => Post::STATUS_DECLINED,
                    'Опубликован' => Post::STATUS_POSTED,
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Создать',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefault('data_class', Post::class);
        $resolver->setDefault('empty_data', new Post());
    }
}