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
namespace Sylius\Behat\Context\Api\Admin\Helper;

use Behat\Step\Then;
use Behat\Step\When;
use Sylius\Component\Core\Formatter\String_Inflector;
use Webmozart\Assert\Assert;
trait Validation_Trait
{
    #[When('I specify a too long :field')]
    public function i_specify_a_too_long(string $field): void
    {
        $this->client->add_request_data($field, str_repeat('a', $this->get_max_code_length() + 1));
    }
    #[Then('I should be notified that :field is too long')]
    #[Then('I should be notified that :field should be no longer than :maxLength characters')]
    public function i_should_be_notified_that_field_is_too_long(string $field, int $max_length = 255): void
    {
        Assert::regex($this->response_checker->get_error($this->client->get_last_response()), sprintf('/%s\: .+ must not be longer than %d characters./', String_Inflector::name_to_camel_case($field), $max_length));
    }
    private function get_max_code_length(): int
    {
        return 255;
    }
}