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

use Countable;
use CycloneDX\Core\Models\Component;

/**
 * Unique collection of {@see Component}.
 *
 * @author jkowalleck
 */
class ComponentRepository implements Countable
{
    /**
     * @var Component[]
     *
     * @psalm-var list<Component>
     */
    private array $items = [];

    public function __construct(Component ...$items)
    {
        $this->addItems(...$items);
    }

    /**
     * @return $this
     */
    public function addItems(Component ...$items): static
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
     * @return Component[]
     *
     * @psalm-return list<Component>
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

    /**
     * @return Component[]
     *
     * @psalm-return list<Component>
     */
    public function findItem(string $name, ?string $group): array
    {
        if ('' === $group) {
            $group = null;
        }

        return array_values(
            array_filter(
                $this->items,
                static fn (Component $c): bool => $c->getName() === $name && $c->getGroup() === $group
            )
        );
    }
}
