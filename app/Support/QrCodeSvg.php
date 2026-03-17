<?php

declare(strict_types=1);

namespace App\Support;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\SvgWriter;

/**
 * SVG QR codes (no GD/Imagick required).
 */
final class QrCodeSvg
{
    public static function forData(string $data, int $size = 180, int $margin = 6): string
    {
        return Builder::create()
            ->writer(new SvgWriter)
            ->writerOptions([SvgWriter::WRITER_OPTION_EXCLUDE_XML_DECLARATION => true])
            ->data($data)
            ->size($size)
            ->margin($margin)
            ->build()
            ->getString();
    }
}
