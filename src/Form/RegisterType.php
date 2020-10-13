<?php

namespace App\Form;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegisterType extends AbstractType
{
    /** @var UserRepository */
    private $repository;

    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', TextType::class, [
                'required' => true,
                'label' => 'Имя пользователя',
            ])
            ->add('email', EmailType::class, [
                'required' => true,
                'label' => 'Емаил',
            ])
            ->add('password', PasswordType::class, [
                'required' => true,
                'label' => 'Пароль',
            ])
            ->add('repeatPassword', PasswordType::class, [
                'required' => true,
                'label' => 'Повторите пароль',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Зарегистрироватся',
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

            $usernameUser = $this->repository->findOneByUsername($user->getUsername());
            $emailUser = $this->repository->findOneByEmail($user->getEmail());

            if ($usernameUser) {
                $event->getForm()->get('username')->addError(new FormError('Это имя пользователя уже занято'));
            }

            if ($emailUser) {
                $event->getForm()->get('email')->addError(new FormError('Этот емаил уже занят'));
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