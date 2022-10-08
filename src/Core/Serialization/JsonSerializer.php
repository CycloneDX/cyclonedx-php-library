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
 */
class JsonSerializer extends BaseSerializer
{
    /**
     * JSON schema `$id` that is applied.
     *
     * @var string[]|null[]
     *
     * @psalm-var  array<Version::*,?string>
     */
    private const SCHEMA = [
        Version::v1dot1 => null, // unsupported version
        Version::v1dot2 => 'http://cyclonedx.org/schema/bom-1.2b.schema.json',
        Version::v1dot3 => 'http://cyclonedx.org/schema/bom-1.3a.schema.json',
        Version::v1dot4 => 'http://cyclonedx.org/schema/bom-1.4.schema.json',
    ];

    /** @readonly */
    private JSON\NormalizerFactory $normalizerFactory;

    /**
     * Bitmask consisting of JSON_*.
     *
     * @readonly
     */
    private int $jsonEncodeFlags = 0
        | \JSON_THROW_ON_ERROR // prevent unexpected data
        | \JSON_PRESERVE_ZERO_FRACTION // float/double not converted to int
    ;

    /**
     * @param int $jsonEncodeFlags Bitmask consisting of JSON_*
     */
    public function __construct(
        JSON\NormalizerFactory $normalizerFactory,
        int $jsonEncodeFlags = \JSON_UNESCAPED_SLASHES // urls become shorter
    ) {
        $this->normalizerFactory = $normalizerFactory;
        $this->jsonEncodeFlags |= $jsonEncodeFlags;
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
    protected function realSerialize($normalizedBom, bool $pretty): string
    {
        /** @var string|null $schema */
        $schema = self::SCHEMA[$this->normalizerFactory->getSpec()->getVersion()] ?? null;
        if (null !== $schema) {
            $normalizedBom['$schema'] = $schema;
        }

        $jsonEncodeFlags = $this->jsonEncodeFlags;
        if ($pretty) {
            $jsonEncodeFlags |= \JSON_PRETTY_PRINT;
        }

        $json = json_encode($normalizedBom, $jsonEncodeFlags);
        \assert(false !== $json); // as option JSON_THROW_ON_ERROR is expected to be set
        \assert('' !== $json);

        return $json;
    }
}
