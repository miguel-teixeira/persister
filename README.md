# Persister

A package to facilitate and speed up multiple inserts or updates to DB in Laravel.

# Usage

1- Add the persister service provider to your **config/app.php** providers list.

    Persister\PersisterServiceProvider::class
    
2- Add the persister facade to your **config/app.php** facades list.

    'Persister' => Persister\Facades\Persister::class,

3- Have fun!

# Example

```php

    Persister::insertOrUpdate(new Record(
        'posts',    // table name
        'id',       // key column
        1,          // key value
        [           // data
            'id' => 1
            'text' => 'foo'
        ]
    ));
```
    
    