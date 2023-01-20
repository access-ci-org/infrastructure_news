# ACCESS CONECT Information Sharing Platform
# Drupal Feed of Infrastructure News

## Summary

Pulls infrastructure news from the API. Maps Resource id from ACCESS Active Resources from CiDeR to Affected Infrastructure (affected_infrastructure) field.

## Basic Information

Basic information includes:
Due to order of operations, this module maily adds operations_cider as a requirement so that will be enabled and then in the install file enables a sub-module 'infrastructure_news_config' which is all of the configuration files to create the node, view, and feed type. Then the install module will add the feed. After all of that, 'infrastructure_news_config' is no longer needed and disabled. Any future update hooks or custom code will be added to the main module 'infrastructure_news' but currently this module is pretty bare bones.

## Implementation Details

Creates these Drupal elements:
- Content Type: Infrastructure News
  Machine name: infrastructure_news
- Feed Type: Infrastructure News
  Machine name: infrastructure_news_feed
- Feed: Retrieve Infrastructure News from Operations API

Developed and tested using:
- Drupal 9.5.2
- [Feeds 8.x-3.0-beta2](https://www.drupal.org/project/feeds)
- [Feeds Extensible Parsers 8.x-1.0-beta1](https://www.drupal.org/project/feeds_ex)
- [Field Group 8.x-3.4](https://www.drupal.org/project/field_group)

## Installation
*Add repository to composer.json:*
```
"repositories": {
    ...
    "infrastructure_news": {
       "type": "vcs",
       "url": "https://github.com/access-ci-org/infrastructure_news.git"
    },
    "operations_cider": {
       "type": "vcs",
       "url": "https://github.com/access-ci-org/Operations_Drupal_Feed_Cider.git"
    }
},
```

*Install the modules:*
```
composer require access/infrastructure_news access/operations_cider
```
*Enable module:*
```
drush en infrastructure_news
```

## Developer Notes
