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

namespace CycloneDX\Core\Serialize\DOM\Normalizers;

use CycloneDX\Core\_Helpers\SimpleDomTrait;
use CycloneDX\Core\Collections\ComponentRepository;
use CycloneDX\Core\Models\Bom;
use CycloneDX\Core\Models\Metadata;
use CycloneDX\Core\Serialize\DOM\AbstractNormalizer;
use DOMElement;

/**
 * @author jkowalleck
 */
class BomNormalizer extends AbstractNormalizer
{
    use SimpleDomTrait;

    private const XML_NAMESPACE_PATTERN = 'http://cyclonedx.org/schema/bom/%s';

    public function normalize(Bom $bom): DOMElement
    {
        $factory = $this->getNormalizerFactory();
        $document = $factory->getDocument();

        $element = $document->createElementNS(
            sprintf(self::XML_NAMESPACE_PATTERN, $factory->getSpec()->getVersion()),
            'bom' // no namespace = defaultNS - so children w/o NS fall under this NS
        );
        $this->simpleDomSetAttributes(
            $element,
            [
                'version' => $bom->getVersion(),
                // serialNumber
            ]
        );

        $this->simpleDomAppendChildren(
            $element,
            [
                $this->normalizeMetaData($bom->getMetadata()),
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

        return $this->simpleDomAppendChildren(
            $factory->getDocument()->createElement('components'),
            $factory->makeForComponentRepository()->normalize($components)
        );
    }

    private function normalizeMetaData(Metadata $metaData): ?DOMElement
    {
        $factory = $this->getNormalizerFactory();

        if (false === $factory->getSpec()->supportsMetaData()) {
            return null;
        }

        $elem = $factory->makeForMetaData()->normalize($metaData);

        return $elem->hasChildNodes()
            ? $elem
            : null;
    }

    private function normalizeExternalReferences(Bom $bom): ?DOMElement
    {
        $factory = $this->getNormalizerFactory();

        $externalReferenceRepository = $bom->getExternalReferences();

        if (false === $factory->getSpec()->supportsMetaData()) {
            // prevent possible information loss: metadata cannot be rendered -> put it to bom
            $mcr = $bom->getMetadata()->getComponent()?->getExternalReferences();
            if (null !== $mcr) {
                $externalReferenceRepository = \count($externalReferenceRepository) > 0
                    ? (clone $externalReferenceRepository)->addItems(...$mcr->getItems())
                    : $mcr;
            }
            unset($mcr);
        }

        if (0 === \count($externalReferenceRepository)) {
            return null;
        }

        $refs = $factory->makeForExternalReferenceRepository()->normalize($externalReferenceRepository);

        return 0 === \count($refs)
            ? null
            : $this->simpleDomAppendChildren(
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
            : $this->simpleDomAppendChildren(
                $factory->getDocument()->createElement('dependencies'),
                $deps
            );
    }
}
