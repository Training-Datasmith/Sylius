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
namespace Sylius\Behat\Service\Resolver;

use Behat\Mink\Session;
use Sylius\Behat\Page\Sylius_Page_Interface;
use Symfony\Component\Routing\Matcher\Url_Matcher_Interface;
use Webmozart\Assert\Assert;
final readonly class Current_Page_Resolver implements Current_Page_Resolver_Interface
{
    public function __construct(private Session $session, private Url_Matcher_Interface $url_matcher)
    {
    }
    /**
     * @throws \LogicException
     */
    public function get_current_page_with_form(array $pages): Sylius_Page_Interface
    {
        $route_parameters = $this->url_matcher->match(parse_url($this->session->get_current_url(), \PHP_URL_PATH));
        Assert::all_is_instance_of($pages, Sylius_Page_Interface::class);
        foreach ($pages as $page) {
            if ($route_parameters['_route'] === $page->get_route_name()) {
                return $page;
            }
        }
        throw new \LogicException('Route name could not be matched to provided pages.');
    }
}