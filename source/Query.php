<?php

namespace Sage;

class Query
{
  /**
   * Entity Type - 'typ'
   * @var string
   */
  private $type;

  /**
   * Attributes - 'atr' 
   * @var string[]
   */
  private $attributes;

  /**
   * Act name - 'act'
   * @var string
   */
  private $act;

  /**
   * Links - 'lnk'
   * @var string[]
   */
  private $links;

  /**
   * 
   * Arguments - 'arg'
   * @var string[]
   */
  private $arguments;

  /**
   * @param string $type
   * @param string[] $attributes
   * @param string $act
   * @param array $arguments
   * @param array $links
   */
  public function __construct(
    string $type,
    array $attributes,
    string $act,
    array $arguments,
    array $links
  ) {
    $this->type = $type;
    $this->attributes = $attributes;
    $this->act = $act;
    $this->arguments = $arguments;
    $this->links = $links;
  }

  /**
   * Returns the Entity type.
   *
   * @return string
   */
  public function type()
  {
    return $this->type;
  }

  /**
   * Returns the act name.
   *
   * @return string
   */
  public function act()
  {
    return $this->act;
  }

  /**
   * Returns all attributes.
   *
   * @return string[]
   */
  public function attributes()
  {
    return $this->attributes;
  }

  /**
   * Returns if has attribute
   *
   * @param string $name
   * @return boolean
   */
  public function hasAttribute(string $name)
  {
    return in_array($name, $this->attributes);
  }

  /**
   * Returns the argument with the given name.
   *
   * @param string $name
   * 
   * @return mixed The argument value.
   * @return null If has no argument with the given name.
   */
  public function argument(string $name)
  {
    return array_key_exists($name, $this->arguments)
      ? $this->arguments[$name]
      : null;
  }

  /**
   * Returns if has argument.
   *
   * @param string $name
   * @return boolean
   */
  public function hasArgument(string $name)
  {
    return array_key_exists($name, $this->arguments);
  }


  /**
   * Returns all arguments.
   *
   * @return array
   */
  public function arguments()
  {
    return $this->arguments;
  }

  /**
   * Returns the attributes of the link with the given name.
   *
   * @param string $name
   * 
   * @return array The attributes array of the link.
   * @return null If has no link with the given name.
   */
  public function link(string $name)
  {
    return array_key_exists($name, $this->links)
      ? $this->links[$name]
      : null;
  }

  /**
   * Returns if has link.
   *
   * @param string $name
   * @return boolean
   */
  public function hasLink(string $name)
  {
    return array_key_exists($name, $this->links);
  }

  /**
   * Returns all links.
   *
   * @return array
   */
  public function links()
  {
    return $this->links;
  }
}
