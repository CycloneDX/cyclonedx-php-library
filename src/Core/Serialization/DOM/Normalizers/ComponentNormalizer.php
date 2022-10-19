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

use CycloneDX\Core\_helpers\SimpleDomTrait;
use CycloneDX\Core\_helpers\XmlTrait;
use CycloneDX\Core\Collections\ExternalReferenceRepository;
use CycloneDX\Core\Collections\HashDictionary;
use CycloneDX\Core\Collections\LicenseRepository;
use CycloneDX\Core\Collections\PropertyRepository;
use CycloneDX\Core\Models\Component;
use CycloneDX\Core\Serialization\DOM\_BaseNormalizer;
use DomainException;
use DOMElement;
use PackageUrl\PackageUrl;

/**
 * @author jkowalleck
 */
class ComponentNormalizer extends _BaseNormalizer
{
    use SimpleDomTrait;
    use XmlTrait;

    /**
     * @throws DomainException if component has unsupported type
     */
    public function normalize(Component $component): DOMElement
    {
        $name = $component->getName();
        $group = $component->getGroup();
        $version = $component->getVersion();

        $factory = $this->getNormalizerFactory();
        $spec = $factory->getSpec();

        $type = $component->getType();
        if (false === $spec->isSupportedComponentType($type)) {
            $reportFQN = "$group/$name";
            if (null !== $version) {
                $reportFQN .= "@$version";
            }
            throw new DomainException("Component '$reportFQN' has unsupported type: $type");
        }

        $bomRef = $spec->supportsBomRef()
            ? $component->getBomRef()->getValue()
            : null;

        $document = $factory->getDocument();

        return $this->simpleDomAppendChildren(
            $this->simpleDomSetAttributes(
                $document->createElement('component'),
                [
                    'type' => $type,
                    'bom-ref' => $bomRef,
                ]
            ),
            [
                // publisher
                $this->simpleDomSafeTextElement($document, 'group', $group),
                $this->simpleDomSafeTextElement($document, 'name', $name),
                $this->simpleDomSafeTextElement($document, 'version',
                    null === $version && $spec->requiresComponentVersion()
                        ? ''
                        : $version
                ),
                $this->simpleDomSafeTextElement($document, 'description', $component->getDescription()),
                // scope
                $this->normalizeHashes($component->getHashes()),
                $this->normalizeLicenses($component->getLicenses()),
                // copyright
                // cpe
                $this->normalizePurl($component->getPackageUrl()),
                // modified
                // pedigree
                $this->normalizeExternalReferences($component->getExternalReferences()),
                $this->normalizeProperties($component->getProperties()),
                // components
            ]
        );
    }

    private function normalizeLicenses(LicenseRepository $licenses): ?DOMElement
    {
        $factory = $this->getNormalizerFactory();

        return 0 === \count($licenses)
            ? null
            : $this->simpleDomAppendChildren(
                $factory->getDocument()->createElement('licenses'),
                $factory->makeForLicenseRepository()->normalize($licenses)
            );
    }

    private function normalizeHashes(HashDictionary $hashes): ?DOMElement
    {
        $factory = $this->getNormalizerFactory();

        return 0 === \count($hashes)
            ? null
            : $this->simpleDomAppendChildren(
                $factory->getDocument()->createElement('hashes'),
                $factory->makeForHashDictionary()->normalize($hashes)
            );
    }

    private function normalizePurl(?PackageUrl $purl): ?DOMElement
    {
        return null === $purl
            ? null
            : $this->simpleDomSafeTextElement(
                $this->getNormalizerFactory()->getDocument(),
                'purl',
                $this->encodeAnyUriBE((string) $purl)
            );
    }

    private function normalizeExternalReferences(ExternalReferenceRepository $extRefs): ?DOMElement
    {
        $factory = $this->getNormalizerFactory();

        return 0 === \count($extRefs)
            ? null
            : $this->simpleDomAppendChildren(
                $factory->getDocument()->createElement('externalReferences'),
                $factory->makeForExternalReferenceRepository()->normalize($extRefs)
            );
    }

    private function normalizeProperties(PropertyRepository $properties): ?DOMElement
    {
        // TODO check if spec allows element

        return 0 === \count($properties)
            ? null
            : $this->simpleDomAppendChildren(
                $this->getNormalizerFactory()->getDocument()->createElement('properties'),
                [] // TODO
            );
    }
}
