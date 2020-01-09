# Ticketsolve plugin for Craft CMS 3.x

This plugin will pull Venues, Shows and Events from a Ticketsolve XML feed and store them for easy access in your Craft website.

The AutoSync feature will keep your site in sync with Ticketsolve by updating every 15 minutes. There's also the option the sync manually with the click of a button.

A new field type called "Ticketsolve Shows" lets you relate Shows to your site's entries.

The Ticketsolve section in the Control Panel lets you browse all Venues, Shows and Events that have been imported into the site.

Twig extension allows for easy integration of Venues, Shows and Events in your templates.

NOTE: This is not an official Ticketsolve plugin.

## Requirements

This plugin requires:

- Craft CMS 3.0.0-beta.23 or later;
- SimpleXML extension for PHP;

## Installation

To install the plugin, follow these instructions.

1. Open your terminal and go to your Craft project:

        cd /path/to/project

2. Then tell Composer to load the plugin:

        composer require devkokov/craft3-ticketsolve

3. In the Control Panel, go to Settings → Plugins and click the "Install" button for Ticketsolve.

## Configuring Ticketsolve

In the Control Panel, go to Settings → Ticketsolve and enter the URL of your Ticketsolve XML feed.

Enable AutoSync or, for advanced users, create a cron job on your server. See [Syncing](#syncing).

If using AutoSync, note that the initial sync will happen 15 minutes after enabling AutoSync.

To trigger the sync manually, go in Control Panel → Ticketsolve and click the "Sync Now" button.

## Shows Field

The plugin adds a new relationship field type called "Ticketsolve Shows". It allows you to add Shows to your entries in Craft.

    {% set shows = entry.showsFieldHandle.all() %}
    
    {% for show in shows %}
        {{ show.name }}
    {% endfor %}
    
See [Shows](#shows) for a full reference on what properties are exposed for each Show.

## Venues

    {% set venues = craft().ticketsolve.venues().all() %}
    
    {% for venue in venues %}
        {{ venue.name }}
    {% endfor %}
    
#### Parameters

`craft().ticketsolve.venues()` returns a query object that supports Craft's standard query parameters for ordering, sorting, limiting, as well as the following new parameters:

- `venueRef()` - Filter results by venueRef (Ticketsolve's Venue ID). Accepts an integer.
- `name()` - Filter results by name. Accepts a string.
- `excludeVenueRefs()` - Exclude certain venueRefs from the results. Accepts an array of integers.

#### Properties

Venue elements have the following properties:

- `id` - Craft's Venue ID. Note this is different from Ticketsolve's Venue ID.
- `venueRef` - Ticketsolve's Venue ID.
- `name` - Venue's name/title.
- `shows` - Returns a Shows query object pre-filtered for this venue's Shows. See [Shows](#shows).

## Shows

    {% set shows = craft().ticketsolve.shows().all() %}
    
    {% for show in shows %}
        {{ show.name }}
    {% endfor %}

#### Parameters

`craft().ticketsolve.shows()` returns a query object that supports Craft's standard query parameters for ordering, sorting, limiting, as well as the following new parameters:

- `showRef()` - Filter results by showRef (Ticketsolve's Show ID). Accepts an integer.
- `name()` - Filter results by name. Accepts a string.
- `eventCategory()` - Filter results by event category. Accepts a string.
- `productionCompanyName()` - Filter results by production company name. Accepts a string.
- `venueId()` - Filter results by venueId. Note this is different from Ticketsolve's Venue ID. Accepts an integer.
- `excludeShowRefs()` - Exclude certain showRefs from the results. Accepts an array of integers.
- `tags()` - Filter results by certain tags. Accepts an array of strings (tag names).

#### Properties

Show elements have the following properties:

- `id` - Craft's Show ID. Note this is different from Ticketsolve's Show ID.
- `venueId` - Craft's Venue ID of the associated Venue. Note this is different from Ticketsolve's Venue ID.
- `showRef` - Ticketsolve's Show ID.
- `name` - Show's name/title.
- `description` - Show's description. Usually contains HTML markup.
- `eventCategory` - A single category name associated with this Show.
- `productionCompanyName` - Name of the production company.
- `priority` - Priority number.
- `url` - URL to Show page on Ticketsolve.
- `version` - Version number from Ticketsolve.
- `tags` - Returns an array of Tags. Each Tag has an `id` and a `name` available as properties.
- `venue` - Returns the Show's associated Venue. See [Venues](#venues).
- `events` - Returns an Events query object pre-filtered for this show's Events. See [Events](#events).
- `images` - An array of images with the following structure:

        [
            [
                'large' => 'https://exmaple.com/image1.jpg',
                'medium' => 'https://exmaple.com/image2.jpg',
                'thumb' => 'https://exmaple.com/image3.jpg'
            ],
            [
                'large' => 'https://exmaple.com/image4.jpg',
                'medium' => 'https://exmaple.com/image5.jpg',
                'thumb' => 'https://exmaple.com/image6.jpg'
            ]
        ]

## Events

    {% set events = craft().ticketsolve.events().all() %}
    
    {% for event in events %}
        {{ event.name }}
    {% endfor %}
    
#### Parameters

`craft().ticketsolve.events()` returns a query object that supports Craft's standard query parameters for ordering, sorting, limiting, as well as the following new parameters:

- `eventRef()` - Filter results by eventRef (Ticketsolve's Event ID). Accepts an integer.
- `name()` - Filter results by name. Accepts a string.
- `eventStatus()` - Filter results by event status. Accepts a string.
- `showId()` - Filter results by showId. Note this is different from Ticketsolve's Show ID. Accepts an integer.
- `excludeEventRefs()` - Exclude certain eventRefs from the results. Accepts an array of integers.

#### Properties

Event elements have the following properties:

- `id` - Craft's Event ID. Note this is different from Ticketsolve's Event ID.
- `showId` - Craft's Show ID of the associated Show. Note this is different from Ticketsolve's Show ID.
- `eventRef` - Ticketsolve's Event ID.
- `name` - Event's name/title.
- `dateTime` - DateTime object of event's date and time.
- `openingTime` - DateTime object of event's opening date/time.
- `onSaleTime` - DateTime object of event's on sale date/time.
- `duration` - Integer representing event's duration in minutes.
- `available` - Integer representing the number of available spaces.
- `capacity` - Integer representing the capacity of the event (number of spaces).
- `venueLayout` - String describing the venue layout.
- `comment` - String containing any comments.
- `url` - URL to Event page on Ticketsolve.
- `status` - Event status e.g. "available"
- `fee` - Transaction fee e.g. "0.5"
- `feeCurrency` - 3-letter currency code e.g. "GBP"
- `maximumTickets` - Integer representing the maximum number of tickets allowed per transaction.
- `show` - Returns the Event's associated Show. See [Shows](#shows).
- `venue` - Returns the Event's associated Venue. See [Venues](#venues).
- `prices` - An array of prices with the following structure:

        [
            [
                'type' => 'Standard',
                'facePrice' => [
                    'value' => 5.5,
                    'currency' => 'GBP'
                ],
                'sellingPrice' => [
                    'value' => 5,
                    'currency' => 'GBP'
                ]
            ],
            [
                'type' => 'Premium',
                'facePrice' => [
                    'value' => 8.5,
                    'currency' => 'GBP'
                ],
                'sellingPrice' => [
                    'value' => 8,
                    'currency' => 'GBP'
                ]
            ]
        ]

## Syncing

The plugin comes with an AutoSync option which will automatically sync from the XML feed every 15 minutes or so via a pseudo cron job.

For more robust syncing, you can disable Auto Sync in the plugin settings and trigger it from a proper server cron job using the following Craft console command:

    craft ticketsolve/feed/sync

---

Brought to you by [Dimitar Kokov](https://github.com/devkokov)
