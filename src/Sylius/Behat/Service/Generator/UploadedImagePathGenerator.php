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
namespace Sylius\Behat\Service\Generator;

use Sylius\Component\Core\Generator\Image_Path_Generator_Interface;
use Sylius\Component\Core\Model\Image_Interface;
use Symfony\Component\Http_Foundation\File\Uploaded_File;
final class Uploaded_Image_Path_Generator implements Image_Path_Generator_Interface
{
    public function generate(Image_Interface $image): string
    {
        /** @var UploadedFile $file */
        $file = $image->get_file();
        $hash = bin2hex(random_bytes(16));
        return $this->expand_path($hash . '/' . $file->get_client_original_name());
    }
    private function expand_path(string $path): string
    {
        return sprintf('%s/%s/%s', substr($path, 0, 2), substr($path, 2, 2), substr($path, 4));
    }
}