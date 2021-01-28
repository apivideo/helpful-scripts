![](https://github.com/apivideo/API_OAS_file/blob/master/apivideo_banner.png)

# API Video 
[API Video](https://api.video/) lets you easily add video communication - recording and sharing - to all your applications.

# helpful-scripts
This repository contains useful scripts for PHP users who want to move videos into api.video more easily, or extract data details from your api.video account into a csv file. It includes:
* Google Drive to api.video - Move all your videos from a folder on your Google Drive into api.video
* FTP Server to api.video - Move all your videos from a folder on your FTP server into api.video
* Export Video Details to .csv File - Gather data about all your videos on api.video and export it in a .csv file.

# Google Drive to api.video 

Quickly ingest video from Google Drive to api.video with this script.

## Prerequisites

* PHP 5.4 or greater with the command-line interface (CLI) and JSON extension installed
* The Composer dependency management tool
* A Google account with Google Drive enabled
* An Api.video account

## Installation

```shell
composer require api-video/php-sdk
composer require google/apiclient:^2.0
```
## Walkthrough
If you want help setting up the Google Drive to api.video script, we have a complete walkthrough available:
[Helpful PHP Scripts Pt 1 - Upload Videos from Google Drive to API Video](https://api.video/blog/tutorials/helpful-php-scripts-upload-videos-from-google-drive-to-api-video)
 
## Quick Start

```shell
php Ingest_google_drive.php
```

# Ingest from ftp server to api.video 

Easily ingest files from a selected ftp.server to your api.video account. 

## Prerequisites

* PHP 5.4 or greater with the command-line interface (CLI)
* The Composer dependency management tool
* An Ftp server account
* An Api.video account

## Installation

```shell
composer require api-video/php-sdk
```

## Walkthrough
If you want help setting up the Google Drive to api.video script, we have a complete walkthrough available:
[Helpful PHP Scripts Pt 2 - Upload Videos from an FTP Server](https://api.video/blog/tutorials/helpful-php-scripts-pt-2-upload-videos-from-an-ftp-server)
 
### Quick start

```shell
php import_from_ftp.php
```

#  php-export-csv

Allow to easily export data details from tour api.video account into a csv file.

## Prerequisites

* PHP 5.4 or greater with the command-line interface (CLI)
* The Composer dependency management tool
* An Api.video account

## Installation

```composer require api-video/php-sdk
```
## Walkthrough
If you want help setting up the Export Video Details to a .csv File script, we have a complete walkthrough available:
[Helpful PHP Scripts Pt 3 - Export All Your Video Details to a .csv File](https://api.video/blog/tutorials/helpful-php-scripts-pt-3-export-all-your-video-details-to-a-csv/)

## Quick Start

```shell
php create_csv.php
```
