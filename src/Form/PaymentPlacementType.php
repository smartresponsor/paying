<?php

declare(strict_types=1);

namespace App\Paying\Form;

use App\Paying\Dto\Payment\PaymentPlacementFormData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CurrencyType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class PaymentPlacementType extends AbstractType
{
    /** @param array<string, mixed> $options */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('amount', MoneyType::class, [
                'currency' => false,
                'scale' => 2,
                'divisor' => 1,
                'disabled' => true,
            ])
            ->add('currency', CurrencyType::class, [
                'disabled' => true,
            ])
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
            'data_class' => PaymentPlacementFormData::class,
            'csrf_protection' => true,
        ]);
    }
}
