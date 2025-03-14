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

namespace CycloneDX\Core\Validation;

use CycloneDX\Core\Spec\_SpecProtocol as Spec;

/**
 * @author jkowalleck
 */
abstract class BaseValidator implements Validator
{
    public function __construct(
        /* @TODO in next major version: use `\CycloneDX\Core\Enums\Version` instead of `Spec` */
        private readonly Spec $spec,
    ) {
    }

    public function getSpec(): Spec
    {
        return $this->spec;
    }

    /**
     * @throws Exceptions\FailedLoadingSchemaException when schema file unknown or not readable
     */
    protected function getSchemaFile(): string
    {
        $specVersion = $this->spec->getVersion();
        $schemaFile = static::listSchemaFiles()[$specVersion->value] ?? null;
        if (false === \is_string($schemaFile)) {
            throw new Exceptions\FailedLoadingSchemaException("Schema file unknown for specVersion: $specVersion->name");
        }
        $schemaPath = realpath($schemaFile);
        if (\is_string($schemaPath) && is_file($schemaPath) && is_readable($schemaPath)) {
            return $schemaPath;
        }
        // @codeCoverageIgnoreStart
        throw new Exceptions\FailedLoadingSchemaException("Schema file not readable: $schemaFile");
        // @codeCoverageIgnoreEnd
    }

    /**
     * @return string[]|null[] dictionary ala `[ CycloneDX\Core\Spec\Version::value() => string ]`
     *
     * @psalm-return array<string, ?string>
     */
    abstract protected static function listSchemaFiles(): array;
}
