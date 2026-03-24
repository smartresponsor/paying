<?php

declare(strict_types=1);

// Marketing America Corp. Oleksandr Tishchenko

namespace App\Form\Payment;

use App\Controller\Payment\Dto\PaymentConsoleRefundRequestDto;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class PaymentConsoleRefundType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('paymentId', TextType::class, ['label' => 'Payment ID'])
            ->add('amount', MoneyType::class, [
                'label' => 'Refund amount',
                'currency' => false,
                'scale' => 2,
                'divisor' => 1,
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
