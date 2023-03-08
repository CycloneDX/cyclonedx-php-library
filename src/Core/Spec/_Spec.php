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

namespace CycloneDX\Core\Spec;

use CycloneDX\Core\Enums\ComponentType;
use CycloneDX\Core\Enums\ExternalReferenceType;
use CycloneDX\Core\Enums\HashAlgorithm;
use CycloneDX\Core\Spdx\LicenseIdentifiers;

/**
 * This class is not for public use.
 * See {@see \CycloneDX\Core\Spec\SpecFactory Specification Factory} to get prepared instances.
 *
 * @internal as this class may be affected by breaking changes without notice
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 * @SuppressWarnings(PHPMD.CamelCaseClassName)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 *
 * @author jkowalleck
 */
class _Spec implements Spec
{
    /** @psalm-var list<string> */
    private array $lLicenseIdentifiers;

    /**
     * @psalm-param list<Format> $lFormats
     * @psalm-param list<ComponentType> $lComponentTypes
     * @psalm-param list<HashAlgorithm> $lHashAlgorithms
     * @psalm-param list<ExternalReferenceType> $lExternalReferenceTypes
     * @psalm-param list<Format> $lFormatsSupportingBomProperties
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        private readonly Version $version,
        private readonly array $lFormats,
        private readonly array $lComponentTypes,
        private readonly array $lHashAlgorithms,
        private readonly string $sHashContentRegex,
        private readonly array $lExternalReferenceTypes,
        private readonly bool $bLicenseExpression,
        private readonly bool $bMetadata,
        private readonly bool $bBomRef,
        private readonly bool $bDependencies,
        private readonly bool $bExternalReferenceHashes,
        private readonly bool $bComponentVersionMandatory,
        private readonly bool $bToolExternalReferences,
        private readonly bool $bMetadataProperties,
        private readonly bool $bComponentAuthor,
        private readonly bool $bComponentProperties,
        private readonly bool $bComponentEvidence,
        private readonly array $lFormatsSupportingBomProperties,
        LicenseIdentifiers $licenseIdentifiers = new LicenseIdentifiers()
    ) {
        $this->lLicenseIdentifiers = $licenseIdentifiers->getKnownLicenses();
    }

    public function getVersion(): Version
    {
        return $this->version;
    }

    public function isSupportedFormat(Format $format): bool
    {
        return \in_array($format, $this->lFormats, true);
    }

    public function isSupportedComponentType(ComponentType $componentType): bool
    {
        return \in_array($componentType, $this->lComponentTypes, true);
    }

    public function isSupportedHashAlgorithm(HashAlgorithm $alg): bool
    {
        return \in_array($alg, $this->lHashAlgorithms, true);
    }

    public function isSupportedHashContent(string $content): bool
    {
        return 1 === preg_match($this->sHashContentRegex, $content);
    }

    public function isSupportedExternalReferenceType(ExternalReferenceType $referenceType): bool
    {
        return \in_array($referenceType, $this->lExternalReferenceTypes, true);
    }

    public function isSupportedLicenseIdentifier(string $licenseIdentifier): bool
    {
        return \in_array($licenseIdentifier, $this->lLicenseIdentifiers, true);
    }

    public function supportsLicenseExpression(): bool
    {
        return $this->bLicenseExpression;
    }

    public function supportsMetadata(): bool
    {
        return $this->bMetadata;
    }

    public function supportsBomRef(): bool
    {
        return $this->bBomRef;
    }

    public function supportsDependencies(): bool
    {
        return $this->bDependencies;
    }

    public function supportsExternalReferenceHashes(): bool
    {
        return $this->bExternalReferenceHashes;
    }

    public function requiresComponentVersion(): bool
    {
        return $this->bComponentVersionMandatory;
    }

    public function supportsToolExternalReferences(): bool
    {
        return $this->bToolExternalReferences;
    }

    public function supportsMetadataProperties(): bool
    {
        return $this->bMetadataProperties;
    }

    public function supportsComponentAuthor(): bool
    {
        return $this->bComponentAuthor;
    }

    public function supportsComponentProperties(): bool
    {
        return $this->bComponentProperties;
    }

    public function supportsComponentEvidence(): bool
    {
        return $this->bComponentEvidence;
    }

    public function supportsBomProperties(Format $format): bool
    {
        return \in_array($format, $this->lFormatsSupportingBomProperties, true);
    }
}
