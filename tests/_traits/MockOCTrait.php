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

namespace CycloneDX\Tests\_traits;

use PHPUnit\Event\NoPreviousThrowableException;
use PHPUnit\Framework\InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @psalm-require-extends \PHPUnit\Framework\TestCase
 */
trait MockOCTrait
{
    /**
     * @psalm-template RealInstanceType of object
     *
     * @psalm-param class-string<RealInstanceType> $originalClassName
     *
     * @psalm-return MockObject&RealInstanceType
     *
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws InvalidArgumentException
     * @throws NoPreviousThrowableException
     */
    private function createMockObjectOC(string $originalClassName, array $constructorArgs, bool $register = true): MockObject
    {
        /* @var \PHPUnit\Framework\TestCase $this */
        return $this->getMockBuilder($originalClassName)
            ->enableOriginalConstructor()
            ->setConstructorArgs($constructorArgs)
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->getMock($register);
    }

    /**
     * @psalm-template RealInstanceType of object
     *
     * @psalm-param class-string<RealInstanceType> $originalClassName
     *
     * @psalm-return MockObject&RealInstanceType
     *
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws InvalidArgumentException
     * @throws NoPreviousThrowableException
     */
    protected function createMockOC(string $originalClassName, array $constructorArgs): MockObject
    {
        return $this->createConfiguredMockOC($originalClassName, $constructorArgs, []);
    }

    /**
     * @psalm-template RealInstanceType of object
     *
     * @psalm-param class-string<RealInstanceType> $originalClassName
     *
     * @psalm-return MockObject&RealInstanceType
     *
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws InvalidArgumentException
     * @throws NoPreviousThrowableException
     */
    protected function createConfiguredMockOC(string $originalClassName, array $constructorArgs, array $configuration): MockObject
    {
        $o = $this->createMockObjectOC($originalClassName, $constructorArgs);

        foreach ($configuration as $method => $return) {
            $o->method($method)->willReturn($return);
        }

        return $o;
    }
}
