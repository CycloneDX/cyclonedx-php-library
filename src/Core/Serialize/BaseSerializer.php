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

namespace CycloneDX\Core\Serialize;

use CycloneDX\Core\Models\Bom;
use CycloneDX\Core\Models\BomRef;
use CycloneDX\Core\Spec\Spec;

/**
 * @author jkowalleck
 */
abstract class BaseSerializer implements SerializerInterface
{
    private Spec $spec;

    public function __construct(Spec $spec)
    {
        $this->spec = $spec;
    }

    public function getSpec(): Spec
    {
        return $this->spec;
    }

    /**
     * @return $this
     *
     * @deprecated
     *
     * @TODO remove with next major version - milestone v4
     */
    public function setSpec(Spec $spec): self
    {
        $this->spec = $spec;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize(Bom $bom): string
    {
        $bomRefDiscriminator = new BomRefDiscriminator(...$this->getAllBomRefs($bom));
        $bomRefDiscriminator->discriminate();
        try {
            return $this->normalize($bom);
        } finally {
            $bomRefDiscriminator->reset();
        }
    }

    /**
     * Normalize the Bom to a string.
     *
     * May throw implementation-dependent Exceptions.
     *
     * @psalm-return non-empty-string
     */
    abstract protected function normalize(Bom $bom): string;

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
}
