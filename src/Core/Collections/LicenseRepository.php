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

namespace CycloneDX\Core\Collections;

use CycloneDX\Core\Models\License\DisjunctiveLicenseWithId;
use CycloneDX\Core\Models\License\DisjunctiveLicenseWithName;
use CycloneDX\Core\Models\License\LicenseExpression;

/**
 * Unique collection of {@see \CycloneDX\Core\Models\License\DisjunctiveLicenseWithId},
 * {@see \CycloneDX\Core\Models\License\DisjunctiveLicenseWithName} and
 * {@see \CycloneDX\Core\Models\License\LicenseExpression}.
 *
 * @author jkowalleck
 */
class LicenseRepository implements \Countable
{
    /**
     * @var DisjunctiveLicenseWithId[]|DisjunctiveLicenseWithName[]|LicenseExpression[]
     *
     * @psalm-var list<DisjunctiveLicenseWithId|DisjunctiveLicenseWithName|LicenseExpression>
     */
    private array $items = [];

    /**
     * Unsupported Licenses are filtered out silently.
     */
    public function __construct(DisjunctiveLicenseWithId|DisjunctiveLicenseWithName|LicenseExpression ...$items)
    {
        $this->addItems(...$items);
    }

    /**
     * Add supported licenses.
     * Unsupported Licenses are filtered out silently.
     *
     * @return $this
     */
    public function addItems(DisjunctiveLicenseWithId|DisjunctiveLicenseWithName|LicenseExpression ...$items): self
    {
        foreach ($items as $item) {
            if (\in_array($item, $this->items, true)) {
                continue;
            }
            $this->items[] = $item;
        }

        return $this;
    }

    /**
     * @return DisjunctiveLicenseWithId[]|DisjunctiveLicenseWithName[]|LicenseExpression[]
     *
     * @psalm-return list<DisjunctiveLicenseWithId|DisjunctiveLicenseWithName|LicenseExpression>
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @psalm-return 0|positive-int
     */
    public function count(): int
    {
        return \count($this->items);
    }
}
