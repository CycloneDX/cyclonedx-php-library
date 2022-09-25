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

namespace CycloneDX\Core\Serialization\JSON\Normalizers;

use CycloneDX\Core\_helpers\NullAssertionTrait;
use CycloneDX\Core\Models\Bom;
use CycloneDX\Core\Models\Metadata;
use CycloneDX\Core\Serialization\JSON\_BaseNormalizer;

/**
 * @author jkowalleck
 */
class BomNormalizer extends _BaseNormalizer
{
    use NullAssertionTrait;

    private const BOM_FORMAT = 'CycloneDX';

    public function normalize(Bom $bom): array
    {
        $factory = $this->getNormalizerFactory();

        return array_filter(
            [
                'bomFormat' => self::BOM_FORMAT,
                'specVersion' => $factory->getSpec()->getVersion(),
                'version' => $bom->getVersion(),
                'metadata' => $this->normalizeMetaData($bom->getMetadata()),
                'components' => $factory->makeForComponentRepository()->normalize($bom->getComponents()),
                'externalReferences' => $this->normalizeExternalReferences($bom),
                'dependencies' => $this->normalizeDependencies($bom),
            ],
            [$this, 'isNotNull']
        );
    }

    private function normalizeMetaData(Metadata $metaData): ?array
    {
        $factory = $this->getNormalizerFactory();

        if (false === $factory->getSpec()->supportsMetaData()) {
            return null;
        }

        $data = $factory->makeForMetaData()->normalize($metaData);

        return empty($data)
            ? null
            : $data;
    }

    private function normalizeExternalReferences(Bom $bom): ?array
    {
        $factory = $this->getNormalizerFactory();

        $extRefs = $bom->getExternalReferences();

        if (false === $factory->getSpec()->supportsMetaData()) {
            // prevent possible information loss: metadata cannot be rendered -> put it to bom
            $mcr = $bom->getMetadata()->getComponent()?->getExternalReferences();
            if (null !== $mcr) {
                $extRefs = (clone $extRefs)->addItems(...$mcr->getItems());
            }
            unset($mcr);
        }

        if (0 === \count($extRefs)) {
            return null;
        }

        $data = $factory->makeForExternalReferenceRepository()->normalize($extRefs);

        return empty($data)
            ? null
            : $data;
    }

    private function normalizeDependencies(Bom $bom): ?array
    {
        $factory = $this->getNormalizerFactory();

        if (false === $factory->getSpec()->supportsDependencies()) {
            return null;
        }

        $data = $factory->makeForDependencies()->normalize($bom);

        return empty($data)
            ? null
            : $data;
    }
}
