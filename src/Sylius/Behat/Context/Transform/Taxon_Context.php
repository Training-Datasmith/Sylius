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
namespace Sylius\Behat\Context\Transform;

use Behat\Behat\Context\Context;
use Behat\Transformation\Transform;
use Sylius\Component\Core\Model\Taxon_Interface;
use Sylius\Component\Taxonomy\Repository\Taxon_Repository_Interface;
use Webmozart\Assert\Assert;
final readonly class Taxon_Context implements Context
{
    public function __construct(private Taxon_Repository_Interface $taxon_repository, private string $locale)
    {
    }
    #[Transform('/^classified as "([^"]+)"$/')]
    #[Transform('/^belongs to "([^"]+)"$/')]
    #[Transform('/^"([^"]+)" taxon$/')]
    #[Transform('/^"([^"]+)" as a parent taxon$/')]
    #[Transform('/^"([^"]+)" parent taxon$/')]
    #[Transform('/^parent taxon to "([^"]+)"$/')]
    #[Transform('/^taxon should be "([^"]+)"$/')]
    #[Transform('/^taxon with "([^"]+)" name/')]
    #[Transform('/^taxon "([^"]+)"$/')]
    #[Transform(':taxon')]
    #[Transform(':parentTaxon')]
    public function get_taxon_by_name(string $name): Taxon_Interface
    {
        $taxons = $this->taxon_repository->find_by_name($name, $this->locale);
        Assert::eq(count($taxons), 1, sprintf('%d taxons has been found with name "%s".', count($taxons), $name));
        return $taxons[0];
    }
    #[Transform('/^taxon with "([^"]+)" code$/')]
    public function get_taxon_by_code(string $code): Taxon_Interface
    {
        $taxon = $this->taxon_repository->find_one_by(['code' => $code]);
        Assert::not_null($taxon, sprintf('Taxon with code "%s" does not exist.', $code));
        return $taxon;
    }
    #[Transform('/^classified as "([^"]+)" or "([^"]+)"$/')]
    #[Transform('/^configured with "([^"]+)" and "([^"]+)"$/')]
    #[Transform('/^"([^"]+)" and "([^"]+)" taxons$/')]
    #[Transform('/^belongs to "([^"]+)" and "([^"]+)"/')]
    #[Transform('/^"([^"]+)" and "([^"]+)" in the vertical menu$/')]
    public function get_taxons_by_names(string ...$taxon_names): iterable
    {
        foreach ($taxon_names as $taxon_name) {
            yield $this->get_taxon_by_name($taxon_name);
        }
    }
}