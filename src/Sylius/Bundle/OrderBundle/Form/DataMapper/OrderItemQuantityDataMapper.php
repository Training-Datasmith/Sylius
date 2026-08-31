<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Sylius Sp. z o.o.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Sylius\Bundle\OrderBundle\Form\DataMapper;

use Doctrine\Common\Collections\ArrayCollection;
use Sylius\Component\Order\Modifier\OrderItemQuantityModifierInterface;
use Symfony\Component\Form\DataMapperInterface;

/**
 * @internal
 */
class OrderItemQuantityDataMapper implements DataMapperInterface
{
    public function __construct(
        private readonly OrderItemQuantityModifierInterface $orderItemQuantityModifier,
        private readonly DataMapperInterface $propertyPathDataMapper,
    ) {
    }

    public function mapDataToForms($viewData, $forms): void
    {
        $this->propertyPathDataMapper->mapDataToForms($viewData, $forms);
    }

    public function mapFormsToData(\Traversable $forms, mixed &$viewData): void
    {
        if (!$viewData instanceof \Sylius\Component\Order\Model\OrderItemInterface) {
            throw new \InvalidArgumentException(sprintf('$viewData must be an instance of %s.', \Sylius\Component\Order\Model\OrderItemInterface::class));
        }
        $formsOtherThanQuantity = [];
        foreach ($forms as $form) {
            if ('quantity' === $form->getName()) {
                $targetQuantity = $form->getData();
                $this->orderItemQuantityModifier->modify($viewData, (int) $targetQuantity);

                continue;
            }

            $formsOtherThanQuantity[] = $form;
        }

        if (!empty($formsOtherThanQuantity)) {
            $this->propertyPathDataMapper->mapFormsToData(new ArrayCollection($formsOtherThanQuantity), $viewData);
        }
    }
}
