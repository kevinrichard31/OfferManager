# Magento 2 Offer Manager Module

A Magento 2 marketing module for managing promotional offers with advanced features including category assignment, redirect_url image management, and date-based scheduling.

## Features

- **Offer Management**: Create, edit, and delete promotional offers
- **Image Support**: Upload images or select from media gallery with automatic path handling
- **Category Integration**: Display multiple offers simultaneously, each with its own description, image, and link
- **Date Scheduling**: Set start and end dates for offers
- **Admin Grid**: View all offers in a user-friendly admin grid
- **Multilingual Support**: Translation support (French included)
- **Unit Tests**: Comprehensive PHPUnit test coverage

## Requirements

- PHP : ~8.2.0 || ~8.3.0
- Magento : 2.7.4
- MariaDB : 10.4 → 10.6

## Installation

### Via Composer (Recommended)

```bash
composer require kevinrichard34/magento2-offer-manager
php bin/magento module:enable Dnd_OfferManager
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy
php bin/magento cache:flush
```

### Manual Installation

1. Download the module
2. Extract to `app/code/Dnd/OfferManager`
3. Run the installation commands above

## Usage

### Admin Panel

1. Navigate to **Marketing > Promotions > Offer Manager**
2. Click "Add New Offer" to create a new offer
3. Fill in the required fields:
   - **Label**: Display name for the offer
   - **Description**: Brief description (max 155 characters)
   - **Image**: Upload or select from media gallery
   - **Redirect URL**: Target URL when offer is clicked
   - **Start/End Date**: Schedule the offer period
   - **Categories**: Select applicable product categories

## Configuration

The module creates the following database table:
- `dnd_offer_manager_offer`: Stores offer information

## File Structure

```
Dnd/OfferManager/
├── Block/
│   └── Adminhtml/
├── Controller/
│   └── Adminhtml/
├── Model/
│   ├── Offer.php
│   ├── ResourceModel/
│   └── Source/
├── Test/
│   └── Unit/
├── Ui/
│   └── Component/
├── view/
│   └── adminhtml/
├── etc/
│   ├── module.xml
│   ├── db_schema.xml
│   └── adminhtml/
├── i18n/
│   └── fr_FR.csv
└── registration.php
```

## Testing

Run the unit tests:

```bash
php vendor/bin/phpunit app/code/Dnd/OfferManager/Test/Unit
```

## License

This project is licensed under proprietary license

## Support

- **Issues**: [GitHub Issues](https://github.com/kevinrichard31/OfferManager/issues)
- **Documentation**: [GitHub Wiki](https://github.com/kevinrichard31/OfferManager/wiki)

## Changelog

### Version 1.0.0
- Initial release
- Basic offer management functionality
- Admin grid list offer
- Category integration
- Media gallery support
- Unit test coverage
- French translations

## Author

**Kevin Richard**
- GitHub: [@kevinrichard31](https://github.com/kevinrichard31)

---
