# ACCESS CONECT Information Sharing Platform
# Drupal Feed of Infrastructure News

## Summary

Pulls infrastructure news from the API. Maps Resource id from ACCESS Active Resources from CiDeR to Affected Infrastructure (affected_infrastructure) field.

## Basic Information

Basic information includes:
- todo: Add field info

## Implementation Details

Creates these Drupal elements:
- Content Type: Infrastructure News
  Machine name: infrastructure_news

- Feed Type: Infrastructure News
  Machine name: infrastructure_news_feed

- Feed: Retrieve Infrastructure News from Operations API

Developed and tested using:
- Drupal 9.5.2

- Feeds 8.x-3.0-beta2
  https://www.drupal.org/project/feeds

- Feeds Extensible Parsers 8.x-1.0-beta1
  https://www.drupal.org/project/feeds_ex

## Installation
Add repository to composer.json:
```
"repositories": {
    ...
    "infrastructure_news": {
       "type": "vcs",
       "url": "https://github.com/access-ci-org/infrastructure_news.git"
    }
},
```

Install the module:
```
composer require access/infrastructure_news
```

## Developer Notes
