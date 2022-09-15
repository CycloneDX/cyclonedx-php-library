# Changelog

All notable changes to this project will be documented in this file.

## unreleased

## 2.0.0 - unreleased

* BREAKING changes
  * Interface `\CycloneDX\Core\Spec\SpecInterface` became internal, was public api. (via [#65])  
    This is done to prevent the need for future "breaking changed" when the schema requires additional spec implementations.
* Changed
  * Method `\CycloneDX\Core\Serialize\{DOM,JSON}\Normalizers\ExternalReferenceNormalizer::normalize` throw `DomainException` when `ExternalReference`'s type was not supported by the spec.  (via [#65])  
    This is considered a non-breaking change, because the behaviour was already documented in the API, even though there was no need for an implementation before.
  * Class `\CycloneDX\Core\Models\Component`'s property `version` is optional now, to reflect CycloneDX v1.4. (via [#118])  
    This affects constructor arguments, and affects methods `{get,set}Version()`.
* Added
  * New class constant `\CycloneDX\Core\Spec\Version::V_1_4` for CycloneDX v1.4. (via [#65])
  * New class `\CycloneDX\Core\Spec\Spec14` to reflect CycloneDX v1.4. (via [#65])
  * Support for CycloneDX v1.4 in `\CycloneDX\Core\Validation\Validators\{Json,Xml}StrictValidator`. (via [#65])
  * New methods in class `\CycloneDX\Core\Spec\Spec1{1,2,3}` (via [#65])
    * `::getSupportsExternalReferenceTypes()`
    * `::isSupportsExternalReferenceType()`
  * New class constant `CycloneDX\Core\Enums\ExternalReferenceType::RELEASE_NOTES` to reflect CycloneDX v1.4. (via [#65])

[#65]: https://github.com/CycloneDX/cyclonedx-php-library/pull/65
[#118]: https://github.com/CycloneDX/cyclonedx-php-library/pull/118

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
