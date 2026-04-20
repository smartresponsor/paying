<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Form;

use App\Paying\Controller\Dto\PaymentCreateRequestDto;
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
            ->add('orderId', TextType::class, ['label' => 'Order ID'])
            ->add('amountMinor', IntegerType::class, ['label' => 'Amount minor'])
            ->add('currency', CurrencyType::class, ['label' => 'Currency']);
    }

    /**
     * Registers the DTO binding and baseline form options for this type.
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PaymentCreateRequestDto::class,
            'csrf_protection' => true,
        ]);
    }
}
