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

namespace CycloneDX\Core\Serialize\JSON;

use CycloneDX\Core\Spec\Format;
use CycloneDX\Core\Spec\SpecInterface;
use DomainException;

/**
 * @author jkowalleck
 */
class NormalizerFactory
{
    public const FORMAT = Format::JSON;

    /**
     * @readonly
     */
    private SpecInterface $spec;

    /**
     * @throws DomainException when the spec does not support JSON format
     */
    public function __construct(SpecInterface $spec)
    {
        $this->spec = $spec->isSupportedFormat(self::FORMAT)
            ? $spec
            : throw new DomainException('Unsupported format "'.self::FORMAT.'" for spec '.$spec->getVersion());
    }

    public function getSpec(): SpecInterface
    {
        return $this->spec;
    }

    public function makeForBom(): Normalizers\BomNormalizer
    {
        return new Normalizers\BomNormalizer($this);
    }

    public function makeForComponentRepository(): Normalizers\ComponentRepositoryNormalizer
    {
        return new Normalizers\ComponentRepositoryNormalizer($this);
    }

    public function makeForComponent(): Normalizers\ComponentNormalizer
    {
        return new Normalizers\ComponentNormalizer($this);
    }

    public function makeForLicense(): Normalizers\LicenseNormalizer
    {
        return new Normalizers\LicenseNormalizer($this);
    }

    public function makeForLicenseRepository(): Normalizers\LicenseRepositoryNormalizer
    {
        return new Normalizers\LicenseRepositoryNormalizer($this);
    }

    public function makeForHashDictionary(): Normalizers\HashDictionaryNormalizer
    {
        return new Normalizers\HashDictionaryNormalizer($this);
    }

    public function makeForHash(): Normalizers\HashNormalizer
    {
        return new Normalizers\HashNormalizer($this);
    }

    public function makeForMetaData(): Normalizers\MetaDataNormalizer
    {
        return new Normalizers\MetaDataNormalizer($this);
    }

    public function makeForToolRepository(): Normalizers\ToolRepositoryNormalizer
    {
        return new Normalizers\ToolRepositoryNormalizer($this);
    }

    public function makeForTool(): Normalizers\ToolNormalizer
    {
        return new Normalizers\ToolNormalizer($this);
    }

    public function makeForDependencies(): Normalizers\DependenciesNormalizer
    {
        return new Normalizers\DependenciesNormalizer($this);
    }

    public function makeForExternalReference(): Normalizers\ExternalReferenceNormalizer
    {
        return new Normalizers\ExternalReferenceNormalizer($this);
    }

    public function makeForExternalReferenceRepository(): Normalizers\ExternalReferenceRepositoryNormalizer
    {
        return new Normalizers\ExternalReferenceRepositoryNormalizer($this);
    }
}
