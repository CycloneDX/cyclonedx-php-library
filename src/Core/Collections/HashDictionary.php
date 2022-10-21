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
use CycloneDX\Core\Enums\HashAlgorithm;
use DomainException;

/**
 * Dictionary of {@see \CycloneDX\Core\Enums\HashAlgorithm} => `$content:string`.
 *
 * @author jkowalleck
 */
class HashDictionary implements Countable
{
    /**
     * @var string[] dictionary of hashes
     *
     * @psalm-var  array<HashAlgorithm::*,string>
     */
    private array $items = [];

    /**
     * Ignores unknown hash algorithms.
     *
     * @param string[] $items dictionary of hashes. Valid keys are {@see \CycloneDX\Core\Enums\HashAlgorithm}
     *
     * @psalm-param array<string,string> $items
     */
    public function __construct(array $items = [])
    {
        $this->setItems($items);
    }

    /**
     * Set the hashes.
     * Ignores unknown hash algorithms.
     *
     * @param string[] $items dictionary of hashes. Valid keys are {@see \CycloneDX\Core\Enums\HashAlgorithm}
     *
     * @psalm-param array<string,string> $items
     *
     * @return $this
     */
    public function setItems(array $items): self
    {
        foreach ($items as $algorithm => $content) {
            try {
                $this->set($algorithm, $content);
            } catch (DomainException) {
                // pass
            }
        }

        return $this;
    }

    /**
     * @return string[] dictionary of hashes
     *
     * @psalm-return array<HashAlgorithm::*,string>
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @psalm-assert HashAlgorithm::* $algorithm
     *
     * @throws DomainException if $algorithm is not in {@see \CycloneDX\Core\Enums\HashAlgorithm}'s constants list
     *
     * @return $this
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function set(string $algorithm, ?string $content): self
    {
        if (false === HashAlgorithm::isValidValue($algorithm)) {
            throw new DomainException("Unknown hash algorithm: $algorithm");
        }

        if (null === $content) {
            unset($this->items[$algorithm]);
        } else {
            $this->items[$algorithm] = $content;
        }

        return $this;
    }

    /**
     * @psalm-param HashAlgorithm::*|string $algorithm
     */
    public function get(string $algorithm): ?string
    {
        return $this->items[$algorithm] ?? null;
    }

    /**
     * @psalm-return 0|positive-int
     */
    public function count(): int
    {
        return \count($this->items);
    }
}
