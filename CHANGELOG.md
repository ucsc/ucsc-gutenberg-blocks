# Changelog

All notable changes to this project will be documented in this file. See [commit-and-tag-version](https://github.com/absolute-version/commit-and-tag-version) for commit guidelines.

## [1.2.2](https://github.com/ucsc/ucsc-gutenberg-blocks/compare/v1.2.1...v1.2.2) (2026-10-22)


### Bug Fixes

* **campus-directory:** lock saving for active invalid source [WPM-153] ([719f548](https://github.com/ucsc/ucsc-gutenberg-blocks/commit/719f548487c99acb674c594ba0fa67d066cc42a2))
* **campus-directory:** WPM-113 guard against null/empty automated-feed type attributes to prevent array_sum(null) fatal on PHP 8.1 ([38dc34e](https://github.com/ucsc/ucsc-gutenberg-blocks/commit/38dc34e9b80e9f888b7415a23d1b17bb3ab75100))
* **class-schedule:** isolate multiple blocks on the same page ([a213d2c](https://github.com/ucsc/ucsc-gutenberg-blocks/commit/a213d2cccb5d53c1ba551576008d00ddd3da58c0))
* **class-schedule:** WPM-89 show plugin header version instead of hard-coded version ([cc330a9](https://github.com/ucsc/ucsc-gutenberg-blocks/commit/cc330a9ef6d298faa5e816d1c57e4ec3ab3f3a68))
* **tests:** report PHP coverage across all plugin sources ([3e4bea3](https://github.com/ucsc/ucsc-gutenberg-blocks/commit/3e4bea370120b401ffcea1d1941bb737f672a082))
* WPM-154 harden PeopleSoft dept/subject feed fetch ([f33118a](https://github.com/ucsc/ucsc-gutenberg-blocks/commit/f33118ae07c2a3398824ab0a82af7aba196b4eb9))

## [1.2.1](https://github.com/ucsc/ucsc-gutenberg-blocks/compare/v1.2.0...v1.2.1) (2026-08-25)


### Bug Fixes

* **campus-directory:** WPM-114 restore standalone profile template on singular pages ([3c30ecb](https://github.com/ucsc/ucsc-gutenberg-blocks/commit/3c30ecb0d291b7e4299bad41df52762cc7db863a))

## [1.2.0](https://github.com/ucsc/ucsc-gutenberg-blocks/compare/v1.1.39...v1.2.0) (2026-07-29)


### Features

* **class-schedule:** WPM-98 let site editor set default visible columns ([5784ff6](https://github.com/ucsc/ucsc-gutenberg-blocks/commit/5784ff6fd947a256de2b697d099d773b54d046ac))


### Bug Fixes

* **class-schedule:** WPM-98 update Jest coverage for default-columns panel ([6bd0cda](https://github.com/ucsc/ucsc-gutenberg-blocks/commit/6bd0cdaec071a36e5a67970299ec8473f49ed750))
* **course-catalog:** WPM-104 unbreak PHP suite, validate feed XML before caching ([bffe6ec](https://github.com/ucsc/ucsc-gutenberg-blocks/commit/bffe6ecb63b5e244268fba8e67500176eb47dba6))

## [1.1.39](https://github.com/ucsc/ucsc-gutenberg-blocks/compare/v1.1.38...v1.1.39) (2026-06-18)

## [1.1.38](https://github.com/ucsc/ucsc-gutenberg-blocks/compare/v1.1.37...v1.1.38) (2026-06-18)


### Bug Fixes

* **campus-directory:** WPM-99 keep site nav on profile pages for block themes ([20572cf](https://github.com/ucsc/ucsc-gutenberg-blocks/commit/20572cfd01c94db1294ecff12c7d6f7aac51f015))
* **directory:** WPM-99 preserve original content when mounting profile via the_content filter ([7ea491a](https://github.com/ucsc/ucsc-gutenberg-blocks/commit/7ea491a9410228f826d8bfede0180c6f9bd385c7))

### [1.1.29](https://github.com/ucsc/ucsc-gutenberg-blocks/compare/v1.1.28...v1.1.29) (2024-06-27)

### [1.1.28](https://github.com/ucsc/ucsc-gutenberg-blocks/compare/v1.1.27...v1.1.28) (2024-06-27)


### Bug Fixes

* 🚑️ Remove polyfill.io script ([#135](https://github.com/ucsc/ucsc-gutenberg-blocks/issues/135)) ([9ccd29e](https://github.com/ucsc/ucsc-gutenberg-blocks/commit/9ccd29e8d7f1b2541607067beafe455a953952d5))

### [1.1.27](https://github.com/ucsc/ucsc-gutenberg-blocks/compare/v1.1.26...v1.1.27) (2024-06-26)


### Bug Fixes

* 📝 Small update to language in the release file ("theme" ➡️ "plugin") ([5ceadd9](https://github.com/ucsc/ucsc-gutenberg-blocks/commit/5ceadd99fa70a99e2e48f2e53c5ba5aa2d38d036))

### [1.1.26](https://github.com/ucsc/ucsc-gutenberg-blocks/compare/v1.1.25...v1.1.26) (2024-06-24)


### Bug Fixes

* Add deprecation labels to Accordion block and Accordion Wrapper block ([#130](https://github.com/ucsc/ucsc-gutenberg-blocks/issues/130)) ([ed46f64](https://github.com/ucsc/ucsc-gutenberg-blocks/commit/ed46f6496ae0ee59be2e44f6a8a602fc322962f1))
