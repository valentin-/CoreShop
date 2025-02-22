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

namespace CoreShop\Component\Core\Product;

use CoreShop\Component\Address\Model\AddressInterface;
use CoreShop\Component\Core\Provider\DefaultTaxAddressProviderInterface;
use CoreShop\Component\Core\Taxation\TaxApplicatorInterface;
use CoreShop\Component\Order\Calculator\PurchasableCalculatorInterface;
use CoreShop\Component\Order\Model\PurchasableInterface;
use CoreShop\Component\Taxation\Calculator\TaxCalculatorInterface;

class TaxedProductPriceCalculator implements TaxedProductPriceCalculatorInterface
{
    public function __construct(private PurchasableCalculatorInterface $purchasableCalculator, private DefaultTaxAddressProviderInterface $defaultTaxAddressProvider, private ProductTaxCalculatorFactoryInterface $taxCalculatorFactory, private TaxApplicatorInterface $taxApplicator)
    {
    }

    public function getPrice(PurchasableInterface $product, array $context, bool $withTax = true): int
    {
        $price = $this->purchasableCalculator->getPrice($product, $context, true);
        $taxCalculator = $this->getTaxCalculator($product, $context);

        if ($taxCalculator instanceof TaxCalculatorInterface) {
            return $this->taxApplicator->applyTax($price, $context, $taxCalculator, $withTax);
        }

        return $price;
    }

    public function getDiscountPrice(PurchasableInterface $product, array $context, bool $withTax = true): int
    {
        $price = $this->purchasableCalculator->getDiscountPrice($product, $context);

        $taxCalculator = $this->getTaxCalculator($product, $context);

        if ($taxCalculator instanceof TaxCalculatorInterface) {
            return $this->taxApplicator->applyTax($price, $context, $taxCalculator, $withTax);
        }

        return $price;
    }

    public function getDiscount(PurchasableInterface $product, array $context, bool $withTax = true): int
    {
        $price = $this->purchasableCalculator->getPrice($product, $context);
        $discount = $this->purchasableCalculator->getDiscount($product, $context, $price);
        $taxCalculator = $this->getTaxCalculator($product, $context);

        if ($taxCalculator instanceof TaxCalculatorInterface) {
            return $this->taxApplicator->applyTax($discount, $context, $taxCalculator, $withTax);
        }

        return $discount;
    }

    public function getRetailPrice(PurchasableInterface $product, array $context, bool $withTax = true): int
    {
        $price = $this->purchasableCalculator->getRetailPrice($product, $context);

        $taxCalculator = $this->getTaxCalculator($product, $context);

        if ($taxCalculator instanceof TaxCalculatorInterface) {
            return $this->taxApplicator->applyTax($price, $context, $taxCalculator, $withTax);
        }

        return $price;
    }

    protected function getTaxCalculator(PurchasableInterface $product, array $context): ?TaxCalculatorInterface
    {
        return $this->taxCalculatorFactory->getTaxCalculator($product, $this->getDefaultAddress($context));
    }

    protected function getDefaultAddress($context): ?AddressInterface
    {
        return $this->defaultTaxAddressProvider->getAddress($context);
    }
}
