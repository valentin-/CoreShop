<?php
/**
 * CoreShop.
 *
 * This source file is subject to the GNU General Public License version 3 (GPLv3)
 * For the full copyright and license information, please view the LICENSE.md and gpl-3.0.txt
 * files that are distributed with this source code.
 *
 * @copyright  Copyright (c) CoreShop GmbH (https://www.coreshop.org)
 * @license    https://www.coreshop.org/license     GNU General Public License version 3 (GPLv3)
 */

declare(strict_types=1);

namespace CoreShop\Bundle\CoreBundle\Form\Extension;

use CoreShop\Bundle\OrderBundle\Form\Type\CartCreationCartItemType;
use CoreShop\Bundle\ProductBundle\Form\Type\Unit\ProductUnitDefinitionsChoiceType;
use CoreShop\Component\Core\Model\ProductInterface;
use CoreShop\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

final class CartCreationCartItemTypeExtension extends AbstractTypeExtension
{
    public function __construct(private RepositoryInterface $productRepository)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            $product = $data['product'];

            if (!isset($product)) {
                return;
            }

            $product = $this->productRepository->find($product);

            if (!$product instanceof ProductInterface) {
                return;
            }

            if (!$product->hasUnitDefinitions()) {
                return;
            }

            $event->getForm()->add('unitDefinition', ProductUnitDefinitionsChoiceType::class, [
                'product' => $product,
                'required' => false,
                'label' => null,
            ]);
        });
    }

    public static function getExtendedTypes(): iterable
    {
        return [CartCreationCartItemType::class];
    }
}
