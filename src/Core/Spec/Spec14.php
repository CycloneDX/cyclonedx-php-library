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

namespace CycloneDX\Core\Spec;

use CycloneDX\Core\Enums\Classification;
use CycloneDX\Core\Enums\ExternalReferenceType;
use CycloneDX\Core\Enums\HashAlgorithm;

/**
 * @author jkowalleck
 */
final class Spec14 implements SpecInterface
{
    use SpecTrait;

    private const VERSION = Version::V_1_4;

    private const FORMATS = [
        Format::XML,
        Format::JSON,
    ];

    // @TODO
    private const COMPONENT_TYPES = [
        Classification::APPLICATION,
        Classification::FRAMEWORK,
        Classification::LIBRARY,
        Classification::OPERATING_SYSTEMS,
        Classification::DEVICE,
        Classification::FILE,
        Classification::CONTAINER,
        Classification::FIRMWARE,
    ];

    // @TODO
    private const HASH_ALGORITHMS = [
        HashAlgorithm::MD5,
        HashAlgorithm::SHA_1,
        HashAlgorithm::SHA_256,
        HashAlgorithm::SHA_384,
        HashAlgorithm::SHA_512,
        HashAlgorithm::SHA3_256,
        HashAlgorithm::SHA3_384,
        HashAlgorithm::SHA3_512,
        HashAlgorithm::BLAKE2B_256,
        HashAlgorithm::BLAKE2B_384,
        HashAlgorithm::BLAKE2B_512,
        HashAlgorithm::BLAKE3,
    ];

    private const EXTERNAL_REFERENCE_TYPES = [
        ExternalReferenceType::VCS,
        ExternalReferenceType::ISSUE_TRACKER,
        ExternalReferenceType::WEBSITE,
        ExternalReferenceType::ADVISORIES,
        ExternalReferenceType::BOM,
        ExternalReferenceType::MAILING_LIST,
        ExternalReferenceType::SOCIAL,
        ExternalReferenceType::CHAT,
        ExternalReferenceType::DOCUMENTATION,
        ExternalReferenceType::SUPPORT,
        ExternalReferenceType::DISTRIBUTION,
        ExternalReferenceType::LICENSE,
        ExternalReferenceType::BUILD_META,
        ExternalReferenceType::BUILD_SYSTEM,
        ExternalReferenceType::RELEASE_NOTES,
        ExternalReferenceType::OTHER,
    ];

    // @TODO
    private const HASH_CONTENT_REGEX = '/^(?:[a-fA-F0-9]{32}|[a-fA-F0-9]{40}|[a-fA-F0-9]{64}|[a-fA-F0-9]{96}|[a-fA-F0-9]{128})$/';

    public function supportsLicenseExpression(): bool
    {
        return true;
    }

    public function supportsMetaData(): bool
    {
        return true;
    }

    public function supportsBomRef(): bool
    {
        return true;
    }

    public function supportsDependencies(): bool
    {
        return true;
    }

    public function supportsExternalReferenceHashes(): bool
    {
        return true;
    }

    public function requiresComponentVersion(): bool
    {
        return false;
    }
}
