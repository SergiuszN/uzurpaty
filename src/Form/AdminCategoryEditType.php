<?php


namespace App\Form;


use App\Entity\Category;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdminCategoryEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'required' => true,
                'label' => 'Название'
            ])
            ->add('active', ChoiceType::class, [
                'required' => true,
                'label' => 'Статус',
                'choices' => [
                    'Активна' => Category::STATUS_ACTIVE,
                    'Скрыта' => Category::STATUS_DISABLE
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Добавить',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefault('data_class', Category::class);
        $resolver->setDefault('empty_data', new Category());
    }

}