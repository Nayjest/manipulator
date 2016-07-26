<?php

namespace Nayjest\Manipulator\DataInjector;

/**
 * Interface DataExtractorInterface
 *
 * DataExtractorInterface::isApplicable() should be always called for any source
 * before extracting data even if you know that data extractor is definitely applicable for some.
 */
interface DataInjectorInterface
{
    public function isApplicable($source, array $values);

    /**
     * @param $source
     * @param $values
     * @return bool|string[] returns true if all fields injected or array of successfully injected fields.
     */
    public function inject(&$source, array $values);
}
