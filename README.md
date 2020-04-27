# Confident

[![Latest Stable Version](https://poser.pugx.org/confident/confident/v/stable)](https://packagist.org/packages/confident/confident)
[![Latest Unstable Version](https://poser.pugx.org/confident/confident/v/unstable)](https://packagist.org/packages/confident/confident)
[![Total Downloads](https://poser.pugx.org/confident/confident/downloads)](https://packagist.org/packages/confident/confident)
[![License](https://poser.pugx.org/confident/confident/license)](https://packagist.org/packages/confident/confident)

**Confident** is a framework that helps you to easily create your own RESTful API

## Installation

We recommend to install **Confident** using [Composer](https://getcomposer.org/).

```bash
$ composer require confident/confident "^1.0"
```

## Usage

This example shows how easy it is to set up **Confident** in your project. <br>
Try it yourself by pasting the code below into your index.php.

```php
<?php

require 'vendor/autoload.php';

$app = new Confident\ApiController();

$app->get('/hello/{name}', function ($name) {
    echo "hello " . $name;
});

$app->start();
```

## License

**Confident** is licensed under the MIT license. See [License File](LICENSE.md) for more information.
