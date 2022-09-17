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

use CycloneDX\Core\Enums\Classification;
use CycloneDX\Core\Models\License\LicenseExpression;
use CycloneDX\Core\Repositories\BomRefRepository;
use CycloneDX\Core\Repositories\DisjunctiveLicenseRepository;
use CycloneDX\Core\Repositories\ExternalReferenceRepository;
use CycloneDX\Core\Repositories\HashRepository;
use DomainException;
use PackageUrl\PackageUrl;

/**
 * @author nscuro
 * @author jkowalleck
 */
class Component
{
    /**
     * An optional identifier which can be used to reference the component elsewhere in the BOM. Every bom-ref should be unique.
     *
     * Implementation is intended to prevent memory leaks.
     * See ../../../docs/dev/decisions/BomDependencyDataModel.md
     */
    private BomRef $bomRef;

    /**
     * The name of the component. This will often be a shortened, single name
     * of the component.
     *
     * Examples: commons-lang3 and jquery
     *
     * @psalm-suppress PropertyNotSetInConstructor
     */
    private string $name;

    /**
     * The grouping name or identifier. This will often be a shortened, single
     * name of the company or project that produced the component, or the source package or
     * domain name.
     * Whitespace and special characters should be avoided.
     *
     * Examples include: apache, org.apache.commons, and apache.org.
     *
     * @psalm-var non-empty-string|null
     */
    private ?string $group = null;

    /**
     * Specifies the type of component. For software components, classify as application if no more
     * specific appropriate classification is available or cannot be determined for the component.
     * Valid choices are: application, framework, library, operating-system, device, or file.
     *
     * Refer to the {@link https://cyclonedx.org/schema/bom/1.1 bom:classification documentation}
     * for information describing each one.
     *
     * @psalm-var Classification::*
     *
     * @psalm-suppress PropertyNotSetInConstructor
     */
    private string $type;

    /**
     * Specifies a description for the component.
     *
     * @psalm-var non-empty-string|null
     */
    private ?string $description = null;

    /**
     * Package-URL (PURL).
     *
     * The purl, if specified, must be valid and conform to the specification
     * defined at: {@linnk https://github.com/package-url/purl-spec/blob/master/README.rst#purl}.
     */
    private ?PackageUrl $packageUrl = null;

    /**
     * licence(s).
     */
    private LicenseExpression|DisjunctiveLicenseRepository|null $license = null;

    /**
     * Specifies the file hashes of the component.
     */
    private ?HashRepository $hashRepository = null;

    /**
     * References to dependencies.
     *
     * Implementation is intended to prevent memory leaks.
     * See ../../../docs/dev/decisions/BomDependencyDataModel.md
     */
    private ?BomRefRepository $dependenciesBomRefRepository = null;

    /**
     * The component version. The version should ideally comply with semantic versioning
     * but is not enforced.
     */
    private ?string $version = null;

    /**
     * Provides the ability to document external references related to the
     * component or to the project the component describes.
     */
    private ?ExternalReferenceRepository $externalReferenceRepository = null;

    public function getBomRef(): BomRef
    {
        return $this->bomRef;
    }

    /**
     * shorthand for `{@see getBomRef()}->{@see BomRef::setValue() setValue()}`.
     *
     * @return $this
     */
    public function setBomRefValue(?string $value): self
    {
        $this->bomRef->setValue($value);

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return $this
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return non-empty-string|null
     */
    public function getGroup(): ?string
    {
        return $this->group;
    }

    /**
     * @return $this
     */
    public function setGroup(?string $group): self
    {
        $this->group = '' === $group
            ? null
            : $group;

        return $this;
    }

    /**
     * @psalm-return Classification::*
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type A valid {@see \CycloneDX\Core\Enums\Classification}
     *
     * @psalm-assert Classification::* $type
     *
     * @throws DomainException if value is unknown
     *
     * @return $this
     */
    public function setType(string $type): self
    {
        $this->type = Classification::isValidValue($type)
            ? $type
            : throw new DomainException("Invalid type: $type");

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @return $this
     */
    public function setDescription(?string $description): self
    {
        $this->description = '' === $description
            ? null
            : $description;

        return $this;
    }

    public function getLicense(): LicenseExpression|DisjunctiveLicenseRepository|null
    {
        return $this->license;
    }

    /***
     * @return $this
     */
    public function setLicense(LicenseExpression|DisjunctiveLicenseRepository|null $license): self
    {
        $this->license = $license;

        return $this;
    }

    public function getHashRepository(): ?HashRepository
    {
        return $this->hashRepository;
    }

    /**
     * @return $this
     */
    public function setHashRepository(?HashRepository $hashRepository): self
    {
        $this->hashRepository = $hashRepository;

        return $this;
    }

    public function getVersion(): ?string
    {
        return $this->version;
    }

    /**
     * @return $this
     */
    public function setVersion(?string $version): self
    {
        $this->version = $version;

        return $this;
    }

    public function getPackageUrl(): ?PackageUrl
    {
        return $this->packageUrl;
    }

    /**
     * @return $this
     */
    public function setPackageUrl(?PackageUrl $purl): self
    {
        $this->packageUrl = $purl;

        return $this;
    }

    public function getDependenciesBomRefRepository(): ?BomRefRepository
    {
        return $this->dependenciesBomRefRepository;
    }

    /**
     * @return $this
     */
    public function setDependenciesBomRefRepository(?BomRefRepository $dependenciesBomRefRepository): self
    {
        $this->dependenciesBomRefRepository = $dependenciesBomRefRepository;

        return $this;
    }

    public function getExternalReferenceRepository(): ?ExternalReferenceRepository
    {
        return $this->externalReferenceRepository;
    }

    /**
     * @return $this
     */
    public function setExternalReferenceRepository(?ExternalReferenceRepository $externalReferenceRepository): self
    {
        $this->externalReferenceRepository = $externalReferenceRepository;

        return $this;
    }

    /**
     * @psalm-assert Classification::* $type
     *
     * @throws DomainException if type is unknown
     */
    public function __construct(string $type, string $name)
    {
        $this->setType($type);
        $this->setName($name);
        $this->bomRef = new BomRef();
    }

    public function __clone()
    {
        // bom ref must stay unique. a clone must have its own id!
        $this->bomRef = clone $this->bomRef;
    }
}
