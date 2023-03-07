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

namespace CycloneDX\Core\Spdx;

use CycloneDX\Core\Resources;
use JsonException;
use RuntimeException;

/**
 * Work with SPDX licences known to CycloneDX.
 *
 * @author jkowalleck
 */
class SpdxLicenses
{
    /**
     * @var string[]|null
     *
     * @psalm-var array<string, string>|null
     */
    private ?array $licenses = null;

    public function getResourcesFile(): string
    {
        return realpath(Resources::FILE_SPDX_JSON_SCHEMA);
    }

    /**
     * @throws RuntimeException when licenses could not be loaded
     *
     * @return string[]
     */
    public function getKnownLicenses(): array
    {
        $this->loadLicenses();

        return array_values($this->licenses);
    }

    /**
     * @throws RuntimeException when licenses could not be loaded
     */
    public function validate(string $identifier): bool
    {
        $this->loadLicenses();

        return \in_array($identifier, $this->licenses, true);
    }

    /**
     * Return the "fixed" supported SPDX license id, or null if unsupported.
     *
     * @throws RuntimeException when licenses could not be loaded
     */
    public function getLicense(string $identifier): ?string
    {
        $this->loadLicenses();

        return $this->licenses[strtolower($identifier)] ?? null;
    }

    /**
     * @psalm-assert array<string, string> $this->licenses
     *
     * @throws RuntimeException when licenses could not be loaded
     */
    private function loadLicenses(): void
    {
        if (null !== $this->licenses) {
            // @codeCoverageIgnoreStart
            return;
            // @codeCoverageIgnoreEnd
        }

        $file = $this->getResourcesFile();
        $json = file_exists($file)
            ? file_get_contents($file)
            : throw new RuntimeException("Missing licenses file: $file");
        if (false === $json) {
            throw new RuntimeException("Failed to get content from licenses file: $file");
        }

        try {
            /**
             * list of strings, as asserted by an integration test:
             * {@see \CycloneDX\Tests\Core\Spdx\LicenseValidatorTest::testShippedLicensesFile()}.
             *
             * @var string[] $licenses
             *
             * @psalm-suppress MixedArrayAccess
             * @psalm-suppress MixedAssignment
             */
            ['enum' => $licenses] = json_decode($json, true, 3, \JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RuntimeException("Malformed licenses file: $file", previous: $exception);
        }

        $this->licenses = array_combine(
            array_map(strtolower(...), $licenses),
            $licenses
        );
    }
}
