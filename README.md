# velzon-laravel-

## Shipping and locations

Delivery charges are set in **Admin → Shipping Rates**: one default rate per country, plus optional rates for individual
cities. A customer is charged their city's rate if there is one, otherwise their country's default; with neither, we don't
deliver there and checkout is blocked.

Countries and cities in every address form, at checkout and in the admin come from the lists bundled in
`resources/data/locations` (served by `App\Services\LocationService`), so nothing is looked up from an outside service while
a customer is checking out and no city ever has to be added by hand.

The lists come from [GeoNames](https://www.geonames.org) (licensed [CC BY 4.0](https://creativecommons.org/licenses/by/4.0/)):
every country, and each country's cities of 5,000+ people. To refresh them (e.g. once a year):

```bash
php artisan locations:sync
```

A city that isn't in the list can still be typed in; it simply gets its country's default rate unless the admin has set a rate
for that exact name.
