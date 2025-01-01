# O-3PO

[![Build Status](https://travis-ci.org/quantum-journal/o3po.svg?branch=master)](https://travis-ci.org/quantum-journal/o3po) [![codecov](https://codecov.io/gh/quantum-journal/o3po/branch/master/graph/badge.svg)](https://codecov.io/gh/quantum-journal/o3po) [![GitHub license](https://img.shields.io/github/license/quantum-journal/o3po.svg)](https://github.com/quantum-journal/o3po/blob/master/license.txt)



* Contributors: cgogolin, johannesjmeyer, drever
* Donate link: https://quantum-journal.org/donate/
* Tags: publishing, open-access
* Requires at least: 4.0.1
* Tested up to: 5.7.2
* Requires PHP: 7.2
* Stable tag: 1.0.0
* License: GPLv3 or later
* License URI: http://www.gnu.org/licenses/gpl-3.0.html

O-3PO stands for Open-source Open-access Overlay Publishing Option, it intends to make publishing of open-access arXiv overlay journals on WordPress powered websites as easy as possible.

O-3PO powers the website of [Quantum - the open journal for quantum science](https://quantum-journal.org/) and was originally developed for this purpose.

## Warning

This is beta software, which still may contain site-specific and/or not well tested code.

## Description

O-3PO offers the following features:

* Automatic download of LaTeX source code and PDF from the arXiv
* Automatic extraction of meta-data
* Automagic interpretation of references and DOI links
* DOI registration with Plan S compliant rich meta-data deposits including bibliography and funder information
* Xited-by data retrieval from [Crossref REST API](https://api.crossref.org/) and [NASA ads](https://ui.adsabs.harvard.edu/)
* Merging of cited-by information from multiple sources and bibliometrics calculation
* Meta-data deposition at the [DOAJ API](https://doaj.org/api/v1/docs)
* Article source and pdf download as well as meta-data extraction from, as well as journal reference feed for the [arXiv](https://arxiv.org/help/api/index)
* Meta-data and full text deposition in the [CLOCKSS](https://www.clockss.org/clockss/Home) archive
* Posting of updates on publications to the [Buffer.com](https://buffer.com/app) queue
* Linking to [Fermat's library](https://fermatslibrary.com/)
* Automatic and customizable emails for author communication
* Search centered navigation of articles
* Integration of publications in to RSS feed
* Presentation of journal content by volume
* Web-statement endpoint for the verification of licenses
* Transformation of \cite commands in post content into hyperlinks referencing bibliography items
* Form for the submission of works that are ready to publish by the authors, including various sanity checks and collection of additional meta data
* Queuing system for manuscripts awaiting publication
* Functionality for the generation of invoices

## Works best with

* [OnePress theme](https://www.famethemes.com/themes/onepress/)
* [Relevanssi](https://wordpress.org/plugins/relevanssi/)

In fact, certain features of O-3PO may only work correctly in conjunction with the OnePress theme.

## Installation

1. Git clone this repository via: `git clone https://github.com/quantum-journal/o3po.git`
2. Copy the `o3po` directory into your `wp-content/plugins/` directory.
3. Activate the plugin through the Plugins menu of WordPress.

## Installation

O-3PO uses the PHPUnit testing framework. You can run all tests locally by executing `make test`.

## Documentation

Please refer to the [online documentation](https://quantum-journal.github.io/o3po/) to learn about the implementation.

The documentation can be build locally by running `make docs`.

## Bugs, limitations, and to do

* Move fix_custom_logo_html into separate plug-in
* Move remaining onepress related customizations into a separate plug-in

## Frequently Asked Questions

### Why should I use O-3PO?

O-3PO is open source, actively maintained, and allows you to combine all the features of a professional publishing platform, with exactly the integrations an arXiv overlay journal needs, with the power of the worlds most popular blogging platform.

### Why the name O-3PO?

It is obviously a play on the name of humanoid robot character from a series of popular science fiction movies, whose primary purpose is to assist people in their communication, just like this plugin.

## Screenshots

1. Various settings can be customized via the settings.

## Changelog

### 0.4.3
* Unpack tar.gz files in one go to avoid problems with long file names (see https://stackoverflow.com/questions/24800217/phardata-limitation-of-file-name-length)
* Include formated affiliations in meta-data.
* Fixed detection of license information from arXiv abstract page.
### 0.4.2
* Treat .txt files like LaTeX source files because this is what the arXiv seems to be doing
* More robust error handling for the case of pdf-only arXiv manuscripts
* Support for ShortDOIs https://shortdoi.org/
* Prevent accidental trashing or deletion of publication posts after public publishing
### 0.4.1
* Added shortcodes to generate various lists of contributing people from data that can be specified in the plugin settings
* Support database cites in cited by data
* Fix for recognizing certain ways of specifying affiliations from latex source code
* Ensure that MathML title and absctract are always valid xml
### 0.4.0
* Compatibility with PHP 8.0
* Added form for submission to works ready to publish
* Added dashboard widget displaying a queue of works awaiting publication
* Added functionality to generate invoices
* Correctly handle and display different name style (given name(s) first or last)
* Improved error handling in interaction with CLOCKSS
* Updated used Crossref meta-data scheme to version 4.4.2 and include funder information
* Improved fuzzy identification of DOIs from bibliographies
* Better handling of LaTeX commands when converting to utf-8
* Advanced bibliometrics including calculation of a lower bound on the journal impact factor (JIF) from openly available citation data
### 0.3.1
* Uncluttered RSS feed content
* Use button instead of form/input elements for action buttons (full text, print, ...)
* improved generation of Trackbacks
* Fixed truncation of formulas when creating excerpts
* Extract DOIs from ULRs as a fallback also from BibLaTeX bibliographies
* Minor bug fixes
### 0.3.0
* Added maintenance mode.
* O-3PO is now largely theme independent.
* Settings page completely redesigned.
* Email templates and various other aspects of the plugin are now customizable.
* Tweaked appearance of publications.
* Code quality and test coverage improved.
* Improved cited-by data parsing.
* Support for buffer.com https api
* Greatly improved documentation of settings
* Better test coverage
* Support for \cite commands and improved parsing of \affiliation macros
### 0.2.2
* Settings page now uses password text fields for sensitive settings.
* Fixed file name of full text pdf download.
### 0.2.1
* Fix for an additional incompatibility with PHP >=7.1.
* Restrict visible indication of test system to adminbar to allow setup on production system.
### 0.2.0
* Now compatible with and tested on multiple PHP versions ranging from 5.6 to 7.2.8.
* CLOCKSS interface activated.
* Search page template with extra feedback to users can now be deactivated in settings.
* Many smaller bug fixes.
* Test coverage massively increased.

### 0.1.0
* First publicly available version.

## License

The WordPress Plugin O-3PO is licensed under the GPL v3 or later.

> This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License, version 2, as published by the Free Software Foundation.
> This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
> You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA

A copy of the license is included in the root of the plugin’s directory. The file is named `license`.

## Credits

This plugin is based on the structure provided by the [WordPress-Plugin-Boilerplate](https://github.com/DevinVinson/WordPress-Plugin-Boilerplate/tree/master/plugin-name).
