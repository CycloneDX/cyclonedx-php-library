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
 * transform data models to JSON.
 *
 * @template-extends BaseSerializer<array>
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

    /** @readonly  */
    private int $jsonEncodeOptions;

    public function __construct(
        private JSON\NormalizerFactory $normalizerFactory,
        int $jsonEncodeOptions = \JSON_UNESCAPED_SLASHES // urls become shorter
    ) {
        $this->jsonEncodeOptions = $jsonEncodeOptions
            | \JSON_THROW_ON_ERROR // prevent unexpected data
            | \JSON_PRESERVE_ZERO_FRACTION // float/double not converted to int
        ;
    }

    /**
     * @psalm-return array
     */
    protected function _normalize(Bom $bom): array
    {
        return $this->normalizerFactory
            ->makeForBom()
            ->normalize($bom);
    }

    public function serialize(Bom $bom, bool $pretty = false): string
    {
        /**
         * @var array $document
         *
         * @psalm-suppress UnnecessaryVarAnnotation
         */
        $document = $this->normalize($bom);

        /** @var string|null $schema */
        $schema = self::SCHEMA[$this->normalizerFactory->getSpec()->getVersion()] ?? null;
        if (null !== $schema) {
            $document['$schema'] = $schema;
        }

        $jsonEncodeOptions = $this->jsonEncodeOptions;
        if ($pretty) {
            $jsonEncodeOptions |= \JSON_PRETTY_PRINT;
        }

        $json = json_encode($document, $jsonEncodeOptions);
        \assert(false !== $json); // as option JSON_THROW_ON_ERROR is expected to be set
        \assert('' !== $json);

        return $json;
    }
}
