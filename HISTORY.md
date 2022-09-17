# Changelog

All notable changes to this project will be documented in this file.

## unreleased

## 2.0.0 - unreleased

* BREAKING
  * Removed support for PHP v7.3. ([#6]   via [#125])
  * Removed support for PHP v7.4. ([#114] via [#125])
  * Changed models' aggregation properties to be no longer optional. ([#66] via [#131])
  * Streamlined repository data structures to follow a common method naming scheme. (via [#131])
* Added
  * Support for CycloneDX v1.4. ([#57] via [#65], [#118], [#123])
* Misc
  * All class properties now enforce the correct types. ([#6], [#114] via [#125])  
    This is considered a non-breaking change, because the types were already correctly annotated.  
    This was possible due to PHP74's features and php8's UnionType language feature.
  * Migrated internals to PHP8 language features. ([#114] via [#125])
  
API changes

- Overall
  * BREAKING: Enforced the use of concrete union types instead of protocols. ([#114] via [#125])
    Affected the usages of no longer public `\CycloneDX\Core\Models\License\AbstractDisjunctiveLicense` and methods that used license-related classes.
    This was possible due to PHP8's UnionType language feature.
  * Changed some methods to no longer throw `\InvalidArgumentException`. (via [#125])  
    PhpDoc annotations were updated, so that code analysis tools should pick up.  
    This was possible by enforcing correct typing on PHP8 language level.
- `\CycloneDX\Core\Enum`
  * Added class constant `ExternalReferenceType::RELEASE_NOTES` to reflect CycloneDX v1.4. ([#57] via [#65])
- `\CycloneDX\Core\Factories`
  * No noteworthy changes.
- `\CycloneDX\Core\Models`
  * `Bom`
    * BREAKING: renamed methods `{get,set}ComponentRepository()` -> `{get,set}Components()`. ([#66] via [#131])
    * BREAKING: renamed methods `{get,set}ExternalReferenceRepository()` -> `{get,set}ExternalReferences()`
      and made their parameter & return type non-nullable, was nullable. ([#66] via [#131])
  * `Component`
    * BREAKING: renamed methods `{get,set}DependenciesBomRefRepository()` -> `{get,set}Dependencies()`
      and made their parameter & return type non-nullable, was nullable. ([#66] via [#131])
    * BREAKING: renamed methods `{get,set}ExternalReferenceRepository()` -> `{get,set}ExternalReferences()`
      and made their parameter & return type non-nullable, was nullable. ([#66] via [#131])
    * BREAKING: renamed methods `{get,set}HashRepository()` -> `{get,set}Hashes()`
      and made their parameter & return type non-nullable, was nullable. ([#66] via [#131])
    * BREAKING: renamed methods `{get,set}MetaData()` -> `{get,set}Metadata()`
      and made their parameter & return type non-nullable, was nullable. ([#66] via [#131])
    * BREAKING: renamed methods `{get,set}License()` -> `{get,set}Licenses()`
      and made it work with class `LicenseRepository` only, was working with various `Models\License\*` types. ([#66] via [#131])
    * BREAKING: Changed class property `version` to optional now, to reflect CycloneDX v1.4. ([#27] via [#118], [#131])  
      This affects constructor arguments, and affects methods `{get,set}Version()`.
  * `ExternalReference`
    * BREAKING: renamed methods `{get,set}HashRepository()` -> `{get,set}Hashes()`
    and made their parameter & return type non-nullable, was nullable. ([#66] via [#131])
  * `Licenses\*`
    * BREAKING: Removed the public usage of the internal class `License\AbstractDisjunctiveLicense`. (via [#125])  
      This was made possible due PHP8's UnionType language feature.  
      The class was not removed, but marked `@internal`.
  * `MetaData`
    * BREAKING: renamed class to `Metadata`.  (via [#131])  
      Even though PHP is case-insensitive with class names, autoloaders are not. Therefore, this is considered a breaking change.
    * BREAKING: changed methods `{get,set}Tools()` so that their parameter & return type non-nullable, was nullable. ([#66] via [#131])
  * `Tool`
    * BREAKING: renamed methods `{get,set}ExternalReferenceRepository()` -> `{get,set}ExternalReferences()`
      and made their parameter & return type non-nullable, was nullable. ([#66] via [#131])
    * BREAKING: renamed methods `{get,set}HashRepository()` -> `{get,set}Hashes()`
      and made their parameter & return type non-nullable, was nullable. ([#66] via [#131])
- `\CycloneDX\Core\Repositories`
  * Overall:
    * BREAKING: Renamed the namespace to `Collections`. (via [#131])
    * BREAKING: Streamlined all classes, renamed all getters to `getItems` and all setters to `setItems`.  (via [#131])
      In addition, the method arguments were renamed to generic `$items`.
  * `DisjunctiveLicenseRepository`
    * BREAKING: renamed the class to `LicenseRepository`. (via [#131])
    * BREAKING: Added the capability to also aggregate instances of class `Models\LicenseExpression`. (via [#131])
      Therefore, various getters and setters and the constructor changed their signatures,
      was usage of `Models\License\AbstractDisjunctiveLicense` only.
  * `HashRepository`
    * BREAKING: renamed to `HashDictionary`. (via [#131])
    * BREAKING: renamed all methods and changed all method signatures to match the overall streamlined scheme. (via [#131])
- `\CycloneDX\Core\Serialize`
  * Changed the method `{DOM,JSON}\Normalizers\ExternalReferenceNormalizer::normalize()`
    to actually throw `\DomainException` when `\ExternalReference`'s type was not supported by the spec. (via [#65])  
    This is considered a non-breaking change, because the behaviour was already documented in the API, even though there was no need for an implementation before.
- `\CycloneDX\Core\Spdx`
  * No noteworthy changes.
- `\CycloneDX\Core\Spec`
  * BREAKING: Removed the public usage of the interface `SpecInterface`. (via [#65])  
    This is done to prevent the need for future "breaking changed" when the schema requires additional spec implementations.  
    The class was not removed, but marked `@internal`.
  * Added class constant `Version::V_1_4` for CycloneDX v1.4. ([#57] via [#65])
  * Added new class `Spec14` to reflect CycloneDX v1.4. ([#57] via [#65])
  * Added new methods in class `Spec1{1,2,3}`:
    * `::getSupportedExternalReferenceTypes()` (via [#65], [#124])
    * `::isSupportedExternalReferenceType()` (via [#65], [#124])
    * `::supportsToolExternalReferences()` (via [#123])
- `\CycloneDX\Core\Validation`
  * Added support for CycloneDX v1.4 in `{Json,Xml}StrictValidator`. ([#57] via [#65])
  
[#6]: https://github.com/CycloneDX/cyclonedx-php-library/issues/6
[#27]: https://github.com/CycloneDX/cyclonedx-php-library/issues/27
[#57]: https://github.com/CycloneDX/cyclonedx-php-library/issues/57
[#65]: https://github.com/CycloneDX/cyclonedx-php-library/pull/65
[#66]: https://github.com/CycloneDX/cyclonedx-php-library/issues/66
[#114]: https://github.com/CycloneDX/cyclonedx-php-library/issues/114
[#118]: https://github.com/CycloneDX/cyclonedx-php-library/pull/118
[#123]: https://github.com/CycloneDX/cyclonedx-php-library/pull/123
[#124]: https://github.com/CycloneDX/cyclonedx-php-library/pull/124
[#125]: https://github.com/CycloneDX/cyclonedx-php-library/pull/125
[#131]: https://github.com/CycloneDX/cyclonedx-php-library/pull/131

## 1.6.3 - 2022-09-15

Maintenance Release.

* Legal:
  * Transferred copyright to OWASP Foundation. (via [#121])

[#121]: https://github.com/CycloneDX/cyclonedx-php-library/pull/121

## 1.6.2 - 2022-09-12

Maintenance release.

* Docs:
  * Added "Responsibilities", "Capabilities" and "Usage" sections to README. (via [#115])

[#115]: https://github.com/CycloneDX/cyclonedx-php-library/pull/115

## 1.6.1 - 2022-08-16

* Maintenance release.

## 1.6.0 - 2022-08-03

* Changed
  * Use [version 9b04a94 of CycloneDX specification][CDX-specification#9b04a94474dfcabafe7d3a9f8db6c7e5eb868adb]
    for XML and JSON schema validation. (via [#105])
  * Use SPDX license enumeration from
    [version 9b04a94 of CycloneDX specification][CDX-specification#9b04a94474dfcabafe7d3a9f8db6c7e5eb868adb].
    (via [#105])
* Style
  * Fixe some whitespaces. (via [#82])

[#82]: https://github.com/CycloneDX/cyclonedx-php-library/pull/82
[#105]: https://github.com/CycloneDX/cyclonedx-javascript-library/pull/105
[CDX-specification#9b04a94474dfcabafe7d3a9f8db6c7e5eb868adb]: https://github.com/CycloneDX/specification/tree/9b04a94474dfcabafe7d3a9f8db6c7e5eb868adb

## 1.5.0 - 2022-03-08

* Changed
  * Use [version 82bf9e3 of CycloneDX specification][CDX-specification#82bf9e30ba3fd6413e72a0e66adce2cdf3354f32]
    for XML and JSON schema validation. (via [#79])
  * Use SPDX license enumeration from
    [version 82bf9e3 of CycloneDX specification][CDX-specification#82bf9e30ba3fd6413e72a0e66adce2cdf3354f32].
    (via [#79])

[#79]: https://github.com/CycloneDX/cyclonedx-php-library/pull/79
[CDX-specification#82bf9e30ba3fd6413e72a0e66adce2cdf3354f32]: https://github.com/CycloneDX/specification/tree/82bf9e30ba3fd6413e72a0e66adce2cdf3354f32

## 1.4.2 - 2022-02-05

* Fixed
  * Return type of `CycloneDX\Core\Serialize\SerializerInterface::serialize()` and implementations/usage
    are documented as `non-empty-string`, were undocumented `string` before. (via [#70])

[#70]: https://github.com/CycloneDX/cyclonedx-php-library/pull/70

## 1.4.1 - 2022-01-31

* Fixed
  * `CycloneDX\Core\Validation\ValidatorInterface::validateString()` and implementations
    are documented as `non-empty-string`, were undocumented `string` before. (via [#63])

[#63]: https://github.com/CycloneDX/cyclonedx-php-library/pull/63

## 1.4.0 - 2021-12-20

* Added
  * Resulting JSON files hold the correct `$schema`. ([#43] via [#42])

[#43]: https://github.com/CycloneDX/cyclonedx-php-library/issues/43
[#42]: https://github.com/CycloneDX/cyclonedx-php-library/pull/42

## 1.3.1 - 2021-12-03

* Fixed
  * XML serializer & DOM normalizer no longer generate invalid `XML::anyURI`. (via [#34])

[#34]: https://github.com/CycloneDX/cyclonedx-php-library/pull/34

## 1.3.0 - 2021-12-01

* Changed
  * JSON result does no longer have slashes escaped in strings. (via [#33])  
    Old: `"http:\/\/exampe.com"`  
    New: `"http://exampe.com"`

[#33]: https://github.com/CycloneDX/cyclonedx-php-library/pull/27

## 1.2.0 - 2021-11-29

* Added
  * Prevention of information-loss on metadata-component's ExternalReferences,
    when normalizing to a specification that does not support `bom.metadata`
    (via [#26])

[#26]: https://github.com/CycloneDX/cyclonedx-php-library/pull/26

## 1.1.0 - 2021-11-25

* Added
  * Support for ExternalReferences in BOM and Component (via [#17])

[#17]: https://github.com/CycloneDX/cyclonedx-php-library/pull/17

## 1.0.3 - 2021-11-15

* Fixed
  * `CycloneDX\Core\Models\License\AbstractDisjunctiveLicense::setUrl()` no longer restricts the argument to be a valid URL.  
    Per schema definition `licenseType.url` should be a URI, not a URL.
    See [#18](https://github.com/CycloneDX/cyclonedx-php-library/issues/18)
* Changed
  * `CycloneDX\Core\Models\License\AbstractDisjunctiveLicense::setUrl()` no longer throws `InvalidArgumentException`
     if the argument is not a URL (via [#19])

[#19]: https://github.com/CycloneDX/cyclonedx-php-library/pull/19

## 1.0.2 - 2021-10-30

* Fixed
  * Psalm-annotation of `CycloneDX\Core\Enums\Classification::isValidValue()` (via [#10])

[#10]: https://github.com/CycloneDX/cyclonedx-php-library/pull/10

## 1.0.1 - 2021-10-23

Removed composer's `conflict` constraint.  
This was done to enable some workflows with package forks/mirrors that don't have proper version detection.
See [#9](https://github.com/CycloneDX/cyclonedx-php-library/pull/9)

## 1.0.0 - 2021-10-07

Initial release.  
Split the library from
[`/src/Core` of cyclonedx-php-composer (346e6200fb2f5086061b15c2ee44f540893ce97d)](https://github.com/CycloneDX/cyclonedx-php-composer/tree/346e6200fb2f5086061b15c2ee44f540893ce97d/src/Core)
