# SM - Sameday Lockers Locator for WooCommerce

[![Licence](https://img.shields.io/badge/LICENSE-GPL2.0+-blue)](./LICENSE)

- **Developed by:** Martin Nestorov  
    - Explore more at [nestorov.dev](https://github.com/mnestorov)
- **Plugin URI:** https://github.com/mnestorov/smarty-sameday-lockers-locator

## Overview

The Sameday Lockers Locator plugin integrates seamlessly with [Sameday](https://sameday.ro/), a courier service, to manage and display Sameday locker locations on WooCommerce checkout pages.

## Description

The Sameday Lockers Locator plugin equips WordPress sites, particularly WooCommerce-powered e-commerce platforms, with the functionality to fetch and display current Sameday locker locations. This plugin facilitates efficient shipping and logistics management by ensuring access to updated locker details.

## Features

- **Dynamic Locker Updates:** Regularly synchronizes locker information from Sameday's database both automatically and manually.
- **Manual Update Trigger:** Admins can manually trigger a locker information update via the plugin settings page.
- **Automated Weekly Updates:** The plugin automatically updates locker information on a configurable schedule.
- **Flexible Shortcode Integration:** Embed the locker locator anywhere using [sameday_lockers] shortcode.
- **Admin Settings Page:** Tailor the plugin's behavior and locker display via a dedicated settings page.
- **Country Code Filtering:** Configure the plugin to display lockers based on specific countries.
- **Enhanced Performance:** Leverages WordPress transients for efficient caching and faster data retrieval.
- **Comprehensive Error Logging:** Detailed logs for troubleshooting and updates monitoring.
- **Select2 Integration:** Improved UI for locker selection with Select2 dropdowns.

## Installation

1. **Upload Plugin:** Download the plugin ZIP file and upload it via Plugins > Add New > Upload Plugin in WordPress.
2. **Activate Plugin:** Activate the plugin in the 'Plugins' menu.
3. **Automatic Table Creation:** A database table for Sameday lockers is automatically created upon activation.
4. **Configuration:** Optionally, configure the plugin settings via its settings page.

## Usage

- Use `[sameday_lockers]` shortcode to display the locker locator.
- Adjust shortcode attributes for country or city-based locker filtering.
- Configure locker display settings through the admin settings page.
- Manually update locker data as needed via the settings page.
- Set up an automatic update schedule to keep locker data current.

## Frequently Asked Questions

**Q:** How often does the plugin update locker data?  
**A:** Locker data is updated weekly by default, but you can adjust this in the settings. Manual updates are also available.

**Q:** Can I display lockers from a specific country?  
**A:** Yes, configure this via the shortcode or admin settings.

**Q:** Is the locker selection process user-friendly for large lists?  
**A:** Yes, the plugin uses Select2 for a smoother selection experience.

## Translation

This plugin is translation-ready. Add translations to the `languages` directory.

## Changelog

For a detailed list of changes and updates made to this project, please refer to our [Changelog](./CHANGELOG.md).

---

## License

This project is released under the [GPL-2.0+ License](http://www.gnu.org/licenses/gpl-2.0.txt).
