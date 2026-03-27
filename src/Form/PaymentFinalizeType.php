<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Form;

use App\Controller\Dto\PaymentFinalizeRequestDto;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class PaymentFinalizeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('provider', ChoiceType::class, [
                'label' => 'Provider',
                'choices' => [
                    'Internal' => 'internal',
                    'Stripe' => 'stripe',
                ],
            ])
            ->add('providerRef', TextType::class, [
                'label' => 'Provider ref',
                'required' => false,
            ])
            ->add('gatewayTransactionId', TextType::class, [
                'label' => 'Gateway transaction ID',
                'required' => false,
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'Finalize status',
                'required' => false,
                'placeholder' => 'Provider default',
                'choices' => [
                    'Completed' => 'completed',
                    'Failed' => 'failed',
                    'Refunded' => 'refunded',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PaymentFinalizeRequestDto::class,
            'csrf_protection' => true,
        ]);
    }
}
