# Changelog

All notable changes to this project will be documented in this file.

## unreleased

* Fixed
  * `CycloneDX\Core\Models\License\AbstractDisjunctiveLicense::setUrl()` no longer restricts the argument to be a valid URL.  
     Per schema definition `licenseType.url` should be a URI, not a URL.
* Changed
  * `CycloneDX\Core\Models\License\AbstractDisjunctiveLicense::setUrl()` no longer throws `InvalidArgumentException`
     if the argument is not a URL.

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
