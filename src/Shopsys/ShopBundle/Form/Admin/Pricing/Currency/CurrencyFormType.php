<?php

namespace Shopsys\ShopBundle\Form\Admin\Pricing\Currency;

use Shopsys\ShopBundle\Form\CurrencyType;
use Shopsys\ShopBundle\Model\Pricing\Currency\CurrencyData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class CurrencyFormType extends AbstractType
{
    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please enter name']),
                    new Constraints\Length(['max' => 50, 'maxMessage' => 'Name cannot be longer than {{ limit }} characters']),
                ],
            ])
            ->add('code', CurrencyType::class, [
                'required' => true,
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please enter currency code']),
                    new Constraints\Length(['max' => 3, 'maxMessage' => 'Currency code cannot be longer than {{ limit }} characters']),
                ],
            ])
            ->add('exchangeRate', NumberType::class, [
                'required' => true,
                'scale' => 6,
                'read_only' => $options['is_default_currency'],
                'constraints' => [
                    new Constraints\NotBlank(['message' => 'Please enter currency exchange rate']),
                    new Constraints\GreaterThan(0),
                ],
            ]);
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired('is_default_currency')
            ->setAllowedTypes('is_default_currency', 'bool')
            ->setDefaults([
                'data_class' => CurrencyData::class,
                'attr' => ['novalidate' => 'novalidate'],
            ]);
    }
}
