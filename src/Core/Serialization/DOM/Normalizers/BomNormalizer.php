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

namespace CycloneDX\Core\Serialization\DOM\Normalizers;

use CycloneDX\Core\_helpers\SimpleDom;
use CycloneDX\Core\_helpers\SimpleDomTrait;
use CycloneDX\Core\Collections\ComponentRepository;
use CycloneDX\Core\Models\Bom;
use CycloneDX\Core\Models\Metadata;
use CycloneDX\Core\Serialization\DOM\_BaseNormalizer;
use DOMElement;

/**
 * @author jkowalleck
 */
class BomNormalizer extends _BaseNormalizer
{
    use SimpleDomTrait;

    private const XML_NAMESPACE_PATTERN = 'http://cyclonedx.org/schema/bom/%s';

    public function normalize(Bom $bom): DOMElement
    {
        $factory = $this->getNormalizerFactory();
        $document = $factory->getDocument();

        $element = $document->createElementNS(
            sprintf(self::XML_NAMESPACE_PATTERN, $factory->getSpec()->getVersion()->value),
            'bom' // no namespace = defaultNS - so children w/o NS fall under this NS
        );
        SimpleDom::setAttributes(
            $element,
            [
                'version' => $bom->getVersion(),
                'serialNumber' => $bom->getSerialNumber(),
            ]
        );

        SimpleDom::appendChildren(
            $element,
            [
                $this->normalizeMetadata($bom->getMetadata()),
                $this->normalizeComponents($bom->getComponents()),
                $this->normalizeExternalReferences($bom),
                $this->normalizeDependencies($bom),
            ]
        );

        return $element;
    }

    private function normalizeComponents(ComponentRepository $components): DOMElement
    {
        $factory = $this->getNormalizerFactory();

        return SimpleDom::appendChildren(
            $factory->getDocument()->createElement('components'),
            $factory->makeForComponentRepository()->normalize($components)
        );
    }

    private function normalizeMetadata(Metadata $metadata): ?DOMElement
    {
        $factory = $this->getNormalizerFactory();

        if (false === $factory->getSpec()->supportsMetadata()) {
            return null;
        }

        $elem = $factory->makeForMetadata()->normalize($metadata);

        return $elem->hasChildNodes()
            ? $elem
            : null;
    }

    private function normalizeExternalReferences(Bom $bom): ?DOMElement
    {
        $factory = $this->getNormalizerFactory();

        $extRefs = $bom->getExternalReferences();

        if (false === $factory->getSpec()->supportsMetadata()) {
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

        $refs = $factory->makeForExternalReferenceRepository()->normalize($extRefs);

        return 0 === \count($refs)
            ? null
            : SimpleDom::appendChildren(
                $factory->getDocument()->createElement('externalReferences'),
                $refs
            );
    }

    private function normalizeDependencies(Bom $bom): ?DOMElement
    {
        $factory = $this->getNormalizerFactory();

        if (false === $factory->getSpec()->supportsDependencies()) {
            return null;
        }

        $deps = $factory->makeForDependencies()->normalize($bom);

        return empty($deps)
            ? null
            : SimpleDom::appendChildren(
                $factory->getDocument()->createElement('dependencies'),
                $deps
            );
    }
}
