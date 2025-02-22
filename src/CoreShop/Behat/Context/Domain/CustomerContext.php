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

namespace CoreShop\Behat\Context\Domain;

use Behat\Behat\Context\Context;
use CoreShop\Component\Core\Model\CustomerInterface;
use CoreShop\Component\Customer\Context\CustomerContextInterface;
use Webmozart\Assert\Assert;

final class CustomerContext implements Context
{
    public function __construct(private CustomerContextInterface $customerContext)
    {
    }

    /**
     * @Then /^I should be logged in with (email "[^"]+")$/
     */
    public function iShouldBeLoggedInWithEmail(CustomerInterface $customer): void
    {
        Assert::same(
            $customer->getId(),
            $this->customerContext->getCustomer()->getId(),
            sprintf(
                'Given customer (%s) is different from logged in customer (%s)',
                $customer->getEmail(),
                $this->customerContext->getCustomer()->getEmail()
            )
        );
    }

    /**
     * @Then /^It should throw an error deleting the (customer "[^"]+")$/
     */
    public function itShouldThrowAnErrorDeletingCustomer(CustomerInterface $customer): void
    {
        Assert::throws(function () use ($customer) {
            $customer->delete();
        });
    }

    /**
     * @Then /^It should not throw an error deleting the (customer "[^"]+")$/
     */
    public function itShouldNotThrowAnErrorDeletingTheCustomer(CustomerInterface $customer): void
    {
        $customer->delete();
    }
}
