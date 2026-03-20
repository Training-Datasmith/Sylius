<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Sylius Sp. z o.o.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare (strict_types=1);
namespace Sylius\Behat\Behaviour;

use Behat\Mink\Element\Node_Element;
use Behat\Mink\Exception\Element_Not_Found_Exception;
trait Counts_Channel_Based_Errors
{
    use Session_Accessor;
    /** @throws ElementNotFoundException */
    protected function count_channel_errors(Node_Element $channel_collection_element, string $channel_code): int
    {
        $error_count_selector = sprintf('[data-test-tab^="%s_"] .badge', $channel_code);
        /** @var NodeElement $element */
        $element = $channel_collection_element->find('css', $error_count_selector);
        if (null === $element) {
            throw new Element_Not_Found_Exception($this->get_session(), 'Channel errors count label', 'css', $error_count_selector);
        }
        return (int) $element->get_text();
    }
}