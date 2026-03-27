<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Form;

use App\Controller\Dto\PaymentConsoleRefundRequestDto;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class PaymentConsoleRefundType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('paymentId', TextType::class, ['label' => 'Payment ID'])
            ->add('amount', TextType::class, [
                'label' => 'Refund amount',
            ])
            ->add('provider', ChoiceType::class, [
                'label' => 'Provider',
                'choices' => [
                    'Internal' => 'internal',
                    'Stripe' => 'stripe',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PaymentConsoleRefundRequestDto::class,
            'csrf_protection' => true,
        ]);
    }
}
