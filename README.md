Cron Status
===========

Cron Status displays an overview of Board's Cron Jobs in Maintenance module of ACP. This extension also shows a notification on the main page of ACP when a cron job is locked (optionally). It shows the time when the cron task started and which cron task was the problem.

## Requirements
* phpBB 3.1.0-dev or higher (phpBB 3.1.0-RC3 or higher for Cron Status Notice on ACP index)
* PHP 5.3.3 or higher

## Installation
You can install this extension on the latest copy of the develop branch ([phpBB 3.1-dev](https://github.com/phpbb/phpbb3)) by doing the following:

1. Copy the [entire contents of this repo](https://github.com/BoardTools/cronstatus/archive/master.zip) to `FORUM_DIRECTORY/ext/boardtools/cronstatus/`.
2. Navigate in the ACP to `Customise -> Extension Management -> Manage extensions`.
3. Click Cron Status => `Enable`.

Note: This extension is in development. Installation is only recommended for testing purposes and is not supported on live boards. This extension will be officially released following phpBB 3.1.0. Extension depends on two core changes.

## Usage
### Cron Status page
To check the Cron Status navigate in the ACP to `Maintenance -> Cron Status -> Check Cron Status`.

### Cron Status Notice
Cron Status Notice is displayed on the main page of ACP when Cron is locked. This notice provides some information about the last Cron task that made Cron locked.
If you want to remove Cron Status Notice from the main page of ACP (to use only Cron Status page), navigate in the ACP to `General -> Board configuration -> Board settings -> Cron Status -> Notice on ACP index page` and set it to "No".

### Cron Status settings
You can change the date format for Cron Status or remove Cron Status Notice that is displayed on ACP index.
Navigate in the ACP to `General -> Board configuration -> Board settings -> Cron Status`.

## Update
1. Download the [latest ZIP-archive of `master` branch of this repository](https://github.com/BoardTools/cronstatus/archive/master.zip).
2. Navigate in the ACP to `Customise -> Extension Management -> Manage extensions` and click Cron Status => `Disable`.
3. Copy the contents of `cronstatus-master` folder to `FORUM_DIRECTORY/ext/boardtools/cronstatus/`.
4. Navigate in the ACP to `Customise -> Extension Management -> Manage extensions` and click Cron Status => `Enable`.
5. Click `Details` or `Re-Check all versions` link to follow updates.

## Uninstallation
Navigate in the ACP to `Customise -> Extension Management -> Manage extensions` and click Cron Status => `Disable`.

To permanently uninstall, click `Delete Data` and then you can safely delete the `/ext/boardtools/cronstatus/` folder.

## License
[GNU General Public License v2](http://opensource.org/licenses/GPL-2.0)

© 2014 - John Peskens (http://ForumHulp.com) and Igor Lavrov (https://github.com/LavIgor)