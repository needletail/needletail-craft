# Needletail plugin for Craft CMS

Needletail Search and Index plugin for Craft CMS.

## Requirements

This plugin requires **Craft CMS 5.x**.

## Installation

To install the plugin, follow these instructions.

1. Open your terminal and go to your Craft project:

        cd /path/to/project

2. Then tell Composer to load the plugin:

        composer require needletail/needletail-craft

3. In the Control Panel, go to Settings → Plugins and click the “Install” button for Needletail.

Brought to you by [Needletail](https://needletail.io)

## Buckets

Buckets define **what gets indexed** into Needletail.

- You can create buckets for a specific element type (Entries, Categories, Assets, etc.).
- You can also create a bucket with element type **All URL resources**, which is designed to index **everything with a URL** in your system (including Assets), and optionally additional element types added by other plugins that support URIs.

### All URL resources bucket

When you select **All URL resources**, Needletail will index **publicly visible** elements only:

- Elements must have a **non-empty URL**
- Elements must be **public** (e.g. Live/Enabled)

## Custom Twig file

You can create a custom twig file to render the results. To do this, create a new file in your templates folder called `_needletail/[[BUCKET_NAME]].twig`. This file will be used to render the search results. You can use the following variables in this file:

- `entry` - The record that will be rendered

### Example:
```json
{
   "title": "{{ entry.title }}",
   "slug": "{{ entry.slug }}",
   "url": "{{ entry.getUrl() }}",
   "date": "{{ entry.postDate|date('Y-m-d') }}",
   "author": "{{ entry.author }}"
}
```

### Testing:

For easy testing you can create a new twig file in your templates folder `[[BUCKET_NAME]].json.twig` and add the following code:

```twig
{% set entry = craft.entries({id: [[ENTRY_ID]]}).one() %}

{% include "_needletail/[[BUCKET_NAME]].twig" %}
```

You can then call `$PRIMARY_SITE_URL/[[BUCKET_NAME]].json` in your browser to see the rendered result.

> **Note!** Make sure to replace `[[BUCKET_NAME]]` and `[[ENTRY_ID]]` with the correct values.  
