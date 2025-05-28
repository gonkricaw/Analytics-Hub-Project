# Asset Management System Documentation

## Overview

This document provides instructions for using the asset management tools implemented to prevent and detect asset-related issues in the Analytics Hub project.

## 1. Dynamic Asset Loading

The application now uses a dynamic asset loading approach that reads from the Vite manifest file instead of hardcoding asset paths. This prevents 404 errors when assets are rebuilt with new hashes.

### How It Works

- The `AssetManager` helper class (`app/Helpers/AssetManager.php`) provides methods to dynamically fetch asset paths from the manifest
- Routes in `web.php` use this helper to redirect legacy asset paths to the current ones
- No hardcoded asset hashes means we're always referencing the current assets

### Usage

To get asset paths in your code:

```php
// Get the main JS asset path
$jsPath = \App\Helpers\AssetManager::getMainJs();

// Get the main CSS asset path
$cssPath = \App\Helpers\AssetManager::getMainCss();

// Get a specific asset path
$customAsset = \App\Helpers\AssetManager::getAssetPath('resources/js/custom.js');
```

## 2. Post-Build Asset Validation

The `validate-assets.php` script checks that all assets referenced in the manifest actually exist on disk.

### Usage

Run after every build to catch issues early:

```bash
# Run validation manually
php validate-assets.php

# Or use the automated script
.\post-build-check.bat
```

### Integration with Build Process

Add this to your build workflow by updating the build script in `package.json`:

```json
"scripts": {
  "build": "vite build && php validate-assets.php",
  "build:check": "vite build && post-build-check.bat"
}
```

## 3. 404 Error Monitoring

The `monitor-asset-404s.php` script analyzes log files for 404 errors related to assets.

### Usage

Run periodically or after deploying to check for asset-related issues:

```bash
php monitor-asset-404s.php
```

### Configuring Alerts

Edit the configuration section in `monitor-asset-404s.php` to:

1. Set email alerts (`send_email_alerts` = true)
2. Configure recipients
3. Adjust error thresholds

## Best Practices

1. **Never hardcode asset hashes** - Always use the dynamic loading approach
2. **Run validation after builds** - Make it part of your deployment checklist
3. **Monitor logs regularly** - Set up a scheduled task to run `monitor-asset-404s.php`
4. **Update assets in tandem** - When updating JS, ensure CSS is also rebuilt

## Troubleshooting

If asset issues occur:

1. Check the manifest file (`public/build/manifest.json`)
2. Verify assets exist on disk in the specified location
3. Run `php validate-assets.php` to identify issues
4. Check logs for 404 errors with `php monitor-asset-404s.php`
5. Rebuild the assets if necessary
6. Clear browser caches

## Further Improvements

Consider implementing:

1. Automated CI/CD checks for asset validation
2. Real-time monitoring for 404 errors
3. Version tracking of assets between deployments
