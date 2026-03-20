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
namespace Sylius\Behat\Service\Checker;

use Liip\Imagine_Bundle\Service\Filter_Service;
final readonly class Image_Existence_Checker implements Image_Existence_Checker_Interface
{
    public function __construct(private Filter_Service $filter_service, private string $media_root_path)
    {
    }
    public function does_image_with_url_exist(string $image_url, string $liip_imagine_filter): bool
    {
        $image_url = str_replace($liip_imagine_filter . '/', '', substr($image_url, strpos($image_url, $liip_imagine_filter), strlen($image_url)));
        $browser_image_path = $this->filter_service->get_url_of_filtered_image($image_url, $liip_imagine_filter);
        return file_exists($this->media_root_path . parse_url($browser_image_path, \PHP_URL_PATH));
    }
}