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

namespace CycloneDX\Tests\Core\Serialization;

use CycloneDX\Core\Collections\BomRefRepository;
use CycloneDX\Core\Collections\ComponentRepository;
use CycloneDX\Core\Enums\ComponentType;
use CycloneDX\Core\Models\Bom;
use CycloneDX\Core\Models\BomRef;
use CycloneDX\Core\Models\Component;
use CycloneDX\Core\Models\Metadata;
use Generator;

abstract class _TestCommon
{
    public static function BomsForDpNormalize(): Generator
    {
        $dependencies = new BomRefRepository();

        $componentWithoutBomRefValue = (new Component(ComponentType::Library, 'WithoutBomRefValue'))
            ->setBomRefValue(null)
            ->setDependencies($dependencies);

        $componentWithoutDeps = (new Component(ComponentType::Library, 'WithoutDeps'))
            ->setBomRefValue('ComponentWithoutDeps')
            ->setDependencies($dependencies);
        $componentWithNoDeps = (new Component(ComponentType::Library, 'WithoutDeps'))
            ->setBomRefValue('ComponentWithNoDeps')
            ->setDependencies(new BomRefRepository());
        $componentWithDeps = (new Component(ComponentType::Library, 'WithoutDeps'))
            ->setBomRefValue('ComponentWithDeps')
            ->setDependencies(new BomRefRepository($componentWithoutDeps->getBomRef(),
                $componentWithNoDeps->getBomRef(), ));
        $rootComponent = (new Component(ComponentType::Library, 'WithoutDeps'))
            ->setBomRefValue('myRootComponent')
            ->setDependencies(new BomRefRepository(
                $componentWithDeps->getBomRef(),
                $componentWithoutDeps->getBomRef(),
                $componentWithoutBomRefValue->getBomRef(),
                new BomRef('SomethingOutsideOfTheActualBom'),
            ));

        yield 'with metadata' => (new Bom())
            ->setComponents(new ComponentRepository(
                $componentWithoutDeps,
                $componentWithNoDeps,
                $componentWithDeps,
                $componentWithoutBomRefValue))
            ->setMetadata((new Metadata())->setComponent($rootComponent));
    }
}
