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

namespace CycloneDX\Core\Models\License;

use CycloneDX\Core\Spdx\LicenseValidator;
use DomainException;

/**
 * Disjunctive license with (SPDX-)ID - aka SpdxLicense.
 *
 * @author jkowalleck
 */
class DisjunctiveLicenseWithId
{
    use _DisjunctiveLicenseBase;

    /**
     * A valid SPDX license ID.
     *
     * @see \CycloneDX\Core\Spdx\LicenseValidator::validate()
     */
    private string $id;

    public function getId(): string
    {
        return $this->id;
    }

    private function __construct(string $id)
    {
        $this->id = $id;
    }

    /**
     * @param string $id SPDX-ID of a license
     *
     * @throws DomainException when the SPDX license is invalid
     *
     * @see \CycloneDX\Core\Spdx\LicenseValidator::getLicense()
     * @see \CycloneDX\Core\Spdx\LicenseValidator::validate()
     */
    public static function makeValidated(string $id, LicenseValidator $spdxLicenseValidator): self
    {
        $validId = $spdxLicenseValidator->getLicense($id)
            ?? throw new DomainException("Invalid SPDX license: $id");

        return new self($validId);
    }
}
