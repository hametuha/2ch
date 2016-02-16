2ch
======================

Contributors: Takahashi_Fumiki  
Tags: bbs, forum  
Requires at least: 4.3   
Tested up to: 4.5alpha  
Stable tag: 1.0.0  
License: GPLv3 or later  

WordPress plugin for Japanese notorious anonymous BBS clone.

## Description

You can create anonymous BBS like [2ch.net](http://2ch.net).

## Installation

- Upload this folder to your `wp-content/plugins` directory.
- Activate it from Plugin's page.
- Do some setting on **Setting > 2ch Setting**

## Changelog

- 1.0.0 First release.

## Customization

Besides it's setting screen on WordPress admin panel, 2ch has a lot of options to
customize. Please visit [our Wiki](https://github.com/hametuha/2ch/wiki)!

## Contribution

This plugin is hosted on [github](https://github.com/hametuha/2ch). 
Any pull requests are welcomed.

On github repository, required files are ignored by .gitignore. 
You have to build the missing files. [Composer](https://getcomposer.org/) and [npm](https://www.npmjs.com/) is required.

```
# Go in to this directory.
cd path/to/this/dir
# Install composer.
composer install
# Install node modules.
npm install
# Build files.
npm run build
# Watch static files on development.
npm run watch
```
