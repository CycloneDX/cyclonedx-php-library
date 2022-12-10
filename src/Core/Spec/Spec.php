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

/**
 * See {@see \CycloneDX\Core\Spec\SpecFactory Specification Factory} to get prepared instances.
 *
 * @author jkowalleck
 */
interface Spec
{
    /** @psalm-return Version::* */
    public function getVersion(): string;

    public function isSupportedFormat(string $format): bool;

    public function isSupportedComponentType(string $componentType): bool;

    public function isSupportedHashAlgorithm(string $alg): bool;

    public function isSupportedHashContent(string $content): bool;

    public function isSupportedExternalReferenceType(string $referenceType): bool;

    public function supportsLicenseExpression(): bool;

    public function supportsMetadata(): bool;

    public function supportsBomRef(): bool;

    public function supportsDependencies(): bool;

    public function supportsExternalReferenceHashes(): bool;

    public function requiresComponentVersion(): bool;

    public function supportsToolExternalReferences(): bool;

    public function supportsMetadataProperties(): bool;

    public function supportsComponentAuthor(): bool;

    public function supportsComponentProperties(): bool;
}
