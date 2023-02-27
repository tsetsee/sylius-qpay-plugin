<?php

declare(strict_types=1);

namespace Tsetsee\SyliusQpayPlugin\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

final class QpayGatewayConfigurationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class)
            ->add('password', TextType::class)
            ->add('env', ChoiceType::class, [
                'choices' => [
                    'Sandbox' => 'sandbox',
                    'Production' => 'prod',
                ],
            ])
            ->add('invoiceCode', TextType::class)
        ;
    }
}
