<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NewPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('password', PasswordType::class, [
                'required' => true,
                'label' => false,
                'attr' => [
                    'placeholder' => 'Новый пароль'
                ]
            ])
            ->add('repeatPassword', PasswordType::class, [
                'required' => true,
                'label' => false,
                'attr' => [
                    'placeholder' => 'Повторите пароль'
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Сменить',
                'attr' => [
                    'class' => 'btn btn-block btn-success text-center'
                ]
            ]);

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            /** @var User $user */
            $user = $event->getData();

            if ($user->getPassword() != $user->getRepeatPassword()) {
                $event->getForm()->get('password')->addError(new FormError('Пароли должны быть одинаковыми!'));
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefault('data_class', User::class);
        $resolver->setDefault('empty_data', new User());
    }
}