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
use DomainException;

/**
 * Factory for {@see \CycloneDX\Core\Spec\Spec Specification} objects.
 */
abstract class SpecFactory
{
    /* IDEA
    // have the instances in a static const cache, tht is held by WeakReferences
    // see https://www.php.net/manual/en/class.weakreference.php
    private const $cache = []
    $spec = $cache[Version::v1dot1]?->get();
    if (null === $spec) {
        $spec = new Spec();
        $cache[Version::v1dot1] = WeakReference::create($spec);
    }
    return $spec
    ...
    */

    /**
     * Create the appropriate {@see \CycloneDX\Core\Spec\Spec Specification} based on {@see \CycloneDX\Core\Spec\Version}.
     *
     * @throws DomainException when $version was unsupported
     */
    public static function makeForVersion(Version $version): Spec
    {
        return match ($version) {
            Version::v1dot1 => self::make1dot1(),
            Version::v1dot2 => self::make1dot2(),
            Version::v1dot3 => self::make1dot3(),
            Version::v1dot4 => self::make1dot4(),
            /* just in case fallback */
            default => throw new DomainException("unsupported version: $version->name"),
        };
    }

    /**
     * Create the {@see \CycloneDX\Core\Spec\Spec Specification} based on {@see \CycloneDX\Core\Spec\Version::v1dot1}.
     */
    public static function make1dot1(): Spec
    {
        return new _Spec(
            Version::v1dot1,
            [
                Format::XML,
            ],
            [
                ComponentType::Application,
                ComponentType::Framework,
                ComponentType::Library,
                ComponentType::OperatingSystem,
                ComponentType::Device,
                ComponentType::File,
            ],
            [
                HashAlgorithm::MD5,
                HashAlgorithm::SHA_1,
                HashAlgorithm::SHA_256,
                HashAlgorithm::SHA_384,
                HashAlgorithm::SHA_512,
                HashAlgorithm::SHA3_256,
                HashAlgorithm::SHA3_512,
            ],
            '/^(?:[a-fA-F0-9]{32}|[a-fA-F0-9]{40}|[a-fA-F0-9]{64}|[a-fA-F0-9]{96}|[a-fA-F0-9]{128})$/',
            [
                ExternalReferenceType::VCS,
                ExternalReferenceType::IssueTracker,
                ExternalReferenceType::Website,
                ExternalReferenceType::Advisories,
                ExternalReferenceType::BOM,
                ExternalReferenceType::MailingList,
                ExternalReferenceType::Social,
                ExternalReferenceType::Chat,
                ExternalReferenceType::Documentation,
                ExternalReferenceType::Support,
                ExternalReferenceType::Distribution,
                ExternalReferenceType::License,
                ExternalReferenceType::BuildMeta,
                ExternalReferenceType::BuildSystem,
                ExternalReferenceType::Other,
            ],
            true,
            false,
            false,
            false,
            false,
            true,
            false,
            false,
            false,
            false,
            false,
            [],
        );
    }

    /**
     * Create the {@see \CycloneDX\Core\Spec\Spec Specification} based on {@see \CycloneDX\Core\Spec\Version::v1dot2}.
     */
    public static function make1dot2(): Spec
    {
        return new _Spec(
            Version::v1dot2,
            [
                Format::XML,
                Format::JSON,
            ],
            [
                ComponentType::Application,
                ComponentType::Framework,
                ComponentType::Library,
                ComponentType::OperatingSystem,
                ComponentType::Device,
                ComponentType::File,
                ComponentType::Container,
                ComponentType::Firmware,
            ],
            [
                HashAlgorithm::MD5,
                HashAlgorithm::SHA_1,
                HashAlgorithm::SHA_256,
                HashAlgorithm::SHA_384,
                HashAlgorithm::SHA_512,
                HashAlgorithm::SHA3_256,
                HashAlgorithm::SHA3_384,
                HashAlgorithm::SHA3_512,
                HashAlgorithm::BLAKE2b_256,
                HashAlgorithm::BLAKE2b_384,
                HashAlgorithm::BLAKE2b_512,
                HashAlgorithm::BLAKE3,
            ],
            '/^(?:[a-fA-F0-9]{32}|[a-fA-F0-9]{40}|[a-fA-F0-9]{64}|[a-fA-F0-9]{96}|[a-fA-F0-9]{128})$/',
            [
                ExternalReferenceType::VCS,
                ExternalReferenceType::IssueTracker,
                ExternalReferenceType::Website,
                ExternalReferenceType::Advisories,
                ExternalReferenceType::BOM,
                ExternalReferenceType::MailingList,
                ExternalReferenceType::Social,
                ExternalReferenceType::Chat,
                ExternalReferenceType::Documentation,
                ExternalReferenceType::Support,
                ExternalReferenceType::Distribution,
                ExternalReferenceType::License,
                ExternalReferenceType::BuildMeta,
                ExternalReferenceType::BuildSystem,
                ExternalReferenceType::Other,
            ],
            true,
            true,
            true,
            true,
            false,
            true,
            false,
            false,
            true,
            false,
            false,
            [],
        );
    }

    /**
     * Create the {@see \CycloneDX\Core\Spec\Spec Specification} based on {@see \CycloneDX\Core\Spec\Version::v1dot3}.
     */
    public static function make1dot3(): Spec
    {
        return new _Spec(
            Version::v1dot3,
            [
                Format::XML,
                Format::JSON,
            ],
            [
                ComponentType::Application,
                ComponentType::Framework,
                ComponentType::Library,
                ComponentType::OperatingSystem,
                ComponentType::Device,
                ComponentType::File,
                ComponentType::Container,
                ComponentType::Firmware,
            ],
            [
                HashAlgorithm::MD5,
                HashAlgorithm::SHA_1,
                HashAlgorithm::SHA_256,
                HashAlgorithm::SHA_384,
                HashAlgorithm::SHA_512,
                HashAlgorithm::SHA3_256,
                HashAlgorithm::SHA3_384,
                HashAlgorithm::SHA3_512,
                HashAlgorithm::BLAKE2b_256,
                HashAlgorithm::BLAKE2b_384,
                HashAlgorithm::BLAKE2b_512,
                HashAlgorithm::BLAKE3,
            ],
            '/^(?:[a-fA-F0-9]{32}|[a-fA-F0-9]{40}|[a-fA-F0-9]{64}|[a-fA-F0-9]{96}|[a-fA-F0-9]{128})$/',
            [
                ExternalReferenceType::VCS,
                ExternalReferenceType::IssueTracker,
                ExternalReferenceType::Website,
                ExternalReferenceType::Advisories,
                ExternalReferenceType::BOM,
                ExternalReferenceType::MailingList,
                ExternalReferenceType::Social,
                ExternalReferenceType::Chat,
                ExternalReferenceType::Documentation,
                ExternalReferenceType::Support,
                ExternalReferenceType::Distribution,
                ExternalReferenceType::License,
                ExternalReferenceType::BuildMeta,
                ExternalReferenceType::BuildSystem,
                ExternalReferenceType::Other,
            ],
            true,
            true,
            true,
            true,
            true,
            true,
            false,
            true,
            true,
            true,
            true,
            [
                Format::XML,
            ],
        );
    }

    /**
     * Create the {@see \CycloneDX\Core\Spec\Spec Specification} based on {@see \CycloneDX\Core\Spec\Version::v1dot4}.
     */
    public static function make1dot4(): Spec
    {
        return new _Spec(
            Version::v1dot4,
            [
                Format::XML,
                Format::JSON,
            ],
            [
                ComponentType::Application,
                ComponentType::Framework,
                ComponentType::Library,
                ComponentType::OperatingSystem,
                ComponentType::Device,
                ComponentType::File,
                ComponentType::Container,
                ComponentType::Firmware,
            ],
            [
                HashAlgorithm::MD5,
                HashAlgorithm::SHA_1,
                HashAlgorithm::SHA_256,
                HashAlgorithm::SHA_384,
                HashAlgorithm::SHA_512,
                HashAlgorithm::SHA3_256,
                HashAlgorithm::SHA3_384,
                HashAlgorithm::SHA3_512,
                HashAlgorithm::BLAKE2b_256,
                HashAlgorithm::BLAKE2b_384,
                HashAlgorithm::BLAKE2b_512,
                HashAlgorithm::BLAKE3,
            ],
            '/^(?:[a-fA-F0-9]{32}|[a-fA-F0-9]{40}|[a-fA-F0-9]{64}|[a-fA-F0-9]{96}|[a-fA-F0-9]{128})$/',
            [
                ExternalReferenceType::VCS,
                ExternalReferenceType::IssueTracker,
                ExternalReferenceType::Website,
                ExternalReferenceType::Advisories,
                ExternalReferenceType::BOM,
                ExternalReferenceType::MailingList,
                ExternalReferenceType::Social,
                ExternalReferenceType::Chat,
                ExternalReferenceType::Documentation,
                ExternalReferenceType::Support,
                ExternalReferenceType::Distribution,
                ExternalReferenceType::License,
                ExternalReferenceType::BuildMeta,
                ExternalReferenceType::BuildSystem,
                ExternalReferenceType::ReleaseNotes,
                ExternalReferenceType::Other,
            ],
            true,
            true,
            true,
            true,
            true,
            false,
            true,
            true,
            true,
            true,
            true,
            [
                Format::XML,
            ],
        );
    }
}
