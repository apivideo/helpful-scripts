![](https://github.com/apivideo/API_OAS_file/blob/master/apivideo_banner.png)

# API Video 
API Video lets you easily add video communication - recording and sharing - to all your applications.

# helpful-scripts
This repository contains useful scripts for PHP users who want to move videos into api.video more easily, or extract data details from your api.video account into a csv file. 

## Google Drive to api.video 

Quickly ingest video from Google Drive to api.video with this script.

### Prerequisites 

* PHP 5.4 or greater with the command-line interface (CLI) and JSON extension installed
* The Composer dependency management tool
* A Google account with Google Drive enabled
* An Api.video account

### Installation

```shell
composer require api-video/php-sdk
composer require google/apiclient:^2.0
```
 
### Quick Start

```shell
php index.php
```

## Ingest from ftp server to api.video 

Easily ingest files from a selected ftp.server to your api.video account. 

### Prerequisites

* PHP 5.4 or greater with the command-line interface (CLI)
* The Composer dependency management tool
* An Ftp server account
* An Api.video account

### Installation

```shell
composer require api-video/php-sdk
```
 
### Quick start

```shell
php import_from_ftp.php
```

##  php-export-csv

Allow to easily export data details from tour api.video account into a csv file.

### Prerequisites

* PHP 5.4 or greater with the command-line interface (CLI)
* The Composer dependency management tool
* An Api.video account

### Installation

```composer require api-video/php-sdk
```
### Quick Start

```shell
php create_csv.php
```
