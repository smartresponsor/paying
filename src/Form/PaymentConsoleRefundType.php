<?php

declare(strict_types=1);

namespace App\Form;

use App\Controller\Dto\PaymentConsoleRefundRequestDto;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Symfony form used to perform refund operations from the operator console.
 *
 * Maps UI fields to {@see PaymentConsoleRefundRequestDto}.
 */
final class PaymentConsoleRefundType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('paymentId', TextType::class)
            ->add('amount', TextType::class)
            ->add('provider', ChoiceType::class, [
                'choices' => [
                    'Internal' => 'internal',
                    'Stripe' => 'stripe',
                    'PayPal' => 'paypal',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PaymentConsoleRefundRequestDto::class,
        ]);
    }
}
