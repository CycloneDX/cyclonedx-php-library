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

use CycloneDX\Core\Models\Tool;

/**
 * Unique collection of {@see \CycloneDX\Core\Models\Tool}.
 *
 * @author jkowalleck
 */
class ToolRepository implements \Countable
{
    /**
     * @var Tool[]
     *
     * @psalm-var list<Tool>
     */
    private array $items = [];

    public function __construct(Tool ...$items)
    {
        $this->addItems(...$items);
    }

    /**
     * @return $this
     */
    public function addItems(Tool ...$items): self
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
     * @return Tool[]
     *
     * @psalm-return list<Tool>
     */
    public function getItems(): array
    {
        return $this->items;
    }

    public function count(): int
    {
        return \count($this->items);
    }
}
