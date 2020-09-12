<?php


namespace App\Form;


use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdminUserEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', TextType::class, [
                'required' => true,
                'label' => 'Имя пользователя'
            ])
            ->add('email', EmailType::class, [
                'required' => true,
                'label' => 'E-mail'
            ])
            ->add('roles', ChoiceType::class, [
                'required' => true,
                'label' => 'Роль',
                'choices' => [
                    'Администратор' => User::ROLE_ADMIN,
                    'Модератор' => User::ROLE_MODER,
                    'Автор' => User::ROLE_AUTHOR,
                    'Пользователь' => User::ROLE_USER
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefault('data_class', User::class);
        $resolver->setDefault('empty_data', new User());
    }
}