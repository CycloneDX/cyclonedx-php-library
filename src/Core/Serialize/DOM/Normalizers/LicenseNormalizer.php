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
use CycloneDX\Core\_Helpers\XmlTrait;
use CycloneDX\Core\Models\License\DisjunctiveLicenseWithId;
use CycloneDX\Core\Models\License\DisjunctiveLicenseWithName;
use CycloneDX\Core\Models\License\LicenseExpression;
use CycloneDX\Core\Serialize\DOM\AbstractNormalizer;
use DOMElement;

/**
 * @author jkowalleck
 */
class LicenseNormalizer extends AbstractNormalizer
{
    use SimpleDomTrait;
    use XmlTrait;

    public function normalize(LicenseExpression|DisjunctiveLicenseWithId|DisjunctiveLicenseWithName $license): DOMElement
    {
        return $license instanceof LicenseExpression
            ? $this->normalizeExpression($license)
            : $this->normalizeDisjunctive($license);
    }

    private function normalizeExpression(LicenseExpression $license): DOMElement
    {
        // TO BE IMPLEMENTED IF NEEDED: may throw, if not supported by the spec

        $element = $this->simpleDomSafeTextElement(
            $this->getNormalizerFactory()->getDocument(),
            'expression',
            $license->getExpression()
        );
        \assert(null !== $element);

        return $element;
    }

    private function normalizeDisjunctive(DisjunctiveLicenseWithId|DisjunctiveLicenseWithName $license): DOMElement
    {
        [$id, $name] = $license instanceof DisjunctiveLicenseWithId
            ? [$license->getId(), null]
            : [null, $license->getName()];

        $document = $this->getNormalizerFactory()->getDocument();

        return $this->simpleDomAppendChildren(
            $document->createElement('license'),
            [
                $this->simpleDomSafeTextElement($document, 'id', $id),
                $this->simpleDomSafeTextElement($document, 'name', $name),
                $this->simpleDomSafeTextElement($document, 'url', $this->encodeAnyUriBE($license->getUrl())),
            ]
        );
    }
}
