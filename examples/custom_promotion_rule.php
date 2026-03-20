<?php

declare(strict_types=1);

/**
 * Example: Implementing a custom Sylius promotion rule
 *
 * Tags the service as `sylius.promotion_rule_checker` so Sylius recognises it
 * as an eligibility condition that can be configured in the admin UI.
 *
 * Scenario: A promotion that is only eligible on orders whose item count >= N.
 *
 * Register in services.yaml:
 *
 *   App\Promotion\Rule\MinimumItemCountRuleChecker:
 *       tags:
 *           - { name: sylius.promotion_rule_checker, type: minimum_item_count }
 */

namespace App\Promotion\Rule;

use Sylius\Component\Promotion\Checker\Rule\RuleCheckerInterface;
use Sylius\Component\Promotion\Model\PromotionSubjectInterface;

final class MinimumItemCountRuleChecker implements RuleCheckerInterface
{
    public const TYPE = 'minimum_item_count';

    /**
     * Checks whether the promotion subject (order) meets the minimum item count threshold.
     *
     * @param PromotionSubjectInterface $subject    The order being evaluated
     * @param array<string, mixed>      $configuration Rule configuration, expects key 'count' (int)
     *
     * @return bool True when the order's total item count is >= the configured minimum
     */
    public function isEligible(PromotionSubjectInterface $subject, array $configuration): bool
    {
        $minimumCount = (int) ($configuration['count'] ?? 1);

        return $subject->getTotalQuantity() >= $minimumCount;
    }

    /**
     * Returns the human-readable name shown in the Sylius admin promotion form.
     *
     * @return string Localisation key or English fallback label
     */
    public function getConfigurationFormType(): string
    {
        return \App\Form\Type\MinimumItemCountConfigurationType::class;
    }
}
