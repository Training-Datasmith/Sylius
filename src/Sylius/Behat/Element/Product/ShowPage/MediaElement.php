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
namespace Sylius\Behat\Element\Product\Show_Page;

use Sylius\Behat\Element\Sylius_Element;
class Media_Element extends Sylius_Element implements Media_Element_Interface
{
    public function is_image_displayed(): bool
    {
        $image_element = $this->get_document()->find('css', '[data-test-media] img');
        if ($image_element === null) {
            return false;
        }
        $image_url = $image_element->get_attribute('src');
        $original_url = $this->get_driver()->get_current_url();
        $this->get_driver()->visit($image_url);
        $page_text = $this->get_document()->get_text();
        $this->get_driver()->visit($original_url);
        return false === stripos((string) $page_text, '404 Not Found');
    }
}