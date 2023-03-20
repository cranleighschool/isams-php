# spkm/isams
[![Latest Version on Packagist](https://img.shields.io/packagist/v/spkm/isams.svg?style=flat-square)](https://packagist.org/packages/spkm/isams)
[![Build Status](https://img.shields.io/travis/spkm/isams/master.svg?style=flat-square)](https://travis-ci.org/spkm/isams)
![StyleCI Status](https://github.styleci.io/repos/144165171/shield)
[![Total Downloads](https://img.shields.io/packagist/dt/spkm/isams.svg?style=flat-square)](https://packagist.org/packages/spkm/isams)

![Banner](https://banners.beyondco.de/ISAMS%20PHP.png?theme=light&packageName=spkm%2Fisams&pattern=charlieBrown&style=style_1&description=A+Laravel+wrapper+for+the+ISAMS+REST+API&md=1&showWatermark=0&fontSize=175px&images=code)

This package is a PHP wrapper for iSAMS (https://isams.com) using their REST API. It transforms the json 
objects returned from the API into model instances.

See https://developerdemo.isams.cloud/Main/swagger/ui/index for their API documentation & https://developer.isams.com/display/PRA/Getting+started+-+REST+API 

## Installation and usage
This package requires PHP 8.1 & Laravel 9.0 or higher. See the `tests/` folder for documentation. (We'd quite like someone to write some proper documentation) 

### Basic Installation:
You can install this package via composer using:
```
composer require spkm/isams
```

The package will automatically register its service provider

To publish the config file to `config/isams.php` run:
```
php artisan vendor:publish --provider="spkm\isams\IsamsServiceProvider"
```

Update the config file & add the REST API secret(s) to your .env file

If you are using a School model, implement the interface `\spkm\isams\Contracts\Institution`:
```php
<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class School extends Model implements \spkm\isams\Contracts\Institution
{
    /**
     * Define the name used to identify this Schools entry in the config
     */
    public function getConfigName(): string
    {
        return 'cranleighSandbox';
    }
}


``` 
Otherwise implement the interface on your custom class (or copy the example `spkm\isams\School`)

### Testing

``` bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email spkm@cranleigh.org instead of using the issue tracker.

## Credits

- [Simon Mitchell](https://github.com/spkm)
- [Fred Bradley](https://github.com/fredbradley)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
  

