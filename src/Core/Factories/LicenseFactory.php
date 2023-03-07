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

use CycloneDX\Core\Models\License\LicenseExpression;
use CycloneDX\Core\Models\License\NamedLicense;
use CycloneDX\Core\Models\License\SpdxLicense;
use CycloneDX\Core\Spdx\SpdxLicenses as CdxSpdxLicenses;
use Composer\Spdx\SpdxLicenses as ComposerSpdxLicenses;
use DomainException;

class LicenseFactory
{

    public function __construct(
        readonly private CdxSpdxLicenses $cdxSpdxLicenses,
        readonly private ComposerSpdxLicenses $spdxLicenses,
    )
    {
    }

    public function makeFromString(string $license): SpdxLicense|LicenseExpression|NamedLicense
    {
        try {
            return $this->makeSpdxLicense($license);
        } catch (DomainException) {
            if (preg_match('/ WITH | AND | OR /', $license) === 1) {
                try {
                    return $this->makeExpression($license);
                } catch (DomainException) {
                    /* pass */
                }
            }
        }
        return $this->makeNamedLicense($license);
    }

    public function makeDisjunctive(string $license): SpdxLicense|NamedLicense
    {
        try {
            return $this->makeSpdxLicense($license);
        } catch (DomainException) {
            return $this->makeNamedLicense($license);
        }
    }

    /**
     * @throws DomainException if the expression was invalid
     */
    public function makeExpression(string $license): LicenseExpression
    {
        if ($this->spdxLicenses->validate($license))  {
            return new LicenseExpression($license);
        }

        throw new DomainException("invalid SPDX expression: $license");
    }

    /**
     * @throws DomainException when the SPDX license is invalid*
     */
    public function makeSpdxLicense(string $license): SpdxLicense
    {
        $licenseFixed = $this->cdxSpdxLicenses->getLicense($license);
        if (null === $licenseFixed) {
            throw new DomainException("unknown SPDX license : $license");
        }
        return         new SpdxLicense($licenseFixed);
    }

    public function makeNamedLicense(string $license): NamedLicense
    {
        return new NamedLicense($license);
    }
}
