<?php

namespace Bdf\Serializer\Normalizer;

/**
 * Interface AutoRegisterInterface
 */
interface AutoRegisterInterface
{
    /**
     * Auto register to loader
     *
     * @param NormalizerLoaderInterface $loader
     */
    public function registerTo(NormalizerLoaderInterface $loader): void;
}
