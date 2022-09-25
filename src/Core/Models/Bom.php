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

namespace CycloneDX\Core\Models;

use CycloneDX\Core\Collections\ComponentRepository;
use CycloneDX\Core\Collections\ExternalReferenceRepository;
use DomainException;

/**
 * @author nscuro
 * @author jkowalleck
 */
class Bom
{
    /**
     * @psalm-suppress PropertyNotSetInConstructor
     */
    private ComponentRepository $components;

    /**
     * The version allows component publishers/authors to make changes to existing BOMs to update various aspects of the document such as description or licenses.
     * When a system is presented with multiple BOMs for the same component, the system should use the most recent version of the BOM.
     * The default version is '1' and should be incremented for each version of the BOM that is published.
     * Each version of a component should have a unique BOM and if no changes are made to the BOMs, then each BOM will have a version of '1'.
     *
     * @psalm-var positive-int
     */
    private int $version = 1;

    private Metadata $metadata;

    /**
     * Provides the ability to document external references related to the BOM or
     * to the project the BOM describes.
     */
    private ExternalReferenceRepository $externalReferences;

    public function __construct(?ComponentRepository $components = null)
    {
        $this->setComponents($components ?? new ComponentRepository());
        $this->externalReferences = new ExternalReferenceRepository();
        $this->metadata = new Metadata();
    }

    public function getComponents(): ComponentRepository
    {
        return $this->components;
    }

    /**
     * @return $this
     */
    public function setComponents(ComponentRepository $components): self
    {
        $this->components = $components;

        return $this;
    }

    /**
     * @psalm-return positive-int
     */
    public function getVersion(): int
    {
        return $this->version;
    }

    /**
     * @param int $version a value >= 1
     *
     * @psalm-assert positive-int $version
     *
     * @throws DomainException if version <= 0
     *
     * @return $this
     */
    public function setVersion(int $version): self
    {
        $this->version = self::isValidVersion($version)
            ? $version
            : throw new DomainException("Invalid value: $version");

        return $this;
    }

    /**
     * @psalm-assert-if-true positive-int $version
     */
    private static function isValidVersion(int $version): bool
    {
        return $version > 0;
    }

    public function getMetadata(): Metadata
    {
        return $this->metadata;
    }

    /**
     * @return $this
     */
    public function setMetadata(Metadata $metadata): self
    {
        $this->metadata = $metadata;

        return $this;
    }

    public function getExternalReferences(): ExternalReferenceRepository
    {
        return $this->externalReferences;
    }

    /**
     * @return $this
     */
    public function setExternalReferences(ExternalReferenceRepository $externalReferences): self
    {
        $this->externalReferences = $externalReferences;

        return $this;
    }
}
