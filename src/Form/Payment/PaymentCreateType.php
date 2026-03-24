<?php

declare(strict_types=1);

// Marketing America Corp. Oleksandr Tishchenko

namespace App\Form\Payment;

use App\Controller\Payment\Dto\PaymentCreateRequestDto;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CurrencyType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class PaymentCreateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('orderId', TextType::class, ['label' => 'Order ID'])
            ->add('amountMinor', IntegerType::class, ['label' => 'Amount minor'])
            ->add('currency', CurrencyType::class, ['label' => 'Currency']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PaymentCreateRequestDto::class,
            'csrf_protection' => true,
        ]);
    }
}
