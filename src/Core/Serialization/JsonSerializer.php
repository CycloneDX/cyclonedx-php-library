<?php

declare(strict_types=1);

/*
 * This file is part of CycloneDX PHP Library.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * SPDX-License-Identifier: Apache-2.0
 * Copyright (c) OWASP Foundation. All Rights Reserved.
 */

namespace CycloneDX\Core\Serialization;

use CycloneDX\Core\Models\Bom;
use CycloneDX\Core\Spec\Version;

/**
 * Transform data models to JSON.
 *
 * @psalm-type TNormalizedBom=array
 *
 * @template-extends BaseSerializer<TNormalizedBom>
 *
 * @SuppressWarnings(PHPMD.ConstantNamingConventions)
 */
class JsonSerializer extends BaseSerializer
{
    /**
     * List of allowed options for $jsonEncodeFlags.
     * Some flags will break the output...
     *
     * Bitmask consisting of JSON_*.
     *
     * @see https://www.php.net/manual/en/json.constants.php
     */
    private const JsonEncodeFlagsAllowedOptions = 0
        | \JSON_HEX_TAG
        | \JSON_HEX_AMP
        | \JSON_HEX_APOS
        | \JSON_HEX_QUOT
        | \JSON_PRETTY_PRINT
        | \JSON_UNESCAPED_SLASHES
        | \JSON_UNESCAPED_UNICODE
    ;

    /**
     * List of mandatory options for $jsonEncodeFlags.
     *
     * Bitmask consisting of JSON_*.
     *
     * @see https://www.php.net/manual/en/json.constants.php
     */
    private const JsonEncodeFlagsDefaultOptions = 0
        | \JSON_UNESCAPED_SLASHES // urls become shorter
    ;

    /** @readonly */
    private JSON\NormalizerFactory $normalizerFactory;

    /**
     * Flags for {@see json_encode}.
     *
     * Bitmask consisting of JSON_*.
     *
     * @see https://www.php.net/manual/en/json.constants.php
     *
     * @readonly
     */
    private int $jsonEncodeFlags = 0
        // These defaults are required to have valid output.
        | \JSON_THROW_ON_ERROR // prevent unexpected data
        | \JSON_PRESERVE_ZERO_FRACTION // float/double not converted to int
    ;

    /**
     * @param int $jsonEncodeFlags Bitmask consisting of JSON_*. see {@see JsonEncodeFlagsAllowedOptions}
     */
    public function __construct(
        JSON\NormalizerFactory $normalizerFactory,
        int $jsonEncodeFlags = self::JsonEncodeFlagsDefaultOptions
    ) {
        $this->normalizerFactory = $normalizerFactory;
        $this->jsonEncodeFlags |= $jsonEncodeFlags & self::JsonEncodeFlagsAllowedOptions;
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-return TNormalizedBom
     */
    protected function realNormalize(Bom $bom): array
    {
        return $this->normalizerFactory
            ->makeForBom()
            ->normalize($bom);
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-param TNormalizedBom $normalizedBom
     *
     * @psalm-return non-empty-string
     */
    protected function realSerialize($normalizedBom, ?bool $prettyPrint): string
    {
        $jsonEncodeFlags = match ($prettyPrint) {
            // reminder: JSON_PRETTY_PRINT could be in $this->jsonEncodeFlags already
            null => $this->jsonEncodeFlags,
            true => $this->jsonEncodeFlags | \JSON_PRETTY_PRINT,
            false => $this->jsonEncodeFlags & ~\JSON_PRETTY_PRINT,
        };

        $json = json_encode($normalizedBom, $jsonEncodeFlags);
        \assert(false !== $json); // as option JSON_THROW_ON_ERROR is expected to be set
        \assert('' !== $json);

        return $json;
    }
}
