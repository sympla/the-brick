# sympla/the-brick

> We all wish we had that wise neighborhood bartender to give us advice over a few rounds.

This library helps to negotiate content related to eloquent models (fields, relations and filters)

## Installation

Install the package using composer:

    $ composer require sympla/the-brick ~1.0

Publish the package configuration:
        $ php artisan vendor:publish --provider="Sympla\Search\Search\SearchServiceProvider"

That's it.

## Simple usage


```php
public function index(Request $request)
{
    $res = $res->negotiate('Models\User');
    return response()->json($res);
}
```

Extend the negotiate on your model

```php
namespace App\Models;

use Sympla\Search\Model\Model;

class User extends Model
{
}
```

Create your filter

```php
public function scopeFilterByPhone($query)
{
   return $query->where('phone', '<>', '');
}
```

Now you simply call your route with your filter and the fields you want to return in the request

```
http://localhost:8000/api/users?&fields=name,email&filters=filterByPhone
```
## Parameter list
##### fields (string)
List of fields and relationships for the search: id,name,relationName(id,email)

##### filters (string)
List of scopes that will be called in your model: filterByName
```php
public function scopeFilterByName($query)
{
   return $query->where('name', Request::get('name'));
}
```
##### orderBy (string)
Query sort field

##### sort (string)
Sort ASC or DESC

##### limit (int)
Query records Limit

##### noPaginate (bool)
Indicates whether the query will be paged

##### size (int)
Indicates how many records per page

##### debug (bool)
Returns an array with all the sql's that the query generated

## Using with Laravel

### Service Provider (Optional on Laravel 5.5)
Once Composer has installed or updated your packages you need add aliases or register you packages into Laravel. Open up config/app.php and find the aliases key and add:

```
Sympla\Search\Search\SearchServiceProvider::class,
```

## Generating documentation

#### Docblock variables

* @negotiate : Informs which model the deal is using
* @negotiateDesc : Description of method/filter

### How use

Add to your docblock the documentation variables

#### Controller
```php
/**
 * @negotiate Models\User
 * @negotiateDesc Get and filter all users
*/
public function index(Request $request)
{
    $res = $res->negotiate('Models\User');
    return response()->json($res);
}
```

#### Model
```php
/**
 * @negotiateDesc Get users with phones
 */
public function scopeFilterByPhone($query)
{
   return $query->where('phone', '<>', '');
}
```

#### Generate the documentation

Execute this command

```bash
php artisan negotiate:docgen
```

#### Accessing the Documentation

Access the documentation through the url `http://localhost:8000/_negotiate/documentation`

## Contact

Bruno Coelho <bruno.coelho@sympla.com.br>

Marcus Campos <marcus.campos@sympla.com.br>

## License

This project is distributed under the MIT License. Check [LICENSE][LICENSE.md] for more information.
