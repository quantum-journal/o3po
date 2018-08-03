# O-3PO

[![Build Status](https://travis-ci.org/quantum-journal/o3po.svg?branch=master)](https://travis-ci.org/quantum-journal/o3po) [![codecov](https://codecov.io/gh/quantum-journal/o3po/branch/master/graph/badge.svg)](https://codecov.io/gh/quantum-journal/o3po) [![GitHub license](https://img.shields.io/github/license/quantum-journal/o3po.svg)](https://github.com/quantum-journal/o3po/blob/master/license.txt)



* Contributors: cgogolin
* Donate link: https://quantum-journal.org/donate/
* Tags: publishing, open-access
* Requires at least: 4.0.1
* Tested up to: 4.9.5
* Requires PHP: 5.6
* Stable tag: 1.0.0
* License: GPLv3 or later
* License URI: http://www.gnu.org/licenses/gpl-3.0.html

O-3PO stands for Open-source Open-access Overlay Publishing Option, it intends to make publishing of open-access arXiv overlay journals on WordPress powered websites as easy as possible.

O-3PO powers the website of [Quantum - the open journal for quantum science](https://quantum-journal.org/) and was originally developed for this purpose.

## Description

O-3PO offers the following features:

* Automatic download of TeX source code and PDF from the arXiv
* Automatic extraction of meta-data
* Automagic interpretation of references and DOI links
* Interfaces with [Crossref REST API](https://api.crossref.org/)
* Interfaces with [DOAJ API](https://doaj.org/api/v1/docs)
* Interfaces with [arXiv](https://arxiv.org/help/api/index)
* Interfaces with [CLOCKSS](https://www.clockss.org/clockss/Home)
* Interfaces with [Buffer.com](https://buffer.com/app)
* Interfaces with [Fermat's library](https://fermatslibrary.com/)
* Search centered navigation of articles
* RSS integration feed
* arXiv recent papers feed endpoint
* PDF endpoint
* Volume endpoint
* Web-statement endpoint

## Works best with

* OnePress theme
* Relevanssi

## Installation

1. Git clone this repository via: `git clone https://github.com/quantum-journal/o3po.git`
2. Copy the `o3po` directory into your `wp-content/plugins/` directory.
3. Activate the plugin through the Plugins menu of WordPress.

## Documentation

Please refer to the [online documentation](https://quantum-journal.github.io/o3po/) to learn about the implementation.

The documentation can be build locally by running `make docs`.

## Bugs, limitations, and to do

* Extend this readme (explain onepress-extra.css, ...)
* Move div.important-box from the plugin css to the theme
* Fix for special code execution
* Move fix_custom_logo_html into separate plug-in
* Make single-paper.php theme independent
* Make handle_volumes_endpoint_request() theme independent
* Disable the fall-back loading of options from the `quantum-journal-plugin` context.

## Frequently Asked Questions

### A question that someone might have

An answer to that question.

### What about foo bar?

Answer to foo bar dilemma.

## Screenshots

1. Various settings can be customized via the settings.

## Changelog

# 0.1.0
* First publicly available version.

## Upgrade Notice

# 0.1.0
* First publicly available version.

## License

The WordPress Plugin O-3PO is licensed under the GPL v3 or later.

> This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License, version 2, as published by the Free Software Foundation.
> This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
> You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA

A copy of the license is included in the root of the plugin’s directory. The file is named `license`.

## Credits

This plugin is based on the structure provided by the [WordPress-Plugin-Boilerplate](https://github.com/DevinVinson/WordPress-Plugin-Boilerplate/tree/master/plugin-name).
