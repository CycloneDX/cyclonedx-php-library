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

use CycloneDX\Core\Collections\BomRefRepository;
use CycloneDX\Core\Collections\ExternalReferenceRepository;
use CycloneDX\Core\Collections\HashDictionary;
use CycloneDX\Core\Collections\LicenseRepository;
use CycloneDX\Core\Collections\PropertyRepository;
use CycloneDX\Core\Enums\ComponentType;
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
     * See {@link file://../../../docs/dev/decisions/BomDependencyDataModel.md BomDependencyDataModel docs}
     *
     * @readonly
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
     * @psalm-var ComponentType::*
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
     * The person(s) or organization(s) that authored the component.
     *
     * @psalm-var non-empty-string|null
     */
    private ?string $author = null;

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
    private LicenseRepository $licenses;

    /**
     * Specifies the file hashes of the component.
     */
    private HashDictionary $hashes;

    /**
     * References to dependencies.
     *
     * Implementation is intended to prevent memory leaks.
     * See {@link file://../../../docs/dev/decisions/BomDependencyDataModel.md BomDependencyDataModel docs}
     */
    private BomRefRepository $dependencies;

    /**
     * The component version. The version should ideally comply with semantic versioning
     * but is not enforced.
     */
    private ?string $version = null;

    /**
     * Provides the ability to document external references related to the
     * component or to the project the component describes.
     */
    private ExternalReferenceRepository $externalReferences;

    /**
     * Provides the ability to document properties in a name-value store. This provides flexibility to include data not
     * officially supported in the standard without having to use additional namespaces or create extensions.
     * Unlike key-value stores, properties support duplicate names, each potentially having different values.
     *
     * Property names of interest to the general public are encouraged to be registered in the
     * {@link https://github.com/CycloneDX/cyclonedx-property-taxonomy CycloneDX Property Taxonomy}.
     * Formal registration is OPTIONAL.
     */
    private PropertyRepository $properties;

    public function getBomRef(): BomRef
    {
        return $this->bomRef;
    }

    /**
     * shorthand for `->getBomRef()->setValue()`.
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
     * @psalm-return ComponentType::*
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type A valid {@see \CycloneDX\Core\Enums\ComponentType}
     *
     * @psalm-assert ComponentType::* $type
     *
     * @throws DomainException if value is unknown
     *
     * @return $this
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function setType(string $type): self
    {
        $this->type = ComponentType::isValidValue($type)
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

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    /**
     * @return $this
     */
    public function setAuthor(?string $author): self
    {
        $this->author = '' === $author
            ? null
            : $author;

        return $this;
    }

    public function getLicenses(): LicenseRepository
    {
        return $this->licenses;
    }

    /***
     * @return $this
     */
    public function setLicenses(LicenseRepository $licenses): self
    {
        $this->licenses = $licenses;

        return $this;
    }

    public function getHashes(): HashDictionary
    {
        return $this->hashes;
    }

    /**
     * @return $this
     */
    public function setHashes(HashDictionary $hashes): self
    {
        $this->hashes = $hashes;

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

    public function getDependencies(): BomRefRepository
    {
        return $this->dependencies;
    }

    /**
     * @return $this
     */
    public function setDependencies(BomRefRepository $dependencies): self
    {
        $this->dependencies = $dependencies;

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

    public function getProperties(): PropertyRepository
    {
        return $this->properties;
    }

    /**
     * @return $this
     */
    public function setProperties(PropertyRepository $properties): self
    {
        $this->properties = $properties;

        return $this;
    }

    /**
     * @psalm-assert ComponentType::* $type
     *
     * @throws DomainException if type is unknown
     */
    public function __construct(string $type, string $name)
    {
        $this->setType($type);
        $this->setName($name);
        $this->bomRef = new BomRef();
        $this->dependencies = new BomRefRepository();
        $this->licenses = new LicenseRepository();
        $this->hashes = new HashDictionary();
        $this->externalReferences = new ExternalReferenceRepository();
        $this->properties = new PropertyRepository();
    }

    public function __clone()
    {
        // bom ref must stay unique. a clone must have its own id!
        $this->bomRef = clone $this->bomRef;
    }
}
