<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Form;

use App\Paying\Dto\Payment\PaymentFinalizeRequestDto;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Builds the form used to finalize a payment through the web console surface.
 */
final class PaymentFinalizeType extends AbstractType
{
    /**
     * Defines the form fields exposed by this form type.
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('provider', ChoiceType::class, [
                'label' => 'Provider',
                'choices' => [
                    'Internal' => 'internal',
                    'Stripe' => 'stripe',
                ],
                'placeholder' => 'Choose provider',
            ])
            ->add('providerRef', TextType::class, [
                'label' => 'Provider ref',
                'required' => false,
                'help' => 'Optional upstream reference shown by the provider.',
            ])
            ->add('gatewayTransactionId', TextType::class, [
                'label' => 'Gateway transaction ID',
                'required' => false,
                'help' => 'Gateway transaction identifier if one exists.',
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
                'help' => 'Leave empty to accept the provider result.',
            ]);
    }

    /**
     * Registers the DTO binding and baseline form options for this type.
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'data_class' => PaymentFinalizeRequestDto::class,
            'csrf_protection' => true,
        ]);
    }
}
