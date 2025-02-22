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

namespace CoreShop\Component\Index\Filter;

use CoreShop\Component\Index\Condition\RangeCondition;
use CoreShop\Component\Index\Listing\ListingInterface;
use CoreShop\Component\Index\Model\FilterConditionInterface;
use CoreShop\Component\Index\Model\FilterInterface;
use Pimcore\Model\DataObject\QuantityValue\Unit;
use Symfony\Component\HttpFoundation\ParameterBag;

class RangeFilterConditionProcessor implements FilterConditionProcessorInterface
{
    public function prepareValuesForRendering(FilterConditionInterface $condition, FilterInterface $filter, ListingInterface $list, array $currentFilter): array
    {
        $field = $condition->getConfiguration()['field'];
        $rawValues = $list->getGroupByValues($field, true);

        $minValue = count($rawValues) > 0 ? $rawValues[0]['value'] : 0;
        $maxValue = count($rawValues) > 0 ? $rawValues[count($rawValues) - 1]['value'] : 0;

        $currentValueMin = $minValue;
        if (isset($currentFilter["$field-min"]) && ('' !== (string)$currentFilter["$field-min"])) {
            $currentValueMin = $currentFilter["$field-min"];
        }

        $currentValueMax = $maxValue;
        if (isset($currentFilter["$field-max"]) && ('' !== (string)$currentFilter["$field-max"])) {
            $currentValueMax = $currentFilter["$field-max"];
        }

        return [
            'type' => 'range',
            'label' => $condition->getLabel(),
            'minValue' => $minValue,
            'maxValue' => $maxValue,
            'currentValueMin' => $currentValueMin,
            'currentValueMax' => $currentValueMax,
            'values' => array_values($rawValues),
            'fieldName' => $field,
            'stepCount' => $condition->getConfiguration()['stepCount'],
            'quantityUnit' => $condition->getQuantityUnit() ? Unit::getById((string)$condition->getQuantityUnit()) : null,
        ];
    }

    public function addCondition(FilterConditionInterface $condition, FilterInterface $filter, ListingInterface $list, array $currentFilter, ParameterBag $parameterBag, bool $isPrecondition = false): array
    {
        $field = $condition->getConfiguration()['field'];

        if ($parameterBag->has($field)) {
            $values = explode(',', $parameterBag->get($field));

            $parameterBag->set($field . '-min', $values[0]);
            $parameterBag->set($field . '-max', $values[1]);
        }

        $valueMin = $parameterBag->get($field . '-min');
        $valueMax = $parameterBag->get($field . '-max');

        if (null === $valueMax) {
            $valueMax = $condition->getConfiguration()['preSelectMax'];
        }

        if ($valueMax === static::EMPTY_STRING) {
            $valueMax = null;
        }

        if (null === $valueMin) {
            $valueMin = $condition->getConfiguration()['preSelectMin'];
        }

        if ($valueMin === static::EMPTY_STRING) {
            $valueMin = null;
        }

        $currentFilter[$field . '-min'] = $valueMin;
        $currentFilter[$field . '-max'] = $valueMax;

        if (null !== $valueMin && null !== $valueMax) {
            $fieldName = $field;

            if ($isPrecondition) {
                $fieldName = 'PRECONDITION_' . $fieldName;
            }

            $list->addCondition(new RangeCondition($field, (float)$valueMin, (float)$valueMax), $fieldName);
        }

        return $currentFilter;
    }
}
