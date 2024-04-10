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

namespace CycloneDX\Examples;

require_once __DIR__.'/../vendor/autoload.php';

// Example how to serialize a Bom to JSON / XML.

$lFac = new \CycloneDX\Core\Factories\LicenseFactory();

// region build the BOM

$bom = new \CycloneDX\Core\Models\Bom();
$bom->getMetadata()->setComponent(
    $rootComponent = new \CycloneDX\Core\Models\Component(
        \CycloneDX\Core\Enums\ComponentType::Application,
        'myApp'
    )
);
$rootComponent->getBomRef()->setValue('myApp');
$rootComponent->getLicenses()->addItems($lFac->makeFromString('MIT OR Apache-2.0'));

$component = new \CycloneDX\Core\Models\Component(
    \CycloneDX\Core\Enums\ComponentType::Library,
    'myComponent'
);
$component->getLicenses()->addItems($lFac->makeFromString('MIT'));
$bom->getComponents()->addItems($component);

$rootComponent->getDependencies()->addItems($component->getBomRef());

// endregion build the BOM

$spec = \CycloneDX\Core\Spec\SpecFactory::make1dot6();

$prettyPrint = false;

$serializedJSON = (new \CycloneDX\Core\Serialization\JsonSerializer(
    new \CycloneDX\Core\Serialization\JSON\NormalizerFactory($spec)
))->serialize($bom, $prettyPrint);
echo $serializedJSON, \PHP_EOL;
$jsonValidationErrors = (new \CycloneDX\Core\Validation\Validators\JsonValidator($spec))->validateString($serializedJSON);
if (null === $jsonValidationErrors) {
    echo 'JSON valid', \PHP_EOL;
} else {
    fwrite(\STDERR, \PHP_EOL.'JSON ValidationError:'.\PHP_EOL);
    fwrite(\STDERR, print_r($jsonValidationErrors, true));
    exit(1);
}

$serializedXML = (new \CycloneDX\Core\Serialization\XmlSerializer(
    new \CycloneDX\Core\Serialization\DOM\NormalizerFactory($spec)
))->serialize($bom, $prettyPrint);
echo $serializedXML, \PHP_EOL;
$xmlValidationErrors = (new \CycloneDX\Core\Validation\Validators\XmlValidator($spec))->validateString($serializedXML);
if (null === $xmlValidationErrors) {
    echo 'XML valid', \PHP_EOL;
} else {
    fwrite(\STDERR, \PHP_EOL.'XML ValidationError:'.\PHP_EOL);
    fwrite(\STDERR, print_r($xmlValidationErrors, true));
    exit(2);
}
