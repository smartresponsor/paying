<?php

# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Form;

use App\Paying\Dto\Payment\PaymentCreateRequestDto;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CurrencyType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Builds the form used to create a payment aggregate from interactive tooling.
 */
final class PaymentCreateType extends AbstractType
{
    /**
     * Defines the form fields exposed by this form type.
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('orderId', TextType::class, [
                'label' => 'Order ID',
                'help' => 'Target order identifier to attach the payment to.',
            ])
            ->add('amountMinor', IntegerType::class, [
                'label' => 'Amount minor',
                'help' => 'Minor-unit amount, e.g. 1250 for 12.50.',
            ])
            ->add('currency', CurrencyType::class, [
                'label' => 'Currency',
                'help' => 'ISO 4217 currency code.',
            ]);
    }

    /**
     * Registers the DTO binding and baseline form options for this type.
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'data_class' => PaymentCreateRequestDto::class,
            'csrf_protection' => true,
        ]);
    }
}
