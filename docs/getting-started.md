# Getting Started

## Prerequisites

This documentation assumes your familiarity with Sage concepts. If it is not the case - 
first learn about Sage on [the website](http://Sage.org/learn/).


## Hello World
Let's create a type system that will be capable to process following simple query:

```json
{
  "typ": "Person",
  "atr": ["name"],
  "arg": {
    "id": 1
  }
}
```

To do so we need an Entity type `Person` with the attribute `name` **:**

```php
<?php
use Sage\Type\Definition\Entity;
use Sage\Type\Definition\Type;

$Person = new Entity([
  'attributes' => [
    'name' => $name,
  ]
  'resolver' => function ($) {
		$id = $referenceValue['id'];
  	$person = DataSource::getPersonById($id);
		return $person->name;
	} 
]);

# Define the attribute 'name'
# new Attribute(callable $resolver, array $constraints)
$name = new Attribute(
  function ($referenceValue) {
		$id = $referenceValue['id'];
  	$person = DataSource::getPersonById($id);
		return $person->name;
	},
  [
    'type'        => Type::string(),
    'description' => "Name of a person."
    'nonNull'     => true
  ]
);



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
