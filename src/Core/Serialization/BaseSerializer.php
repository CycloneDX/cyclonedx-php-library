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
use CycloneDX\Core\Models\BomRef;
use Exception;

/**
 * @template TNormalizedBom
 */
abstract class BaseSerializer implements Serializer
{
    /**
     * @return BomRef[]
     */
    private function getAllBomRefs(Bom $bom): array
    {
        $allBomRefs = [];
        $allComponents = $bom->getComponents()->getItems();
        $metadataComponent = $bom->getMetadata()->getComponent();
        if (null !== $metadataComponent) {
            $allComponents[] = $metadataComponent;
        }
        foreach ($allComponents as $component) {
            $allBomRefs[] = $component->getBomRef();
            array_push($allBomRefs, ...$component->getDependencies()->getItems());
        }

        return $allBomRefs;
    }

    /**
     * @throws Exception
     *
     * @psalm-return TNormalizedBom
     *
     * @uses \CycloneDX\Core\Serialization\BomRefDiscriminator
     */
    private function normalize(BOM $bom)
    {
        $bomRefDiscriminator = new BomRefDiscriminator(...$this->getAllBomRefs($bom));
        $bomRefDiscriminator->discriminate();
        // This IS NOT the place to put meaning to the BomRef values. This would be out of scope.
        // This IS the place to make BomRef values (temporary) unique in their own document scope.
        try {
            return $this->realNormalize($bom);
        } finally {
            $bomRefDiscriminator->reset();
        }
    }

    /**
     * {@inheritDoc}
     *
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    final public function serialize(Bom $bom, bool $prettyPrint = false): string
    {
        return $this->realSerialize(
            $this->normalize($bom),
            $prettyPrint
        );
    }

    /**
     * Normalize an BOM to the data structure that {@see realSerialize} can handle.
     *
     * @throws Exception
     *
     * @psalm-return TNormalizedBom
     */
    abstract protected function realNormalize(Bom $bom);

    /**
     * Serialize a {@see realNormalize normalized} version of {@see Bom}.
     *
     * @throws Exception
     *
     * @psalm-param TNormalizedBom $normalizedBom
     *
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    abstract protected function realSerialize($normalizedBom, bool $pretty): string;
}
