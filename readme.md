This package is a PHP wrapper for iSAMS (https://isams.com) using their REST API. It transforms the json 
objects returned from the API into model instances

See https://developerdemo.isams.cloud/Main/swagger/ui/index for their API documentation & https://developer.isams.com/display/PRA/Getting+started+-+REST+API 

## Installation and usage
This package requires PHP 7 & Laravel 5.5 or higher. See the tests folder for documentation. 

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
    public function getConfigName()
    {
        return 'cranleighSandbox';
    }
}


``` 
Otherwise implement the interface on your custom class (or copy the example `spkm\isams\School`)

  