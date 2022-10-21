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

namespace CycloneDX\Core\Factories;

use CycloneDX\Core\Models\License\DisjunctiveLicenseWithId;
use CycloneDX\Core\Models\License\LicenseExpression;
use CycloneDX\Core\Models\License\NamedLicense;
use CycloneDX\Core\Spdx\LicenseValidator as SpdxLicenseValidator;
use DomainException;
use UnexpectedValueException;

class LicenseFactory
{
    private ?SpdxLicenseValidator $spdxLicenseValidator;

    public function __construct(?SpdxLicenseValidator $spdxLicenseValidator = null)
    {
        $this->spdxLicenseValidator = $spdxLicenseValidator;
    }

    /**
     * @psalm-assert SpdxLicenseValidator $this->spdxLicenseValidator
     *
     * @throws UnexpectedValueException when SpdxLicenseValidator is missing
     */
    public function getSpdxLicenseValidator(): SpdxLicenseValidator
    {
        return $this->spdxLicenseValidator
            ?? throw new UnexpectedValueException('Missing spdxLicenseValidator');
    }

    public function setSpdxLicenseValidator(SpdxLicenseValidator $spdxLicenseValidator): self
    {
        $this->spdxLicenseValidator = $spdxLicenseValidator;

        return $this;
    }

    public function makeFromString(string $license): NamedLicense|DisjunctiveLicenseWithId|LicenseExpression
    {
        try {
            return $this->makeExpression($license);
        } catch (DomainException) {
            return $this->makeDisjunctive($license);
        }
    }

    /**
     * @throws DomainException if the expression was invalid
     */
    public function makeExpression(string $license): LicenseExpression
    {
        return new LicenseExpression($license);
    }

    public function makeDisjunctive(string $license): DisjunctiveLicenseWithId|NamedLicense
    {
        try {
            return $this->makeDisjunctiveWithId($license);
        } catch (UnexpectedValueException|DomainException) {
            return $this->makeDisjunctiveWithName($license);
        }
    }

    /**
     * @throws DomainException          when the SPDX license is invalid
     * @throws UnexpectedValueException when SpdxLicenseValidator is missing
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function makeDisjunctiveWithId(string $license): DisjunctiveLicenseWithId
    {
        return DisjunctiveLicenseWithId::makeValidated($license, $this->getSpdxLicenseValidator());
    }

    public function makeDisjunctiveWithName(string $license): NamedLicense
    {
        return new NamedLicense($license);
    }
}
