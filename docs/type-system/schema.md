# Schema
## Schema Definition

The schema is the contract of your type system, which accepts types in a constructor and provides
methods for receiving information about your types to internal Sage tools.

In **Sage.php**, schema is an instance of [`Sage\Type\Schema`](../reference.md#Sage-Type-Schema) 
which accepts configuration array in its constructor**:**

```php
<?php
use Sage\Type\Schema;

$schema = new Schema([
	'types' => [
    'Todo' => $Todo
  ]
]);
```
See possible constructor options [below](#configuration-options).

The schema is simply a collection of the Entity types you define and want to expose through your API.

```php
<?php
use Sage\Type\Definition\Entity;
use Sage\Type\Definition\Type;

$Todo = new Entity([
  'name' => 'Todo',
  'attributes' => [
    'title' => new Attribute([
      'type' => Type::string(),
      'resolve' => function($referenceValue, ContextInfo $info) {
        $id = $referenceValue['todo.id'];
        return DataSource::getTodoById($id);
      }
    ])
  ]
]);
```

Keep in mind that other than the special meaning of declaring a surface area of your API,
those types are the same as any other [Entity](entity.md), and their fields work exactly the same way.

## Configuration Options
Schema constructor expects an array with following options:

Option       | Type     | Notes
------------ | -------- | -----
types     | `Entity[]` | List of your schema's Entity types. 
typeLoader     | `callable` | **function (** *$name* **)** <br>Expected to return type instance given the name. Must always return the same instance if called multiple times. See section below on lazy type loading. 


## Lazy Loading of Types
By default, the schema will scan all of your type system definitions to serve Sage queries.
It may cause performance overhead when there are many types in the schema.

In this case, it is recommended to pass `typeLoader` option to schema constructor and define all 
of your object **fields** as callbacks.

Type loading concept is very similar to PHP class loading, but keep in mind that `typeLoader` must
always return the same instance of a type.

Usage example**:**
```php
<?php
use Sage\Type\Definition\ObjectType;
use Sage\Type\Schema;

class Types
{
  private $types = [];

  public function get($name)
  {
    if (!isset($this->types[$name])) {
      $this->types[$name] = $this->{$name}();
    }
    return $this->types[$name];
  }

  private function ToDo()
  {
    return new Entity([
      'name' => 'ToDo',
      'attributes' => [
        'title' => new Attribute([
          'name' => 'title',
          'type' => Type::string(),
          'resolve' => function() {

          }
        ])
      ]
    ]);
  }
}

$typeRegistry = new Types();

$schema = new Schema([
  'typeLoader' => function($name) use ($typeRegistry) {
    return $typeRegistry->get($name);
  }
]);
```


## Schema Validation
By default, the schema is created with only shallow validation of entity type and artifact definitions 
(because validation requires full schema scan and is very costly on bigger schemas).

But there is a special method **assertValid()** on schema instance which throws `Sage\Error\InvariantViolation` exception when it encounters any schema error.

Schema validation is supposed to be used in the build step of your app.
Don't call it in web requests in production.

Usage example **:**
```php
<?php
try {
    $schema = new Sage\Type\Schema([
        'MyType' => $MyType
    ]);
    $schema->assertValid();
} catch (Sage\Error\InvariantViolation $e) {
    echo $e->getMessage();
}
```
