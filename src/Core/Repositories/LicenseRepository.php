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

namespace CycloneDX\Core\Repositories;

use CycloneDX\Core\Models\License\DisjunctiveLicenseWithId;
use CycloneDX\Core\Models\License\DisjunctiveLicenseWithName;
use CycloneDX\Core\Models\License\LicenseExpression;

/**
 * @author jkowalleck
 */
class LicenseRepository implements \Countable
{
    /**
     * @var DisjunctiveLicenseWithId[]|DisjunctiveLicenseWithName[]|LicenseExpression[]
     *
     * @psalm-var list<DisjunctiveLicenseWithId|DisjunctiveLicenseWithName|LicenseExpression>
     */
    private array $licenses = [];

    /**
     * Unsupported Licenses are filtered out silently.
     */
    public function __construct(DisjunctiveLicenseWithId|DisjunctiveLicenseWithName|LicenseExpression ...$licenses)
    {
        $this->addLicense(...$licenses);
    }

    /**
     * Add supported licenses.
     * Unsupported Licenses are filtered out silently.
     *
     * @return $this
     */
    public function addLicense(DisjunctiveLicenseWithId|DisjunctiveLicenseWithName|LicenseExpression ...$licenses): self
    {
        foreach ($licenses as $license) {
            if (\in_array($license, $this->licenses, true)) {
                continue;
            }
            $this->licenses[] = $license;
        }

        return $this;
    }

    /**
     * @return DisjunctiveLicenseWithId[]|DisjunctiveLicenseWithName[]|LicenseExpression[]
     *
     * @psalm-return list<DisjunctiveLicenseWithId|DisjunctiveLicenseWithName|LicenseExpression>
     */
    public function getLicenses(): array
    {
        return $this->licenses;
    }

    public function count(): int
    {
        return \count($this->licenses);
    }
}
