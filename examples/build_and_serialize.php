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

require_once __DIR__.'/../vendor/autoload.php';

// Example how to serialize a Bom to JSON / XML.

$bom = new \CycloneDX\Core\Models\Bom();
$bom->getMetadata()->setComponent(
    $rootComponent = new \CycloneDX\Core\Models\Component(
        \CycloneDX\Core\Enums\ComponentType::APPLICATION,
        'myApp'
    )
);
$component = new \CycloneDX\Core\Models\Component(
    \CycloneDX\Core\Enums\ComponentType::LIBRARY,
    'myComponent'
);
$bom->getComponents()->addItems($component);
$rootComponent->getDependencies()->addItems($component->bomRef);

$spec = \CycloneDX\Core\Spec\SpecFactory::make1dot4();

$prettyPrint = false;

$jsonSerializer = new \CycloneDX\Core\Serialization\JsonSerializer(
    new \CycloneDX\Core\Serialization\JSON\NormalizerFactory($spec)
);
$serializedJSON = $jsonSerializer->serialize($bom, $prettyPrint);
echo $serializedJSON, \PHP_EOL;

$xmlSerializer = new \CycloneDX\Core\Serialization\XmlSerializer(
    new \CycloneDX\Core\Serialization\DOM\NormalizerFactory($spec)
);
$serializedXML = $xmlSerializer->serialize($bom, $prettyPrint);
echo $serializedXML, \PHP_EOL;
