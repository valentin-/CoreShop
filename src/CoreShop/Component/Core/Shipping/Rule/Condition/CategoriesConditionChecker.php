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

namespace CoreShop\Component\Core\Shipping\Rule\Condition;

use CoreShop\Component\Address\Model\AddressInterface;
use CoreShop\Component\Core\Model\OrderInterface;
use CoreShop\Component\Core\Repository\CategoryRepositoryInterface;
use CoreShop\Component\Core\Rule\Condition\CategoriesConditionCheckerTrait;
use CoreShop\Component\Product\Model\ProductInterface;
use CoreShop\Component\Resource\Model\ResourceInterface;
use CoreShop\Component\Shipping\Model\CarrierInterface;
use CoreShop\Component\Shipping\Model\ShippableInterface;
use CoreShop\Component\Shipping\Rule\Condition\AbstractConditionChecker;

final class CategoriesConditionChecker extends AbstractConditionChecker
{
    use CategoriesConditionCheckerTrait {
        CategoriesConditionCheckerTrait::__construct as private __traitConstruct;
    }

    public function __construct(CategoryRepositoryInterface $categoryRepository)
    {
        $this->__traitConstruct($categoryRepository);
    }

    public function isShippingRuleValid(
        CarrierInterface $carrier,
        ShippableInterface $shippable,
        AddressInterface $address,
        array $configuration
    ): bool {
        if (!$shippable instanceof OrderInterface) {
            return false;
        }

        $cartItems = $shippable->getItems() ?? [];

        $categoryIdsToCheck = $this->getCategoriesToCheck(
            $configuration['categories'],
            $shippable->getStore(),
            $configuration['recursive'] ?: false
        );

        foreach ($cartItems as $item) {
            $product = $item->getProduct();
            if ($product instanceof ProductInterface) {
                $categories = $product->getCategories();

                if (null === $categories) {
                    continue;
                }

                foreach ($categories as $category) {
                    if ($category instanceof ResourceInterface) {
                        if (in_array($category->getId(), $categoryIdsToCheck)) {
                            return true;
                        }
                    }
                }
            }
        }

        return false;
    }
}
