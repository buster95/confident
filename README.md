# Confident

[![Latest Stable Version](https://poser.pugx.org/confident/confident/v/stable)](https://packagist.org/packages/confident/confident)
[![Latest Unstable Version](https://poser.pugx.org/confident/confident/v/unstable)](https://packagist.org/packages/confident/confident)
[![Total Downloads](https://poser.pugx.org/confident/confident/downloads)](https://packagist.org/packages/confident/confident)
[![License](https://poser.pugx.org/confident/confident/license)](https://packagist.org/packages/confident/confident)

Confident es un framework para crear API Rest con sencillez

## Instalación

Se recomienda usar [Composer](https://getcomposer.org/) para instalar confident.

```bash
$ composer require confident/confident "^1.0"
```

## Uso

Crear un archivo index.php con el siguiente contenido:

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

The Confident is licensed under the MIT license. See [License File](LICENSE.md) for more information.