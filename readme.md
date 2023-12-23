This contains the source files for the "*Omnipedia - Discourse*" Drupal module,
which provides [Discourse](https://discourse.org/) integration and functionality
for [Omnipedia](https://omnipedia.app/).

⚠️ ***[Why open source? / Spoiler warning](https://omnipedia.app/open-source)***

----

# Requirements

* [Drupal 9.5 or 10](https://www.drupal.org/download) ([Drupal 8 is end-of-life](https://www.drupal.org/psa-2021-11-30))

* PHP 8.1

* [Composer](https://getcomposer.org/)

## Drupal dependencies

Before attempting to install this, you must add the Composer repositories as
described in the installation instructions for these dependencies:

* The [`omnipedia_core` module](https://github.com/neurocracy/drupal-omnipedia-core).

* The [`omnipedia_main_page` module](https://github.com/neurocracy/drupal-omnipedia-main-page).

----

# Installation

## Composer

### Set up

Ensure that you have your Drupal installation set up with the correct Composer
installer types such as those provided by [the `drupal/recommended-project`
template](https://www.drupal.org/docs/develop/using-composer/starting-a-site-using-drupal-composer-project-templates#s-drupalrecommended-project).
If you're starting from scratch, simply requiring that template and following
[the Drupal.org Composer
documentation](https://www.drupal.org/docs/develop/using-composer/starting-a-site-using-drupal-composer-project-templates)
should get you up and running.

### Repository

In your root `composer.json`, add the following to the `"repositories"` section:

```json
"drupal/omnipedia_discourse": {
  "type": "vcs",
  "url": "https://github.com/neurocracy/drupal-omnipedia-discourse.git"
}
```


### Patching

This provides [one or more patches](#patches). These can be applied automatically by the the
[`cweagans/composer-patches`](https://github.com/cweagans/composer-patches/tree/1.x)
Composer plug-in, but some set up is required before installing this module.
Notably, you'll need to [enable patching from
dependencies](https://github.com/cweagans/composer-patches/tree/1.x#allowing-patches-to-be-applied-from-dependencies) (such as this module 🤓). At
a minimum, you should have these values in your root `composer.json` (merge with
existing keys as needed):


```json
{
  "require": {
    "cweagans/composer-patches": "^1.7.0"
  },
  "config": {
    "allow-plugins": {
      "cweagans/composer-patches": true
    }
  },
  "extra": {
    "enable-patching": true,
    "patchLevel": {
      "drupal/core": "-p2"
    }
  }
}

```

**Important**: The 1.x version of the plug-in is currently required because it
allows for applying patches from a dependency; this is not implemented nor
planned for the 2.x branch of the plug-in.

### Installing

Once you've completed all of the above, run `composer require
"drupal/omnipedia_discourse:^1.0@dev"` in the root of your project to have
Composer install this and its required dependencies for you.

----

# Patches

The following patches are supplied (see [Patching](#patching) above):

* [Discourse SSO module](https://www.drupal.org/project/discourse_sso):

  * [Missing config schema [#3344663]](https://www.drupal.org/project/discourse_sso/issues/3344663)

  * [Sites that don't grant 'access content' permission to anonymous users result in access denied when redirected to the Drupal site for log in [#3371060]](https://www.drupal.org/project/discourse_sso/issues/3371060)
