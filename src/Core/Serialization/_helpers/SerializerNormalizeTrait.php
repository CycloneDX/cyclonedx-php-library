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

namespace CycloneDX\Core\Serialization\_helpers;

use CycloneDX\Core\Models\Bom;
use CycloneDX\Core\Models\BomRef;
use CycloneDX\Core\Serialization\BomRefDiscriminator;
use Exception;

/**
 * @internal as this trait may be affected by breaking changes without notice
 */
trait SerializerNormalizeTrait
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
     * @uses \CycloneDX\Core\Serialization\BomRefDiscriminator
     *
     * @return mixed depending on `@see $this->normalizerFactory->makeForBom()->normalize()`
     */
    protected function normalize(Bom $bom): mixed
    {
        $bomRefDiscriminator = new BomRefDiscriminator(...$this->getAllBomRefs($bom));
        $bomRefDiscriminator->discriminate();
        try {
            return $this->normalizerFactory->makeForBom()->normalize($bom);
        } finally {
            $bomRefDiscriminator->reset();
        }
    }
}
