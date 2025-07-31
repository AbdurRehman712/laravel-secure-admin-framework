# CMS Themes

This directory contains themes for the CMS similar to OctoberCMS structure.

## Theme Structure

Each theme is structured as follows:

- **assets/** - CSS, JavaScript, images, and other assets
- **blueprints/** - Component and page blueprints
- **content/** - Static content files
  - **ajax/** - AJAX content partials
- **layouts/** - Layout template files (.htm)
- **pages/** - Page template files (.htm)
- **partials/** - Partial template files (.htm)
- **seeds/** - Seed content data

## Theme Configuration

Each theme contains:

- **theme.yaml** - Theme metadata and configuration
- **version.yaml** - (optional) Theme version information
- **webpack.config.js** - (optional) Webpack configuration
- **package.json** - (optional) NPM package configuration

## Theme Conventions

### File Extensions

- `.htm` - HTML template files with theme syntax
- `.yaml` or `.yml` - Configuration files
- `.css` - Stylesheet files
- `.js` - JavaScript files

### Template Syntax

Templates use a syntax similar to OctoberCMS/Twig:

```
title = "Page Title"
url = "/page-url"
layout = "default"
description = "Page description"
==
<div class="content">
    <h1>{{ variable }}</h1>
    {% partial 'partial-name' %}
</div>
```

## Available Themes

1. **demo** - Default demonstration theme
2. **rapidesoftware-tailwind-theme** - Modern theme using Tailwind CSS
