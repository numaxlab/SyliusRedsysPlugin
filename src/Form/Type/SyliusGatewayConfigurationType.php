<?php

declare(strict_types=1);

namespace Eclyptox\SyliusRedsysPlugin\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

final class SyliusGatewayConfigurationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('sandbox', CheckboxType::class)
            ->add('pay_methods', ChoiceType::class, [
                'choices' => [
                    'Con tarxeta' => 'C',
                    'Bizum' => 'z'
                ]
            ])
            ->add('merchant_code', TextType::class)
            ->add('terminal', NumberType::class)
            ->add('secret_key', TextType::class)
            ->add('consumer_language', ChoiceType::class, [
                'choices' => [
                    'Español' => '001',
                    'English' => '002',
                    'Catalá' => '003',
                    'Français' => '004',
                    'Deutsch' => '005',
                    'Nederlands' => '006',
                    'Italiano' => '007',
                    'Svenska' => '008',
                    'Português' => '009',
                    'Valencià' => '010',
                    'Polski' => '011',
                    'Galego' => '012',
                    'Euskara' => '013'
                ]
            ]);
    }
}
