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
namespace Sylius\Behat\Context\Ui\Admin;

use Behat\Behat\Context\Context;
use Behat\Step\Then;
use Sylius\Behat\Page\Admin\Administrator\Create_Page_Interface;
use Sylius\Behat\Page\Admin\Dashboard_Page_Interface;
use Symfony\Contracts\Translation\Translator_Interface;
use Webmozart\Assert\Assert;
final readonly class Locale_Context implements Context
{
    public function __construct(private Dashboard_Page_Interface $dashboard_page, private Translator_Interface $translator, private Create_Page_Interface $create_page)
    {
    }
    #[Then('I should be viewing the administration panel in :localeCode locale')]
    #[Then('I should still be viewing the administration panel in :localeCode locale')]
    #[Then('they should be viewing the administration panel in :localeCode locale')]
    public function i_should_be_viewing_the_administration_panel_in(string $locale_code): void
    {
        if (!$this->dashboard_page->is_open()) {
            $this->dashboard_page->open();
        }
        Assert::same($this->dashboard_page->get_dashboard_header(), $this->translate('sylius.ui.dashboard', $locale_code));
    }
    #[Then('I should be notified that this email is not valid in :localeCode locale')]
    public function i_should_be_notified_that_this_email_is_not_valid_in_locale(string $locale_code): void
    {
        Assert::same($this->create_page->get_validation_message('field_email'), $this->translate('sylius.contact.email.invalid', $locale_code, 'validators'));
    }
    private function translate(string $text, string $locale_code, ?string $domain = null): string
    {
        return $this->translator->trans($text, [], $domain, $locale_code);
    }
}