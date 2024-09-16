<!--- BEGIN HEADER -->
# Changelog

All notable changes to this project will be documented in this file.
<!--- END HEADER -->

## [9.6.4](https://github.com/UN-OCHA/gms-site/compare/v9.6.3...v9.6.4) (2023-11-27)

### Bug Fixes

* Patch contact_storage so that site install may succeed, so we can run tests. ([6786d8](https://github.com/UN-OCHA/gms-site/commit/6786d836cbed14056abf6c9d603d58f3f3a1576c))
* Userprotect cannot install from scratch if the config is not up to date. ([628fa9](https://github.com/UN-OCHA/gms-site/commit/628fa93815abeb83fc8027490a72103a4b02fa62))


---

## [9.6.3](https://github.com/UN-OCHA/gms-site/compare/v9.6.2...v9.6.3) (2023-11-14)

### Bug Fixes

* Enable core search, as the custom search pages use its search form block and pages break without it. ([a56d7c](https://github.com/UN-OCHA/gms-site/commit/a56d7ccb0db2275ef7e9bffce015c4eff0547d6b))
* Export once the search blocks work. ([ed65dd](https://github.com/UN-OCHA/gms-site/commit/ed65ddf1a6e0ae9b40077a960357ba411dd09255))
* Make the search indexer use fields, to avoid indexing errors on rendered content. ([ca90ee](https://github.com/UN-OCHA/gms-site/commit/ca90eebf06196db1a75eb9da5eb53fac9dc33e8c))
* One more. Bump composer, so we have an audit command and do not install dev dependencies. ([0293cb](https://github.com/UN-OCHA/gms-site/commit/0293cb93f7d9f2f948d0a19b30a224c3c12b01dd))
* This is the controller we invoke, so make it return its response object. ([2052aa](https://github.com/UN-OCHA/gms-site/commit/2052aaa443f6dbf048cfb7291f2fe886757ad79e))
* Use vars.SLACK_CHANNEL for the notifcations after ensuring dependabot can see that too. ([2c6ee6](https://github.com/UN-OCHA/gms-site/commit/2c6ee656d1f6b07a1010d47e1776224b720da457))


---

## [9.6.2](https://github.com/UN-OCHA/gms-site/compare/v9.6.1...v9.6.2) (2023-10-18)


---

## [9.6.1](https://github.com/UN-OCHA/gms-site/compare/v9.6.0...v9.6.1) (2023-09-22)


---

## [9.6.0](https://github.com/UN-OCHA/gms-site/compare/v9.5.10...v9.6.0) (2023-09-04)

### Bug Fixes

* Annoyingly, GMS still uses sass, so needs a builder with node. ([4a7a38](https://github.com/UN-OCHA/gms-site/commit/4a7a38bfa38db371dda3859456fe3e2d922c72ac))
* Bump config_split and add config_filter. ([7c30be](https://github.com/UN-OCHA/gms-site/commit/7c30be46f97abb7b0eaaca724e62edca002c0259))
* Declare dependency and set service args. ([62a035](https://github.com/UN-OCHA/gms-site/commit/62a035e760d0a177b67199189450946c107ab807))
* Depend on an actual module, not an imaginary one. ([fa7225](https://github.com/UN-OCHA/gms-site/commit/fa7225c27a8b968e7220c400cb7d302a20d4c782))
* Drop the 8.1 builder and the sass build step. Commit theme build guff in case it is needed. Add README note. ([e56da9](https://github.com/UN-OCHA/gms-site/commit/e56da90cc3f6b5b7b9c0d17d3735b56816503d23))
* Drop the core key from the info file, spell check dependencies and re-order the use statements. ([cade15](https://github.com/UN-OCHA/gms-site/commit/cade15ffc0b18f82e39c22e3ac1c3b8fd17615c2))
* Ensure settings.php works with drush 12. ([b2a9a2](https://github.com/UN-OCHA/gms-site/commit/b2a9a21f16c40264afebf67bab2b41616de1de59))
* Patch ocha_snap module and revert unnecessary changes ([fd63b8](https://github.com/UN-OCHA/gms-site/commit/fd63b8b24e39e8a8154370fc2d637ab08a61df21))
* Set the required PHP version to 8.2 in composer.json. ([9ebc51](https://github.com/UN-OCHA/gms-site/commit/9ebc51707b7e1c75e99827c4452454b4b9614f7f))
* Undo an oopsie. ([f8aef4](https://github.com/UN-OCHA/gms-site/commit/f8aef4a7c551b9bb363b62718b442a8d8f3ae5b7))
* Wait, we need to depend on contact_storage too. ([8fb3c9](https://github.com/UN-OCHA/gms-site/commit/8fb3c98f801dbb57a8a9ccad0b3bd388a0f5700d))


---

## [9.5.10](https://github.com/UN-OCHA/gms-site/compare/v9.5.9...v9.5.10) (2023-07-19)

### Bug Fixes

* Adjust order of use statements for phpcs ([dd3879](https://github.com/UN-OCHA/gms-site/commit/dd3879416758735e96c40484b064939cc8b0ed05))
* Complete change in case for patches dir ([b2f965](https://github.com/UN-OCHA/gms-site/commit/b2f965de62550d509d8ac26d25df7e0061c80b32))
* Look at that, more domain related fields. Shoo! ([775bd7](https://github.com/UN-OCHA/gms-site/commit/775bd77255d86bf147a591665d41b7cb51dfbca3))
* Remove permissions that pertain to the domain module, which is not installed. ([c2d3f3](https://github.com/UN-OCHA/gms-site/commit/c2d3f352d658c05fff3dd587a3d9bbce521377a7))
* Remove the composer-preserve-paths script permission. ([bf061c](https://github.com/UN-OCHA/gms-site/commit/bf061ccf0082d07aa1a50d7f8d50ce41151db6cf))


---

## [9.5.9](https://github.com/UN-OCHA/gms-site/compare/v9.5.8...v9.5.9) (2023-06-12)

### Bug Fixes

* Return response objects for failed pdfs ([ec5f27](https://github.com/UN-OCHA/gms-site/commit/ec5f27f6e2948c4bf370026b386377dcca9a79df))


---

## [9.5.8](https://github.com/UN-OCHA/gms-site/compare/v9.5.7...v9.5.8) (2023-05-22)


---

## [9.5.7](https://github.com/UN-OCHA/gms-site/compare/v9.5.6...v9.5.7) (2023-05-10)


---

## [9.5.6](https://github.com/UN-OCHA/gms-site/compare/v9.5.5...v9.5.6) (2023-04-24)


---

## [9.5.5](https://github.com/UN-OCHA/gms-site/compare/v9.5.4...v9.5.5) (2023-04-19)

### Bug Fixes

* Bump the docker image to a fixed one. ([09c8c5](https://github.com/UN-OCHA/gms-site/commit/09c8c558648b1af3d02722eaa1932db724b04d4d))
* Bump the docker image to a propoerly fixed one. ([0a2ae6](https://github.com/UN-OCHA/gms-site/commit/0a2ae63947b4d30a42e64b55c94b595aaa90852a))


---

## [9.5.4](https://github.com/UN-OCHA/gms-site/compare/v9.5.3...v9.5.4) (2023-03-16)


---

## [9.5.3](https://github.com/UN-OCHA/gms-site/compare/v9.5.2...v9.5.3) (2023-03-15)

### Features

* Drop in phpunit and code coverage so we can use them for that. ([3ec924](https://github.com/UN-OCHA/gms-site/commit/3ec924c975d11137b7ed3ffb7a58378d91c61ff3))
* Drop in the run-tests workflow in favour of the Travis CI one. ([c8ef7f](https://github.com/UN-OCHA/gms-site/commit/c8ef7f8f038378018699f7983d0943f000567536))

### Bug Fixes

* Add in PHPUnit config and make it available for tests. ([956ca1](https://github.com/UN-OCHA/gms-site/commit/956ca169c12dbc7ed82029ef090bc39262bd669f))
* And that depends on drupal-test-traits, so add that too. ([ecf0a3](https://github.com/UN-OCHA/gms-site/commit/ecf0a3667a234273d9529c263967f3f20473e23b))
* Do not modify the defaults when they are overridden via Ansible anyway. ([b954a4](https://github.com/UN-OCHA/gms-site/commit/b954a4e5888f4ff9efc942328e3b9464b9474a5b))
* Drop FLOWDOCK. ([c3fff6](https://github.com/UN-OCHA/gms-site/commit/c3fff6b407ae80897c89049973b95865e8c9d7b6))
* Ensure xdebug is loaded, so phpunit can do coverage. ([dd0059](https://github.com/UN-OCHA/gms-site/commit/dd0059bf78037a4eccb4b07e992a25708a4478c3))
* Pass the token as the correct variable. ([2f74c5](https://github.com/UN-OCHA/gms-site/commit/2f74c5c7047cc33ddfd5194fb75a5eda10ccc503))


---

## [9.5.2](https://github.com/UN-OCHA/gms-site/compare/v9.5.1...v9.5.2) (2023-02-14)


---

## [9.5.1](https://github.com/UN-OCHA/gms-site/compare/v9.5.0...v9.5.1) (2023-01-23)


---

## [9.5.0](https://github.com/UN-OCHA/gms-site/compare/v9.4.16...v9.5.0) (2023-01-20)

### Bug Fixes

* Copy config/contact.form.request_form.yml from the feature branch. ([2ed8d3](https://github.com/UN-OCHA/gms-site/commit/2ed8d38ab69296d17e4446f0a4c67ef0c25d3aec))
* Copy config/views.view.contact_messages.yml from the feature branch. ([fab1db](https://github.com/UN-OCHA/gms-site/commit/fab1db72abb3e9d48e2ce3b4acad7143c6cbc9f7))
* Patch GTM so that it stops making trouble on NFS. ([2a2ae0](https://github.com/UN-OCHA/gms-site/commit/2a2ae023582f2120e1f6cd4d441758dcf664dd69))
* Remove install from travis tests, temporarily ([41cd10](https://github.com/UN-OCHA/gms-site/commit/41cd1001f995a2a8614d8fc2a12672ef11d516d9))
* Rerun sass and commit the result. ([aa7cff](https://github.com/UN-OCHA/gms-site/commit/aa7cffa9d9b28b93af399245083fce832e265905))
* Use the current MySQL in tests. ([1a461c](https://github.com/UN-OCHA/gms-site/commit/1a461c4be2438cfaf0ccb7f68dad8e9fc8e3c14b))


---

## [9.4.16](https://github.com/UN-OCHA/gms-site/compare/v9.4.15...v9.4.16) (2022-12-14)


---

## [9.4.15](https://github.com/UN-OCHA/gms-site/compare/v9.4.14...v9.4.15) (2022-12-13)


---

## [9.4.14](https://github.com/UN-OCHA/gms-site/compare/v9.4.13...v9.4.14) (2022-12-13)


---

## [9.4.13](https://github.com/UN-OCHA/gms-site/compare/v9.4.12...v9.4.13) (2022-11-15)

### Features

* Allow updates to be run manually ([7b016d](https://github.com/UN-OCHA/gms-site/commit/7b016d35be43690fdf1d6ec8577ba7887b30e928))

### Bug Fixes

* Include csp exceptions for unsafe-inline ([63f65b](https://github.com/UN-OCHA/gms-site/commit/63f65b985363dfa7e945dc266aa393535f798f7f))
* Rename functions as suggested by coder module ([44f3b7](https://github.com/UN-OCHA/gms-site/commit/44f3b7c6726251e1cb2d1d2a629fe71ead037966))
* Update hook_event_dispatcher module and uninstall color module ([744954](https://github.com/UN-OCHA/gms-site/commit/744954547aa20e219c34ec584840932a1eabb72d))


---

## [9.4.12](https://github.com/UN-OCHA/gms-site/compare/v9.4.11...v9.4.12) (2022-10-10)

### Features

* Allow updates to be run manually ([7b016d](https://github.com/UN-OCHA/gms-site/commit/7b016d35be43690fdf1d6ec8577ba7887b30e928))

### Bug Fixes

* Include csp exceptions for unsafe-inline ([63f65b](https://github.com/UN-OCHA/gms-site/commit/63f65b985363dfa7e945dc266aa393535f798f7f))
* Rename functions as suggested by coder module ([44f3b7](https://github.com/UN-OCHA/gms-site/commit/44f3b7c6726251e1cb2d1d2a629fe71ead037966))
* Update hook_event_dispatcher module and uninstall color module ([744954](https://github.com/UN-OCHA/gms-site/commit/744954547aa20e219c34ec584840932a1eabb72d))


---

## [9.4.11](https://github.com/UN-OCHA/gms-site/compare/v9.4.10...v9.4.11) (2022-10-04)

### Features

* Allow updates to be run manually ([7b016d](https://github.com/UN-OCHA/gms-site/commit/7b016d35be43690fdf1d6ec8577ba7887b30e928))


---

## [9.4.10](https://github.com/UN-OCHA/gms-site/compare/v9.4.9...v9.4.10) (2022-09-14)


---

## [9.4.9](https://github.com/UN-OCHA/gms-site/compare/v9.4.8...v9.4.9) (2022-09-14)


---

## [9.4.8](https://github.com/UN-OCHA/gms-site/compare/v9.4.7...v9.4.8) (2022-09-09)

### Bug Fixes

* Rename functions as suggested by coder module ([44f3b7](https://github.com/UN-OCHA/gms-site/commit/44f3b7c6726251e1cb2d1d2a629fe71ead037966))
* Update hook_event_dispatcher module and uninstall color module ([744954](https://github.com/UN-OCHA/gms-site/commit/744954547aa20e219c34ec584840932a1eabb72d))


---

## [9.4.7](https://github.com/UN-OCHA/gms-site/compare/v9.4.6...v9.4.7) (2022-08-17)

### Bug Fixes

* Include csp exceptions for unsafe-inline ([63f65b](https://github.com/UN-OCHA/gms-site/commit/63f65b985363dfa7e945dc266aa393535f798f7f))
* Try something unrelated to fix patching error in build ([61d404](https://github.com/UN-OCHA/gms-site/commit/61d404bc6fcd606aa4a829198d05393a44fa09e7))


---

## [9.4.6](https://github.com/UN-OCHA/gms-site/compare/v9.4.5...v9.4.6) (2022-07-28)


---

## [9.4.5](https://github.com/UN-OCHA/gms-site/compare/v9.4.4...v9.4.5) (2022-07-28)


---

## [9.4.4](https://github.com/UN-OCHA/gms-site/compare/v9.4.3...v9.4.4) (2022-07-27)


---

## [9.4.3](https://github.com/UN-OCHA/gms-site/compare/v9.4.2...v9.4.3) (2022-07-26)


---

## [9.4.2](https://github.com/UN-OCHA/gms-site/compare/v9.4.1...v9.4.2) (2022-07-13)


---

## [9.4.1](https://github.com/UN-OCHA/gms-site/compare/v9.3.15...v9.4.1) (2022-06-28)

### Features

* Remove redis altogether, as it slows sites down. ([4d33a8](https://github.com/UN-OCHA/gms-site/commit/4d33a89d78cefec392f3d6868d269861c75893cc))

### Bug Fixes

* Enable new mysql module ([bd336a](https://github.com/UN-OCHA/gms-site/commit/bd336afb31b29f17d6f56f6735ffc0c58d98108b))
* Ugh. ([59da45](https://github.com/UN-OCHA/gms-site/commit/59da4545d6217941c3962ad96d4320593ab2b755))


---

## [9.3.15](https://github.com/UN-OCHA/gms-site/compare/v9.3.14...v9.3.15) (2022-06-14)

### Features

* Notify of builds via Slack. ([da855b](https://github.com/UN-OCHA/gms-site/commit/da855b999f32c92472b71be66df9db43bfe5e740))


---

## [9.3.14](https://github.com/UN-OCHA/gms-site/compare/v9.3.12...v9.3.14) (2022-05-31)


---

## [9.3.12](https://github.com/UN-OCHA/gms-site/compare/v9.3.11...v9.3.12) (2022-04-27)


---

## [9.3.11](https://github.com/UN-OCHA/gms-site/compare/v9.3.10...v9.3.11) (2022-03-30)


---

## [9.3.10](https://github.com/UN-OCHA/gms-site/compare/v9.3.9...v9.3.10) (2022-03-30)


---

## [9.3.9](https://github.com/UN-OCHA/gms-site/compare/v9.3.8...v9.3.9) (2022-03-23)


---

## [9.3.8](https://github.com/UN-OCHA/gms-site/compare/v9.3.7...v9.3.8) (2022-03-21)


---

## [9.3.7](https://github.com/UN-OCHA/gms-site/compare/v9.3.6...v9.3.7) (2022-03-21)


---

## [9.3.6](https://github.com/UN-OCHA/gms-site/compare/v1.0.1...v9.3.6) (2022-02-17)


---

## [1.0.1](https://github.com/UN-OCHA/gms-site/compare/v1.0.0...v1.0.1) (2022-02-15)


---

## [1.0.0](https://github.com/UN-OCHA/gms-site/compare/v0.0.99...v1.0.0) (2022-02-08)


---

## [0.0.99](https://github.com/UN-OCHA/gms-site/compare/8a27d86d37997470d65134493bd0e2e24fcf49be...v0.0.99) (2022-02-08)


---

