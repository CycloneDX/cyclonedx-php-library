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
use CycloneDX\Core\Collections\ExternalReferenceRepository;
use CycloneDX\Core\Collections\HashDictionary;
use CycloneDX\Core\Models\Tool;
use CycloneDX\Core\Serialization\DOM\_BaseNormalizer;
use DOMElement;

/**
 * @author jkowalleck
 */
class ToolNormalizer extends _BaseNormalizer
{
    use SimpleDomTrait;

    public function normalize(Tool $tool): DOMElement
    {
        $doc = $this->getNormalizerFactory()->getDocument();

        return SimpleDom::appendChildren(
            $doc->createElement('tool'),
            [
                SimpleDom::makeSafeTextElement($doc, 'vendor', $tool->getVendor()),
                SimpleDom::makeSafeTextElement($doc, 'name', $tool->getName()),
                SimpleDom::makeSafeTextElement($doc, 'version', $tool->getVersion()),
                $this->normalizeHashes($tool->getHashes()),
                $this->normalizeExternalReferences($tool->getExternalReferences()),
            ]
        );
    }

    private function normalizeHashes(HashDictionary $hashes): ?DOMElement
    {
        $factory = $this->getNormalizerFactory();

        return 0 === \count($hashes)
            ? null
            : SimpleDom::appendChildren(
                $factory->getDocument()->createElement('hashes'),
                $factory->makeForHashDictionary()->normalize($hashes)
            );
    }

    private function normalizeExternalReferences(ExternalReferenceRepository $extRefs): ?DOMElement
    {
        $factory = $this->getNormalizerFactory();

        if (false === $factory->getSpec()->supportsToolExternalReferences()) {
            return null;
        }

        return 0 === \count($extRefs)
            ? null
            : SimpleDom::appendChildren(
                $factory->getDocument()->createElement('externalReferences'),
                $factory->makeForExternalReferenceRepository()->normalize($extRefs)
            );
    }
}
