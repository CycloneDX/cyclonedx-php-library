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
    protected function getAllBomRefs(Bom $bom): array
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
     * {@inheritDoc}
     *
     * @uses \CycloneDX\Core\Serialization\BomRefDiscriminator
     */
    public function serialize(Bom $bom, bool $sortLists = false, bool $prettyPrint = false): string
    {
        $bomRefDiscriminator = new BomRefDiscriminator(...$this->getAllBomRefs($bom));
        try {
            $bomRefDiscriminator->discriminate();
            $normalized = $this->_normalize($bom, $sortLists);
        } finally {
            $bomRefDiscriminator->reset();
        }

        return $this->_serialize($normalized, $prettyPrint);
    }

    /**
     * @throws Exception
     *
     * @psalm-return TNormalizedBom
     */
    abstract protected function _normalize(Bom $bom, bool $sortLists);

    /**
     * @throws Exception
     *
     * @psalm-param TNormalizedBom $normalizedBom
     */
    abstract protected function _serialize($normalizedBom, bool $pretty): string;
}
