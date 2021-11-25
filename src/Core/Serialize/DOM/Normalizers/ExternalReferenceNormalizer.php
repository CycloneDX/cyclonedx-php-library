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
 * Copyright (c) Steve Springett. All Rights Reserved.
 */

namespace CycloneDX\Core\Serialize\DOM\Normalizers;

use CycloneDX\Core\Helpers\SimpleDomTrait;
use CycloneDX\Core\Models\ExternalReference;
use CycloneDX\Core\Repositories\HashRepository;
use CycloneDX\Core\Serialize\DOM\AbstractNormalizer;
use DOMElement;

/**
 * @author jkowalleck
 */
class ExternalReferenceNormalizer extends AbstractNormalizer
{
    use SimpleDomTrait;

    /**
     * @throws \DomainException
     */
    public function normalize(ExternalReference $externalReference): DOMElement
    {
        // could throw DomainException if the type was not supported

        $doc = $this->getNormalizerFactory()->getDocument();

        return $this->simpleDomAppendChildren(
            $this->simpleDomSetAttributes(
                $doc->createElement('reference'),
                [
                    'type' => $externalReference->getType(),
                ]
            ),
            [
                $this->simpleDomSafeTextElement($doc, 'url', $externalReference->getUrl()),
                $this->simpleDomSafeTextElement($doc, 'comment', $externalReference->getComment()),
                $this->normalizeHashes($externalReference->getHashRepository()),
            ]
        );
    }

    private function normalizeHashes(?HashRepository $hashes): ?DOMElement
    {
        $factory = $this->getNormalizerFactory();

        if (false === $factory->getSpec()->supportsExternalReferenceHashes()) {
            return null;
        }

        return null === $hashes || 0 === \count($hashes)
            ? null
            : $this->simpleDomAppendChildren(
                $factory->getDocument()->createElement('hashes'),
                $factory->makeForHashRepository()->normalize($hashes)
            );
    }
}
