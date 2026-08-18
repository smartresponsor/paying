<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Form;

use App\Paying\Dto\Payment\PaymentStartRequestDto;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CurrencyType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Builds the form used to start a payment through the web console surface.
 */
final class PaymentStartType extends AbstractType
{
    /**
     * Defines the form fields exposed by this form type.
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('orderId', TextType::class, [
                'label' => 'Order ID',
                'help' => 'Order identifier that will be charged.',
            ])
            ->add('amount', MoneyType::class, [
                'label' => 'Amount',
                'currency' => false,
                'scale' => 2,
                'divisor' => 1,
                'help' => 'Requested capture amount in major units.',
            ])
            ->add('currency', CurrencyType::class, [
                'label' => 'Currency',
                'help' => 'ISO 4217 currency code.',
            ])
            ->add('provider', ChoiceType::class, [
                'label' => 'Provider',
                'choices' => [
                    'Internal' => 'internal',
                    'Stripe' => 'stripe',
                    'PayPal' => 'paypal',
                ],
                'placeholder' => 'Choose provider',
            ]);
    }

    /**
     * Registers the DTO binding and baseline form options for this type.
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'data_class' => PaymentStartRequestDto::class,
            'csrf_protection' => true,
        ]);
    }
}
