<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Form;

use App\Paying\Dto\Payment\PaymentConsoleFinalizeRequestDto;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Builds the operator console form for manual payment finalization requests.
 */
final class PaymentConsoleFinalizeType extends AbstractType
{
    /**
     * Defines the form fields exposed by this form type.
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('paymentId', TextType::class, [
                'label' => 'Payment ID',
                'help' => 'Identifier of the payment to finalize.',
            ])
            ->add('provider', ChoiceType::class, [
                'label' => 'Provider',
                'choices' => [
                    'Internal' => 'internal',
                    'Stripe' => 'stripe',
                    'PayPal' => 'paypal',
                ],
                'placeholder' => 'Choose provider',
            ])
            ->add('providerRef', TextType::class, [
                'label' => 'Provider ref',
                'required' => false,
                'help' => 'Optional upstream provider reference.',
            ])
            ->add('providerTransactionId', TextType::class, [
                'label' => 'Provider transaction ID',
                'required' => false,
                'help' => 'Optional transaction identifier from the provider.',
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
                'help' => 'Leave empty to use the provider result.',
            ]);
    }

    /**
     * Registers the DTO binding and baseline form options for this type.
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'data_class' => PaymentConsoleFinalizeRequestDto::class,
            'csrf_protection' => true,
        ]);
    }
}
