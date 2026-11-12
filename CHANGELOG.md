# Changelog

All notable changes to this project will be documented in this file. See [commit-and-tag-version](https://github.com/absolute-version/commit-and-tag-version) for commit guidelines.

## [1.3.1](https://github.com/ucsc/ucsc-gutenberg-blocks/compare/v1.3.0...v1.3.1) (2026-11-12)


### Bug Fixes

* use a custom upload directory for campus directory photo cache and reduce scandir calls with file list hook ([4a0ef01](https://github.com/ucsc/ucsc-gutenberg-blocks/commit/4a0ef01d09f70a83c833b63a2c4039e1c08b5345))

## [1.3.0](https://github.com/ucsc/ucsc-gutenberg-blocks/compare/v1.2.4...v1.3.0) (2026-11-11)


### Features

* Cache Campus Directory photos as attachment posts to use CampusPress CDN ([4239401](https://github.com/ucsc/ucsc-gutenberg-blocks/commit/42394015b519b9da8d923c7d143a6405129b3d18))

## [1.2.4](https://github.com/ucsc/ucsc-gutenberg-blocks/compare/v1.2.3...v1.2.4) (2026-11-03)


### Bug Fixes

* WPM-144: allow rich text fields to display properly by sanitizing instead of escaping ([b534497](https://github.com/ucsc/ucsc-gutenberg-blocks/commit/b534497d0272cf49cbf7cf4ddf242a71d4fb56ae))
* WPM-144: also remove style tags when sanitizing, fix paragraph and list rendering ([4039089](https://github.com/ucsc/ucsc-gutenberg-blocks/commit/403908954ad7ddbc04c065f11f953a42f83af690))

## [1.2.3](https://github.com/ucsc/ucsc-gutenberg-blocks/compare/v1.2.2...v1.2.3) (2026-11-02)


### Bug Fixes

* **a11y:** WPM-250 WPM-92 WPM-95 WPM-111 drop editor H5 labels, add name heading level setting, show Class Schedule labels and a quarter Go button ([47c9bbf](https://github.com/ucsc/ucsc-gutenberg-blocks/commit/47c9bbf5e07aed556969df6c2885846bb1c30675))
* **campus-directory:** WPM-183 default automatedFeeds to false so profile title lookups log no warnings ([5b6dab6](https://github.com/ucsc/ucsc-gutenberg-blocks/commit/5b6dab6008474fc72dfc6bcd38ed8662a57635d9))
* **campus-directory:** WPM-228 mention 'Create My Own List of People to Display' in the unable-to-publish notice ([8977390](https://github.com/ucsc/ucsc-gutenberg-blocks/commit/89773908e578293efdb21997281a330f937bebf7))
* **campus-directory:** WPM-229 explain that unchecking 'Link to campusdirectory.ucsc.edu' shows profiles on this site ([153f156](https://github.com/ucsc/ucsc-gutenberg-blocks/commit/153f15682f9a3435e92e96a67b08007a86467cc7))
* **campus-directory:** WPM-230 serve directory photos only from LDAP and drop the photo.php fallback ([5f0cdc4](https://github.com/ucsc/ucsc-gutenberg-blocks/commit/5f0cdc4417f7745ce013a0524b5a5b885a8fb483))
* **campus-directory:** WPM-231 cache directory photos 4h on the server and 1h in browsers, remove unused getDirDropdowns() ([4f73e75](https://github.com/ucsc/ucsc-gutenberg-blocks/commit/4f73e75643dad580bf021cd55d3873ef54d99848))
* **campus-directory:** WPM-233 bound LDAP stalls, cache missing directory photos and back off on bad campusdirectory answers ([c72ee66](https://github.com/ucsc/ucsc-gutenberg-blocks/commit/c72ee6661c5bfbfc98de84bc20c064f8f36b0043))
* **campus-directory:** WPM-234 connect to LDAP over ldaps with the app bind in every environment ([9162171](https://github.com/ucsc/ucsc-gutenberg-blocks/commit/9162171ddae7d6de4e15d30d884230a6b3d1b2ea))
* **campus-directory:** WPM-238 WPM-247 never cache or 404 a lookup LDAP did not answer ([ee83f3d](https://github.com/ucsc/ucsc-gutenberg-blocks/commit/ee83f3d0abf4cf8f8489ef9249e19131a26771ce))
* **campus-directory:** WPM-244 take the LDAP URL from the same scope as the password so a site admin's URL never receives the network password ([f042eca](https://github.com/ucsc/ucsc-gutenberg-blocks/commit/f042ecaf5bcb45eae7c396b80970965def3988d1))
* **campus-directory:** WPM-246 never cache a department list with no departments, back off 5 minutes instead ([037c8e9](https://github.com/ucsc/ucsc-gutenberg-blocks/commit/037c8e9edcb7a37c985e87dae5c3fc92c7a1fa4a))
* **campus-directory:** WPM-247 send a 404 status for an unknown CruzID profile and stop its PHP warnings ([c3e26e0](https://github.com/ucsc/ucsc-gutenberg-blocks/commit/c3e26e0b8ac78e47b9ee4720bc66e78dfada352b))
* **campus-directory:** WPM-257 lazy-load directory photos and only cache no photo when LDAP returned the person ([486f199](https://github.com/ucsc/ucsc-gutenberg-blocks/commit/486f199643c05b8b6d1d1e3e6cd23466e460b40c))
* **class-schedule:** WPM-236 never cache an unusable PeopleSoft dept/subject feed, back off 5 minutes and cut the timeout to 10s ([ce23835](https://github.com/ucsc/ucsc-gutenberg-blocks/commit/ce23835eabf78660a983cc106456582278f8f5d5))
* **class-schedule:** WPM-248 sort the Course # column numerically so CSE-20 comes before CSE-101 ([ccc62da](https://github.com/ucsc/ucsc-gutenberg-blocks/commit/ccc62daad5a9728063bb25fe42111b6d1a100240))
* **class-schedule:** WPM-249 prefix CSV cells that start with = + - @ so spreadsheets do not run them as formulas ([4b61ec4](https://github.com/ucsc/ucsc-gutenberg-blocks/commit/4b61ec49bf92a418903ab206e71b272881dcd5e9))
* **course-catalog:** WPM-36 WPM-92 replace the orphaned label with a div and test the heading level through theHTML ([04e97f5](https://github.com/ucsc/ucsc-gutenberg-blocks/commit/04e97f5389982ae8b5f7d44bfc63e6ff28e32841))
* **course-catalog:** WPM-96 cache catalog responses for 8 hours instead of a week ([e662698](https://github.com/ucsc/ucsc-gutenberg-blocks/commit/e662698e1851d208b9e2cff8122cc41501d37fbd))
* **editor:** WPM-207 show an error instead of loading forever when a dropdown or requirements request fails ([28155d5](https://github.com/ucsc/ucsc-gutenberg-blocks/commit/28155d5117b2f4bf1e258a57f11048e7fe8b7158))
* **editor:** WPM-254 stop setting block attributes while rendering so opening a Class Schedule or Course Catalog page no longer marks it changed ([edfe721](https://github.com/ucsc/ucsc-gutenberg-blocks/commit/edfe7217386511b0876721552fd8437b2e7e8392))
* **settings:** WPM-245 unslash the network LDAP settings on save and require manage_network_options ([3e5b560](https://github.com/ucsc/ucsc-gutenberg-blocks/commit/3e5b560400309d2363f26782878627a1a885eba4))

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
