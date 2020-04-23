# Central Billing System

[![Latest Version on Packagist](https://img.shields.io/packagist/v/infinitypaul/laravel-cbs.svg?style=flat-square)](https://packagist.org/packages/infinitypaul/laravel-cbs)
[![Build Status](https://img.shields.io/travis/infinitypaul/laravel-cbs/master.svg?style=flat-square)](https://travis-ci.org/infinitypaul/laravel-cbs)
[![Quality Score](https://img.shields.io/scrutinizer/g/infinitypaul/laravel-cbs.svg?style=flat-square)](https://scrutinizer-ci.com/g/infinitypaul/laravel-cbs)
[![Total Downloads](https://img.shields.io/packagist/dt/infinitypaul/laravel-cbs.svg?style=flat-square)](https://packagist.org/packages/infinitypaul/laravel-cbs)

A Laravel Package For Working With CBS (Central Billing System) Seamlessly

## Installation

You can install the package via composer:

```bash
composer require infinitypaul/laravel-cbs
```


> If you use **Laravel >= 5.5** you can skip this step and go to [**`configuration`**](https://github.com/infinitypaul/laravel-cbs#configuration)

* `Infinitypaul\Cbs\CbsServiceProvider::class`

Also, register the Facade like so:

```php
'aliases' => [
    ...
    'Cbs' => Infinitypaul\Cbs\Facades\Cbs::class,
    ...
]
```

## Configuration

You can publish the configuration file using this command:

```bash
php artisan vendor:publish --provider="Infinitypaul\Cbs\CbsServiceProvider"
```

A configuration-file named `cbs.php` with some sensible defaults will be placed in your `config` directory:

```php
return [
    /**
     * Client ID From CBS 
     *
     */
    'clientId' => getenv('CBS_CLIENT_ID'),

    /**
     * Secret Key From CBS 
     *
     */
    'secret' => getenv('CBS_SECRET'),

    /**
     * switch to live or test
     *
     */
    'mode' => getenv('CBS_MODE', 'test'),

    /**
     * CBS Test Payment URL
     *
     */
    'testUrl' => getenv('CBS_TEST_BASE_URL'),

    /**
     * CBS Live Payment URL
     *
     */
    'liveURL' => getenv('CBS_LIVE_BASE_URL'),


    /**
     * Revenue Head
     *
     */
    'revenueHead' => getenv('CBS_REVENUE_HEAD'),

    /**
     * Revenue Head
     *
     */
    'categoryId' => getenv('CBS_CATEGORY_ID'),
];
```



## Usage
Open your .env file and add your cbs key,  cbs secret key, cbs revenue head, category id, live url , and test url like so:

``` php
CBS_CLIENT_ID=****
CBS_SECRET=****
CBS_REVENUE_HEAD=***
CBS_CATEGORY_ID=***
CBS_LIVE_BASE_URL=***
CBS_TEST_BASE_URL=***
```
Set up routes and controller methods like so:

```php
// Laravel 5.1.17 and above
Route::post('/pay', 'InvoiceController@redirectToGateway')->name('pay'); 
```

OR

```php
Route::post('/pay', [
    'uses' => 'InvoiceController@redirectToGateway',
    'as' => 'pay'
]);

Route::post('/pay2', [
    'uses' => 'InvoiceController@getInvoice',
    'as' => 'data'
]);
```

```php
Route::get('/payment/callback', 'InvoiceController@handleGatewayCallback')->name('callback');
```

OR

```php
// Laravel 5.0
Route::get('payment/callback', [
    'uses' => 'InvoiceController@handleGatewayCallback'
]); 
```

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Cbs;

class InvoiceController extends Controller
{

    /**
     * Redirect the User to Cbs Payment Page
     * @return Url
     */
    public function redirectToGateway()
    {
        return Cbs::setInvoice()->redirectNow();
    }

    /**
     * Get The Invoice Information
     * @return array
     */
    public function getInvoice()
    {
        return Cbs::setInvoice()->getData();
    }

    /**
     * Obtain Cbs payment information
     * @return void
     */
    public function handleGatewayCallback()
    {
        $paymentDetails = Cbs::getPaymentData();

        dd($paymentDetails);
        // Now you have the payment details,
        // you can store the authorization_code in your db to allow for recurrent subscriptions
        // you can then redirect or do whatever you want
    }
}
```

Let me explain the fluent methods this package provides a bit here.

```php
/**
 *  This fluent method does all the dirty work of sending a POST request with the form data
 *  to Cbs Api, then it gets the payment Url and redirects the user to Cbs
 *  Payment Page. I abstracted all of it, so you don't have to worry about that.
 *  Just eat your cookies while coding!
 */
Cbs::setInvoice()->redirectNow();

/**
 * This fluent method does all the dirty work of verifying that the just concluded transaction was actually valid,
 */
Cbs::getPaymentData();

/**
 * This method gets the invoice information generated on Cbs
 * @returns array
 */
Cbs::setInvoice()->getData();

```


A sample form will look like so:

```html
<form method="POST" action="{{ route('pay') }}" accept-charset="UTF-8" class="form-horizontal" role="form">
        <div class="row" style="margin-bottom:40px;">
          <div class="col-md-8 col-md-offset-2">
            <p>
                <div>
                    Infinity Biscuit
                    ₦ 5,980
                </div>
            </p>
            <input type="hidden" name="email" value="infinitypaul@live"> {{-- required --}}
            <input type="hidden" name="fullname" value="Edward Paul">
<input type="hidden" name="address" value="Lagos">
<input type="hidden" name="mobile_number" value="0702323463">
<input type="hidden" name="tin" value="1234567890">
            <input type="hidden" name="amount" value="1000"> {{-- required --}}
            <input type="hidden" name="quantity" value="3">
            <input type="hidden" name="description" value="Test Buy" > 
            <input type="hidden" name="callback" value="{{ route('callback') }}"> {{-- required --}}
           
            {{ csrf_field() }} {{-- works only when using laravel 5.1, 5.2 --}}

             <input type="hidden" name="_token" value="{{ csrf_token() }}"> {{-- employ this in place of csrf_field only in laravel 5.0 --}}


            <p>
              <button class="btn btn-success btn-lg btn-block" type="submit" value="Pay Now!">
              <i class="fa fa-plus-circle fa-lg"></i> Pay Now!
              </button>
            </p>
          </div>
        </div>
</form>
```


When clicking the submit button the customer gets redirected to the Cbs site.

So now we've redirected the customer to Cbs. The customer did some actions there (hopefully he or she paid the order) and now gets redirected back to our  site.

A Request is sent to our callback url (we don't want imposters to wrongfully place non-paid order).

In the controller that handles the request coming from the payment provider, we have

`Cbs::getPaymentData()` - This function does the calculation and ensure it is a valid transction else it throws an exception.

>For A Returnee User Rather Than sending the full Name, email, mobile_number , all you need to send it the PayerId which is gotten the first time you generate an invoice

Have A Look

```html
<form method="POST" action="{{ route('pay') }}" accept-charset="UTF-8" class="form-horizontal" role="form">
        <div class="row" style="margin-bottom:40px;">
          <div class="col-md-8 col-md-offset-2">
            <p>
                <div>
                    Infinity Biscuit
                    ₦ 5,980
                </div>
            </p>
            <input type="hidden" name="PayerId" value="12343"> {{-- required --}}
        
<input type="hidden" name="tin" value="1234567890">
            <input type="hidden" name="amount" value="1000"> {{-- required --}}
            <input type="hidden" name="quantity" value="3">
            <input type="hidden" name="description" value="Test Buy" > 
            <input type="hidden" name="callback" value="{{ route('callback') }}"> {{-- required --}}
           
            {{ csrf_field() }} {{-- works only when using laravel 5.1, 5.2 --}}

             <input type="hidden" name="_token" value="{{ csrf_token() }}"> {{-- employ this in place of csrf_field only in laravel 5.0 --}}


            <p>
              <button class="btn btn-success btn-lg btn-block" type="submit" value="Pay Now!">
              <i class="fa fa-plus-circle fa-lg"></i> Pay Now!
              </button>
            </p>
          </div>
        </div>
</form>
```


### Bug & Features

If you have spotted any bugs, or would like to request additional features from the library, please file an issue via the Issue Tracker on the project's Github page: [https://github.com/infinitypaul/laravel-database-filter/issues](https://github.com/infinitypaul/laravel-cbs/issues).

## How can I thank you?

Why not star the github repo? I'd love the attention! Why not share the link for this repository on Twitter or HackerNews? Spread the word!

Don't forget to [follow me on twitter](https://twitter.com/infinitypaul)!

Thanks!
Edward Paul.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

