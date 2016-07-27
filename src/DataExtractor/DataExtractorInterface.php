<?php

namespace Nayjest\Manipulator\DataExtractor;

/**
 * Interface DataExtractorInterface
 *
 * DataExtractorInterface::isApplicable() should be always called for any source
 * before extracting data even if you know that data extractor is definitely applicable for some.
 */
interface DataExtractorInterface
{
    public function isApplicable($source, $targetName);
    public function extract($source, $targetName, $default);
}
