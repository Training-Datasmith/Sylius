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
namespace Sylius\Behat\Context\Ui\Admin\Helper;

use Behat\Step\Then;
use Behat\Step\When;
use Sylius\Behat\Behaviour\Specifies_Its_Field;
use Sylius\Behat\Page\Sylius_Page_Interface;
use Sylius\Component\Core\Formatter\String_Inflector;
use Webmozart\Assert\Assert;
trait Validation_Trait
{
    #[When('I specify a too long :field')]
    public function i_specify_a_too_long(string $field): void
    {
        $this->resolve_current_page()->specify_field(ucwords($field), str_repeat('a', 256));
    }
    #[Then('I should be notified that :field is too long')]
    #[Then('I should be notified that :field should be no longer than :maxLength characters')]
    public function i_should_be_notified_that_field_value_is_too_long(string $field, int $max_length = 255): void
    {
        try {
            $validation_message = $this->resolve_current_page()->get_validation_message('field_' . String_Inflector::name_to_lowercase_code($field));
        } catch (\InvalidArgumentException) {
            $validation_message = $this->resolve_current_page()->get_validation_message(String_Inflector::name_to_lowercase_code($field));
        }
        Assert::contains($validation_message, sprintf('must not be longer than %d characters.', $max_length));
    }
    /**
     * @return SyliusPageInterface&SpecifiesItsField
     */
    abstract protected function resolve_current_page(): Sylius_Page_Interface;
}