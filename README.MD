# IPA Currency Converter
Drupal 9 || 10 module for convertation currencies  

## Table of content
- [Install](#install)
- [Usage](#usage)
- [License](#license)

## Install

1. Create `ipa_currency_convert` folder in `modules\custom\` and put this module there.
2. Install module. Currency entity will be created.
3. Follow `Configuration -> System -> Currency Converter` section.
4. Add currencies. Select one as base.
5. Follow Settings page and set api key for [freecurrencyapi.com](https://freecurrencyapi.com)
6. Update rates
7. Setup cron using ultimate cron module

## Usage

```php
\\ ...

use Drupal\ipa_currency_converter\Controller\CurrencyConverterController

\\ ...

$value = CurrencyConverterController::convert(5, 'EUR', 'USD');

\\ ...
```

## License

The IPA Currency Converter module is licensed under the [GPL-3.0](/LICENSE).