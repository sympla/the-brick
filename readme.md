# sympla/the-brick

> We all wish we had that wise neighborhood bartender to give us advice over a few rounds.

This library helps to negotiate content related to eloquent models (fields, relations and filters)

## Installation

Install the package using composer:

    $ composer require sympla/the-brick

##### Service Provider (Optional on Laravel 5.5)
Once Composer has installed or updated your packages you need add aliases or register you packages into Laravel. 

Open up config/app.php and find the `providers` key and add:

```
Sympla\Search\Search\SearchServiceProvider::class,
```


Open up config/app.php and find the `aliases` key and add:

```
'Search' => Sympla\Search\Facades\SearchFacade::class,
```

That's it.

## Usage


## Using with Laravel


## Contact

Bruno Coelho <bruno.coelho@sympla.com.br>

Marcus Campos <marcus.campos@sympla.com.br>

## License

This project is distributed under the MIT License. Check [LICENSE][LICENSE.md] for more information.
