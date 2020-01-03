# Ticketsolve plugin for Craft CMS 3.x

Pulls venues, shows and events from a Ticketsolve XML feed and keeps your website in sync.

## Requirements

This plugin requires Craft CMS 3.0.0-beta.23 or later.

## Installation

To install the plugin, follow these instructions.

1. Open your terminal and go to your Craft project:

        cd /path/to/project

2. Then tell Composer to load the plugin:

        composer require devkokov/craft3-ticketsolve

3. In the Control Panel, go to Settings → Plugins and click the “Install” button for Ticketsolve.

## Configuring Ticketsolve

In the Control Panel, go to Settings → Ticketsolve and enter the URL to your Ticketsolve XML feed.

## Syncing

By default, the plugin will automatically sync from the XML feed every 15 minutes or so via a pseudo cron job.

For more robust syncing, you can disable Auto Sync in the plugin settings and trigger it from a proper server cron job using the following Craft console command:

    ./craft ticketsolve/feed/sync

## Using Ticketsolve

    ... 

---

Brought to you by [Dimitar Kokov](https://github.com/devkokov)
