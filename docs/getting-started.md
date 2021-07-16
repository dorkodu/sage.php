# Prerequisites
This documentation assumes your familiarity with Sage concepts. If it is not the case - 
first learn about Sage on [the official website](http://Sage.org/learn/).

# Installation

Using [composer](https://getcomposer.org/doc/00-intro.md), run:

```sh
composer require webonyx/Sage-php
```

# Upgrading
We try to keep library releases backwards compatible. But when breaking changes are inevitable 
they are explained in [upgrade instructions](https://github.com/webonyx/Sage-php/blob/master/UPGRADE.md).

# Install Tools (optional)
While it is possible to communicate with Sage API using regular HTTP tools it is way 
more convenient for humans to use [GraphiQL](https://github.com/Sage/graphiql) - an in-browser 
IDE for exploring Sage APIs.

It provides syntax-highlighting, auto-completion and auto-generated documentation for 
Sage API.

The easiest way to use it is to install one of the existing Google Chrome extensions:

 - [ChromeiQL](https://chrome.google.com/webstore/detail/chromeiql/fkkiamalmpiidkljmicmjfbieiclmeij)
 - [GraphiQL Feen](https://chrome.google.com/webstore/detail/graphiql-feen/mcbfdonlkfpbfdpimkjilhdneikhfklp)

Alternatively, you can follow instructions on [the GraphiQL](https://github.com/Sage/graphiql)
page and install it locally.


# Hello World
Let's create a type system that will be capable to process following simple query:
```
query {
  echo(message: "Hello World")
}
```

To do so we need an object type with field `echo`:

```php
<?php
use Sage\Type\Definition\ObjectType;
use Sage\Type\Definition\Type;

$queryType = new ObjectType([
    'name' => 'Query',
    'fields' => [
        'echo' => [
            'type' => Type::string(),
            'args' => [
                'message' => Type::nonNull(Type::string()),
            ],
            'resolve' => function ($rootValue, $args) {
                return $rootValue['prefix'] . $args['message'];
            }
        ],
    ],
]);

```

(Note: type definition can be expressed in [different styles](type-system/index.md#type-definition-styles), 
but this example uses **inline** style for simplicity)

The interesting piece here is **resolve** option of field definition. It is responsible for returning 
a value of our field. Values of **scalar** fields will be directly included in response while values of 
**composite** fields (objects, interfaces, unions) will be passed down to nested field resolvers 
(not in this example though).

Now when our type is ready, let's create Sage endpoint file for it **Sage.php**:

```php
<?php
use Sage\Sage;
use Sage\Type\Schema;

$schema = new Schema([
    'query' => $queryType
]);

$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput, true);
$query = $input['query'];
$variableValues = isset($input['variables']) ? $input['variables'] : null;

try {
    $rootValue = ['prefix' => 'You said: '];
    $result = Sage::executeQuery($schema, $query, $rootValue, null, $variableValues);
    $output = $result->toArray();
} catch (\Exception $e) {
    $output = [
        'errors' => [
            [
                'message' => $e->getMessage()
            ]
        ]
    ];
}
header('Content-Type: application/json');
echo json_encode($output);
```

Our example is finished. Try it by running:
```sh
php -S localhost:8080 Sage.php
curl http://localhost:8080 -d '{"query": "query { echo(message: \"Hello World\") }" }'
```

Check out the full [source code](https://github.com/webonyx/Sage-php/blob/master/examples/00-hello-world) of this example
which also includes simple mutation.

Obviously hello world only scratches the surface of what is possible. 
So check out next example, which is closer to real-world apps.
Or keep reading about [schema definition](type-system/index.md).

# Blog example
It is often easier to start with a full-featured example and then get back to documentation
for your own work. 

Check out [Blog example of Sage API](https://github.com/webonyx/Sage-php/tree/master/examples/01-blog).
It is quite close to real-world Sage hierarchies. Follow instructions and try it yourself in ~10 minutes.
