# Google Analytics 4 / CRO App - Magento 2 Integration

---

## Plugin to help integrate Google Analytics 4 and [Cro App](https://croapp.com/) Features in Magento 2

## Installation & Configuration

### Installation

- Run these commands in the root folder of your magento installation

```
composer require croapp/integration

php bin/magento maintenance:enable
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy -f
php bin/magento maintenance:disable
php bin/magento cache:flush
```

### Configuration

#### Get Measurement ID From GA

- Login to your google analytics account
- Navigate to `admin` -> select the desired account & property
- Click on `Data Streams` in Property
- Click on data stream or create a new one
- Copy `MEASUREMENT ID`

#### Add Measurement ID In Magento

- Login to magento 2 admin panel
- Navigate to `Stores` -> `Configuration`
- From the left sidebar/menu, click on `CRO App / GA 4 Configuration` under `CRO App / GA 4`
- Enter the `MEASUREMENT ID` copied from previous step in `GA 4 Configuration Section` -> `GA-4 Measurement ID`
- Click `Save Config` on top right of the page
- Clear cache from System ⇨ Cache Management

## List of google analytics 4 events added via this plugin

- home_viewed - Triggered on homepage
- content_page_viewed - Triggered on content cms pages like About US, Contact US
- view_item_list - Triggered on category pages
- view_item - Triggered on product page
- view_search_results - Triggered on search results page
- sign_up - Triggered when customer is registered in frontend
- login - Triggered when customer logs in to account
- logout - Triggered when customer logs out from his account
- add_to_cart - Triggered when user adds a product to cart
- remove_from_cart - Triggered when user removes a product from cart
- view_cart - Triggered when user visit cart page
- add_to_wishlist - Triggered when user adds product to wishlist
- add_to_compare - Triggered when user tries to add a product to his compare list
- remove_from_compare - Triggered when user removes a product from his compare list
- begin_checkout - Triggered when user visits checkout page
- purchase - Triggered when user completes order and visits order success page

## Notes

- Some events like `add_to_cart`, `remove_from_cart` may not trigger
  - Until page is reloaded.
  - Or if some cache plugin installed in magento prevents it, then it will be triggered when user visits a dynamic page like cart page.
