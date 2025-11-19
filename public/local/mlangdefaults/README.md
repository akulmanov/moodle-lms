# Multi-language Defaults Plugin

A Moodle plugin that automatically inserts multi-language placeholders using `{mlang}` tags into course, section, and activity fields when creating new content.

## Features

- **Automatic insertion** of multi-language placeholders on creation pages
- **Global defaults** configurable by administrators
- **Per-activity-type overrides** for different module types
- **Per-category overrides** for courses in specific categories
- **Per-course overrides** for individual courses
- **Custom field mappings** via admin UI
- **Integration** with TinyMCE/Atto editors
- **Compatibility** with filter_multilang2
- **Diagnostics page** for monitoring and troubleshooting

## Installation

1. Copy the plugin to `local/mlangdefaults`
2. Visit Site administration > Notifications to complete installation
3. Configure the plugin at Site administration > Plugins > Local plugins > Multi-language Defaults

## Configuration

### Global Settings

- **Enable plugin**: Turn the plugin on/off
- **Languages**: Comma-separated list of language codes (e.g., kk,ru,en)
- **Fallback language**: Language code to use as fallback (default: ru)
- **Creation pages only**: Only inject on creation pages, never on edit pages
- **Skip if {mlang} already present**: Don't inject if field already contains {mlang} tags
- **Show notification after insertion**: Display a toast notification when defaults are inserted

### Templates

Configure default templates for:
- Course fullname
- Course summary
- Section name
- Section summary
- Activity name
- Activity intro

### Field Mappings

Manage which fields on which pages receive multi-language defaults. Built-in mappings are provided for common Moodle pages, and custom mappings can be added.

### Course Overrides

Course administrators can override site defaults for individual courses via the course settings page.

## Usage

When creating a new course, section, or activity, if the relevant fields are empty, the plugin will automatically insert multi-language placeholders in the format:

```
{mlang kk}{mlang}{mlang ru}Default text{mlang}{mlang en}{mlang}
```

Authors can then edit each language block to provide translations.

## Requirements

- Moodle 4.0 or higher
- filter_multilang2 plugin (recommended for proper display)

## License

GPL v3 or later

