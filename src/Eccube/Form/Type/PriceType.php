<?php
/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) 2000-2015 LOCKON CO.,LTD. All Rights Reserved.
 *
 * http://www.lockon.co.jp/
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */


namespace Eccube\Form\Type;

use Eccube\Common\EccubeConfig;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Intl\Intl;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;

class PriceType extends AbstractType
{
    /**
     * @var EccubeConfig
     */
    protected $eccubeConfig;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * PriceType constructor.
     *
     * @param EccubeConfig $eccubeConfig
     */
    public function __construct(EccubeConfig $eccubeConfig, ContainerInterface $container)
    {
        $this->eccubeConfig = $eccubeConfig;
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $currency = $this->container->getParameter('currency_code');
        $scale = Intl::getCurrencyBundle()->getFractionDigits($currency);
        $max = $this->eccubeConfig['price_max'];
        $min = -$max;

        $constraints = function (Options $options) use ($max, $min) {
            $constraints = [];
            // requiredがtrueに指定されている場合, NotBlankを追加
            if (isset($options['required']) && true === $options['required']) {
                $constraints[] = new NotBlank();
            }

            if (isset($options['accept_minus']) && true === $options['accept_minus']) {
                $constraints[] = new Range([
                    'min' => $min,
                    'max' => $max
                ]);
            } else {
                $constraints[] = new Range(['min' => 0, 'max' => $max]);
            }

            return $constraints;
        };

        $resolver->setDefaults(
            [
                'currency' => $currency,
                'scale' => $scale,
                'grouping' => true,
                'constraints' => $constraints,
                'accept_minus' => false, // マイナス値を許容するかどうか
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return MoneyType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'price';
    }
}
