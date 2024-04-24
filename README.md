# Needletail plugin for Craft CMS

Needletail Search and Index package for Craft 3.x

## Requirements

This plugin requires Craft CMS 3 or 4

## Installation

To install the plugin, follow these instructions.

1. Open your terminal and go to your Craft project:

        cd /path/to/project

2. Then tell Composer to load the plugin:

        composer require needletail/needletail-craft

3. In the Control Panel, go to Settings → Plugins and click the “Install” button for Needletail.
4. 
Brought to you by [Needletail](https://needletail.io)

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
