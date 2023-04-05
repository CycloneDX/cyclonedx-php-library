# Changelog

All notable changes to this project will be documented in this file.

## unreleased

## 2.1.2 - 2023-04-05

* Fixed
  * `\CycloneDX\Core\Serialization\{DOM,JSON}\Normalizers\LicenseRepositoryNormalizer::normalize()` now omits invalid license combinations ([#285] via [#290])  
    If there is any `LicenseExpression`, then this is the only license normalized; otherwise all licenses are normalized.
* Docs
  * Fixed link to CycloneDX-specification in README (via [#288])

[#285]: https://github.com/CycloneDX/cyclonedx-php-library/issues/285
[#288]: https://github.com/CycloneDX/cyclonedx-php-library/pull/288
[#290]: https://github.com/CycloneDX/cyclonedx-php-library/pull/290

## 2.1.1 - 2023-03-28

* Docs
  * Announce and annotate the generator for BOM's SerialNumber ([#277] via [#282])
  
[#282]: https://github.com/CycloneDX/cyclonedx-php-library/pull/282

## 2.1.0 - 2023-03-24

* Fixed
  * "Bom.serialNumber" data model can have values following the alternative format allowed in CycloneDX XML specification ([#277] via [#278])
  * `\CycloneDX\Core\Serialization\{DOM,JSON}\Normalizers\BomNormalizer::normalize()` now omits invalid/unsupported values for `serialNumber` ([#277] via [#278])
* Changed
  * `\CycloneDX\Core\Models\Bom::setSerialNumber()` no longer throws `\DomainException` when the value is of an unsupported format ([#277] via [#278])  
    This is considered a non-breaking behaviour change, because the corresponding normalizers assure valid data results.
* Added
  * Published generator for BOM's SerialNumber: `\CycloneDX\Core\Utils\BomUtility::randomSerialNumber()` ([#277] via [#278])  
    The code was donated from [cyclonedx-php-composer](https://github.com/CycloneDX/cyclonedx-php-composer).

[#277]: https://github.com/CycloneDX/cyclonedx-php-library/issues/277
[#278]: https://github.com/CycloneDX/cyclonedx-php-library/pull/278

## 2.0.0 - 2023-03-20

* BREAKING
  * Removed support for PHP v7.3 ([#6] via [#125])
  * Removed support for PHP v7.4 ([#114] via [#125])
  * Removed support for PHP v8.0 (via [#204])
  * Changed models' aggregation properties to be no longer optional ([#66] via [#131])
  * Changed models to be less restrictive ([#247] via [#249])
  * Streamlined repository data structures to follow a common method naming scheme (via [#131])
  * Enumeration-like classes were converted to native [PHP Enumerations](https://www.php.net/manual/en/language.types.enumerations.php) ([#140], [#256] via [#204], [#257])
* Added
  * Support for CycloneDX schema/spec v1.4 ([#57] via [#65], [#118], [#123])
  * Support for [properties](https://cyclonedx.org/use-cases/#properties--name-value-store) ([#228] via [#165], [#229], [#231])
* Misc
  * All class properties now enforce the correct types ([#6], [#114] via [#125])  
    This is considered a non-breaking change, because the types were already correctly annotated.  
  * Migrated internals to PHP8 language features ([#114] via [#125])

### API changes v2 - the details

* Overall
  * BREAKING: enforced the use of concrete UnionTypes instead of protocols/interfaces/abstracts ([#114] via [#125])  
    Affected the usages of no longer public `\CycloneDX\Core\Models\License\AbstractDisjunctiveLicense` and methods that used license-related classes.
    This was possible due to PHP8's UnionType language feature.
  * Changed some methods to no longer throw `\InvalidArgumentException` (via [#125])  
    PhpDoc annotations were updated, so that code analysis tools should pick up.
    This was possible by enforcing correct typing on PHP8 language level.
  * BREAKING: every occurrence of `{M,m}etaData` with a capital "D" was renamed to `{M,m}etadata` with a small "d" ([#133] via [#131], [#149])  
    This affects class names, method names, variable names, property names, file names, documentation - everything.
* `\CycloneDX\Core\Collections` namespace
  * Added new class `CopyrightRepository` ([#238] via [#241])
  * Added new class `PropertyRepository` ([#228] via [#165])
* `\CycloneDX\Core\Enum` namespace
  * `Classification` class
    * BREAKING: renamed class to `ComponentType` (via [#170])
    * BREAKING: became a native PHP Enumeration type ([#140] via [#204])
    * BREAKING: all `const` converted to `case` with UpperCamelCase naming scheme ([#256] via [#257])
    * BREAKING: method `isValidValue()` was removed (via [#204])
  * `ExternalReferenceType` class
    * BREAKING: became a native PHP Enumeration type ([#140] via [#204])
    * BREAKING: all `const` converted to `case` with UpperCamelCase naming scheme ([#256] via [#257])
    * BREAKING: method `isValidValue()` was removed (via [#204])
    * Added case `RELEASE_NOTES` to reflect CycloneDX v1.4 ([#57] via [#65])
  * `HashAlgorithm` class
    * BREAKING: became a native PHP Enumeration type ([#140] via [#204])
    * BREAKING: all `const` converted to `case` with UpperCamelCase naming scheme ([#256] via [#257])
    * BREAKING: method `isValidValue()` was removed (via [#204])
* `CycloneDX\Core\Factories` namespace
  * `LicenseFactory` class
    * BREAKING: check whether something is a valid SPDX Expression is now complete, was best effort implementation ([#247] via [#249])  
      This affects all methods that potentially would create `LicenseExpression` models.  
      Utilizes [`composer/spdx-licenses`](https://packagist.org/packages/composer/spdx-licenses).
    * BREAKING: changed constructor method `__construct()` (via [#249])
    * BREAKING: removed method `makeDisjunctiveFromExpression()` ([#163] vial [#166])
    * BREAKING: removed method `setSpdxLicenseValidator()` (via [#249])
    * BREAKING: renamed method `getSpdxLicenseValidator()` -> `getLicenseIdentifiers()` (via [#249])
    * BREAKING: renamed method `makeDisjunctiveWithId()` -> `makeSpdxLicense()` ([#164] vial [#168])
    * BREAKING: renamed method `makeDisjunctiveWithName()` -> `makeNamedLicense()` ([#164] vial [#168])
    * Added new method `getSpdxLicenses()` (via [#249])
* `\CycloneDX\Core\Models` namespace
  * `Bom` class
    * BREAKING: changed constructor to no longer accept components ([#187] via [#188])
    * BREAKING: renamed methods `{get,set}ComponentRepository()` -> `{get,set}Components()` ([#133] via [#131])
    * BREAKING: renamed methods `{get,set}ExternalReferenceRepository()` -> `{get,set}ExternalReferences()` ([#133] via [#131])  
      Also changed parameter & return type to non-nullable, was nullable ([#66] via [#131])
    * BREAKING: renamed methods `{get,set}MetaData()` -> `{get,set}Metadata()` ([#133] via [#131])  
      Also changed parameter & return type to non-nullable, was nullable ([#66] via [#131])
    * Added new methods `{get,set}Properties()` ([#228] via [#229])
    * Added new methods `{get,set}SerialNumber()` (via [#186])
  * `Component` class
    * BREAKING: renamed methods `{get,set}DependenciesBomRefRepository()` -> `{get,set}Dependencies()` ([#133] via [#131])  
      Also changed parameter & return type to non-nullable, was nullable ([#66] via [#131])
    * BREAKING: renamed methods `{get,set}ExternalReferenceRepository()` -> `{get,set}ExternalReferences()` ([#133] via [#131])  
      Also changed parameter & return type to non-nullable, was nullable ([#66] via [#131])
    * BREAKING: renamed methods `{get,set}HashRepository()` -> `{get,set}Hashes()` ([#133] via [#131])  
      Also changed parameter & return type to non-nullable, was nullable ([#66] via [#131])
    * BREAKING: renamed methods `{get,set}License()` -> `{get,set}Licenses()` (via [#131])  
      Also changed it work with class `LicenseRepository` only, was working with various `Models\License\*` types ([#66] via [#131])
    * BREAKING: changed class property `version` to be optional, to reflect CycloneDX v1.4 ([#27] via [#118], [#131])  
      This affects constructor arguments, and affects methods `{get,set}Version()`.
    * BREAKING: changed property `type` to be of type `\CycloneDX\Core\Enum\ComponentType` ([#140] via [#204])  
      This affects constructor arguments, and affects methods `{get,set}Type()`.
    * Added new methods `{get,set}Author()` ([#184] via [#185])
    * Added new methods `{get,set}Copyright()` ([#238] via [#239])
    * Added new methods `{get,set}Evidence()` ([#238] via [#241])
    * Added new methods `{get,set}Properties()` ([#228] via [#165])
  * Added new class `ComponentEvidence` ([#238] via [#241])
  * `ExternalReference` class
    * BREAKING: renamed methods `{get,set}HashRepository()` -> `{get,set}Hashes()` ([#133] via [#131])  
      Also changed parameter & return type to non-nullable, was nullable ([#66] via [#131])
    * BREAKING: changed property `type` to be of type `\CycloneDX\Core\Enum\ExternalReferenceType` ([#140] via [#204])  
      This affects constructor arguments, and affects methods `{get,set}Type()`.
  * `Licenses` namespace
    * `AbstractDisjunctiveLicense`
      * BREAKING: removed this class (via [#125], [#131])
    * `DisjunctiveLicenseWithName` class
      * BREAKING: renamed class to `NamedLicense` ([#164] via [#168])
    * `DisjunctiveLicenseWithId` class
      * BREAKING: renamed class to `SpdxLicense` ([#164] via [#168])
      * BREAKING: removed factory method `makeValidated()` ([#247] via [#249])
        To assert valid values use `\CycloneDX\Core\Factories\LicenseFactory::makeSpdxLicense()`.
      * Changed: constructor `__construct()` is public now, was private ([#247] via [#249])
      * Added new method `setId()` ([#247] via [#249])
    * `LicenseExpression` class
      * BREAKING: constructor `__construct()` and method `setExpression()` no longer do validation, but only assert that the parameter is no empty string ([#247] ia [#249])  
        To assert valid values use `\CycloneDX\Core\Factories\LicenseFactory::makeExpression()`.
      * BREAKING: removed method `isValid()` ([#247] via [#249])
  * `MetaData` class
    * BREAKING: renamed class to `Metadata` ([#133] via [#131])  
      Even though PHP is case-insensitive with class names, autoloaders may be case-sensitive. Therefore, this is considered a breaking change.
    * BREAKING: changed methods `{get,set}Tools()` so that their parameter & return type is non-nullable, was nullable ([#66] via [#131])
    * Added new methods `{get,set}Properties()` ([#228] via [#165])
    * Added new methods `{get,set}Timestamp()` (via [#180], [#181])
  * Added new class `Property` ([#228] via [#165])
  * `Tool` class
    * BREAKING: renamed methods `{get,set}ExternalReferenceRepository()` -> `{get,set}ExternalReferences()` ([#133] via [#131])  
      Also changed parameter & return type to non-nullable, was nullable ([#66] via [#131])
    * BREAKING: renamed methods `{get,set}HashRepository()` -> `{get,set}Hashes()` ([#133] via [#131])  
      Also changed parameter & return type to non-nullable, was nullable ([#66] via [#131])
* `\CycloneDX\Core\Repositories` namespace
  * Overall:
    * BREAKING: renamed the namespace to `\CycloneDX\Core\Collections` ([#133] via [#131])
    * BREAKING: streamlined all classes, renamed all getters to `getItems()` and all setters to `setItems()` ([#133] via [#131])  
      In addition, the method arguments were renamed to generic `$items`.
  * `DisjunctiveLicenseRepository` class
    * BREAKING: renamed the class to `\CycloneDX\Core\Collections\LicenseRepository` (via [#131])
    * BREAKING: added the capability to also aggregate instances of class `Models\LicenseExpression` (via [#131])  
      Therefore, various getters and setters and the constructor changed their signatures,
      was usage of `\CycloneDX\Core\Models\License\AbstractDisjunctiveLicense` only.
  * `HashRepository` class
    * BREAKING: renamed to `\CycloneDX\Core\Collections\HashDictionary` ([#133] via [#131])
    * BREAKING: renamed all methods and changed all method signatures to match the overall streamlined scheme ([#133] via [#131])
    * BREAKING: changed all method signatures to enable handling of native PHP Enumeration type `\CycloneDX\Core\Enum\HashAlgorithm` ([#140] via [#204])
* `\CycloneDX\Core\Serialize` namespace
  * Overall
    * BREAKING: renamed namespace to `Serialization` ([#5] via [#146])
  * `SerializerInterface` interface
    * BREAKING: renamed to `Serializer` ([#133] via [#155])
    * BREAKING: method `serialize()` got a new optional parameter `$prettyPrint` (via [#155])
    * BREAKING: method `serialize()` may throw `\Throwable`, was `\Exception` (via [#253])
  * `BaseSerializer` abstract class
    * BREAKING: complete redesign (via [#155])
  * `{Json,Xml}Serializer` class
    * BREAKING: complete redesign (via [#155])
  * `{DOM,JSON}\NormalizerFactory` classes
    * BREAKING: removed method `makeForLicenseExpression()` (via [#131])
    * BREAKING: removed method `makeForDisjunctiveLicense()` (via [#131])
    * BREAKING: removed method `makeForDisjunctiveLicenseRepository()` (via [#131])
    * BREAKING: removed method `makeForHashRepositonary()` - use `makeForHashDictionary()` instead ([#133] via [#131])
    * BREAKING: removed method `setSpec()` (via [#131])
    * Added new method `makeForComponentEvidence()` ([#238] via [#241])
    * Added new method `makeForHashDictionary()` ([#133] via [#131])
    * Added new method `makeForLicense()` (via [#131])
    * Added new method `makeForLicenseRepository()` (via [#131])
  * `{DOM,JSON}\Normalizers` namespaces
    * BREAKING: removed classes `DisjunctiveLicenseNormalizer` - use `LicenseNormalizer` instead (via [#131])
    * BREAKING: removed classes `LicenseExpressionNormalizer`  - use `LicenseNormalizer` instead (via [#131])
    * BREAKING: removed classes `DisjunctiveLicenseRepositoryNormalizer` (via [#131])
    * BREAKING: renamed classes `HashRepositoryNormalizer` -> `HashDictionaryNormalizer` ([#133] via [#131])  
      Also changed signatures to accept `Models\HashDictionary` instead of `Models\HashRepository`
    * BREAKING: changed signatures of class `HashNormalizer` to accept native PHP Enumeration type `\CycloneDX\Core\Enum\HashAlgorithm` ([#140] via [#204])
    * Added new classes `ComponentEvidenceNormalizer` that can normalize `ComponentEvidence` ([#238] via [#241])
    * Added new classes `LicenseNormalizer` that can normalize every existing license model (via [#131])
    * Added new classes `LicenseRepositoryNormalizer` that can normalize `LicenseRepository` (via [#131])
    * `ExternalReferenceNormalizer` classes
      * Changed the method `normalize()` to actually throw `\DomainException` when `\ExternalReference`'s type was not supported by the spec (via [#65])  
        This is considered a non-breaking change, because the behaviour was already documented in the API, even though there was no need for an implementation before.
    * `ExternalReferenceNormalizer` classes
      * Changed, so that it tries to convert unsupported types to "other", before it throws a `\DomainException` ([#137] via [#147])
  * `JSON\Normalizers\BomNormalizer` class
    * Changed: method `normalize()`'s result data may contain the `$schema` string (via [#155])
  * `JSON\Normalizers\ExternalReferenceNormalizer` class
    * BREAKING: method `normalize()` may throw `\UnexpectedValueException` when the url is invalid to format "ini-reference" (via [#151])
* `\CycloneDX\Core\Spdx` namespace
  * BREAKING: renamed the class `License` -> `LicenseIdentifiers` ([#133] via [#143], [#249])
  * BREAKING: renamed method `getLicense()` -> `fixLicense()` (via [#249])
  * BREAKING: renamed method `getLicenses()` -> `getKnownLicenses()`, and removed keys from return value (via [#249])
  * BREAKING: renamed method `validate()` -> `isKnownLicense()` (via [#249])
* `\CycloneDX\Core\Spec` namespace
  * BREAKING: completely reworked everything ([#139] via [#142], [#174], [#204])  
    See the code base for references
* `\CycloneDX\Core\Validation` namespace
  * `BaseValidator` class
    * BREAKING: removed deprecated method `setSpec()` (via [#144])
  * `ValidatorInterface` interface
    * BREAKING: renamed interface to `Validator` ([#133] via [#143])
    * Removed specification of constructor `__construct()` (via [#253])
    * Removed specification of method `getSpec()` (via [#253])
  * `Validators\{Json,JsonStrict,Xml}Validator` classes
    * Added support for CycloneDX v1.4 ([#57] via [#65])
  * `Validators\{Json,JsonStrict}Validator` classes
    * Utilizes a much more competent validation library than before ([#80] via [#151])

[#5]:   https://github.com/CycloneDX/cyclonedx-php-library/issues/5
[#6]:   https://github.com/CycloneDX/cyclonedx-php-library/issues/6
[#27]:  https://github.com/CycloneDX/cyclonedx-php-library/issues/27
[#57]:  https://github.com/CycloneDX/cyclonedx-php-library/issues/57
[#65]:  https://github.com/CycloneDX/cyclonedx-php-library/pull/65
[#66]:  https://github.com/CycloneDX/cyclonedx-php-library/issues/66
[#80]:  https://github.com/CycloneDX/cyclonedx-php-library/issues/80
[#114]: https://github.com/CycloneDX/cyclonedx-php-library/issues/114
[#118]: https://github.com/CycloneDX/cyclonedx-php-library/pull/118
[#123]: https://github.com/CycloneDX/cyclonedx-php-library/pull/123
[#125]: https://github.com/CycloneDX/cyclonedx-php-library/pull/125
[#131]: https://github.com/CycloneDX/cyclonedx-php-library/pull/131
[#133]: https://github.com/CycloneDX/cyclonedx-php-library/pull/133
[#137]: https://github.com/CycloneDX/cyclonedx-php-library/issues/137
[#139]: https://github.com/CycloneDX/cyclonedx-php-library/issues/139
[#140]: https://github.com/CycloneDX/cyclonedx-php-library/issues/140
[#142]: https://github.com/CycloneDX/cyclonedx-php-library/pull/142
[#143]: https://github.com/CycloneDX/cyclonedx-php-library/pull/143
[#144]: https://github.com/CycloneDX/cyclonedx-php-library/pull/144
[#146]: https://github.com/CycloneDX/cyclonedx-php-library/pull/146
[#147]: https://github.com/CycloneDX/cyclonedx-php-library/pull/147
[#149]: https://github.com/CycloneDX/cyclonedx-php-library/pull/149
[#151]: https://github.com/CycloneDX/cyclonedx-php-library/pull/151
[#155]: https://github.com/CycloneDX/cyclonedx-php-library/pull/155
[#163]: https://github.com/CycloneDX/cyclonedx-php-library/issues/163
[#164]: https://github.com/CycloneDX/cyclonedx-php-library/issues/164
[#165]: https://github.com/CycloneDX/cyclonedx-php-library/pull/165
[#166]: https://github.com/CycloneDX/cyclonedx-php-library/pull/166
[#168]: https://github.com/CycloneDX/cyclonedx-php-library/pull/168
[#170]: https://github.com/CycloneDX/cyclonedx-php-library/pull/170
[#174]: https://github.com/CycloneDX/cyclonedx-php-library/pull/174
[#180]: https://github.com/CycloneDX/cyclonedx-php-library/pull/180
[#181]: https://github.com/CycloneDX/cyclonedx-php-library/pull/181
[#185]: https://github.com/CycloneDX/cyclonedx-php-library/pull/185
[#186]: https://github.com/CycloneDX/cyclonedx-php-library/pull/186
[#187]: https://github.com/CycloneDX/cyclonedx-php-library/issues/187
[#188]: https://github.com/CycloneDX/cyclonedx-php-library/pull/188
[#204]: https://github.com/CycloneDX/cyclonedx-php-library/pull/204
[#228]: https://github.com/CycloneDX/cyclonedx-php-library/issues/228
[#229]: https://github.com/CycloneDX/cyclonedx-php-library/pull/229
[#231]: https://github.com/CycloneDX/cyclonedx-php-library/pull/231
[#238]: https://github.com/CycloneDX/cyclonedx-php-library/issues/238
[#239]: https://github.com/CycloneDX/cyclonedx-php-library/pull/239
[#241]: https://github.com/CycloneDX/cyclonedx-php-library/pull/241
[#247]: https://github.com/CycloneDX/cyclonedx-php-library/issues/247
[#249]: https://github.com/CycloneDX/cyclonedx-php-library/pull/249
[#253]: https://github.com/CycloneDX/cyclonedx-php-library/pull/253
[#256]: https://github.com/CycloneDX/cyclonedx-php-library/issues/256
[#257]: https://github.com/CycloneDX/cyclonedx-php-library/pull/257

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
