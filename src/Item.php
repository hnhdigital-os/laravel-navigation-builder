<?php

namespace Bluora\LaravelNavigationBuilder;

use Bluora\LaravelHtmlGenerator\Html;
use Illuminate\Support\Str;

class Item
{
    /**
     * No link specified.
     *
     * @var string
     */
    const LINK_EMPTY = 'empty';

    /**
     * Link action.
     *
     * @var string
     */
    const LINK_ACTION = 'action';

    /**
     * Link route.
     *
     * @var string
     */
    const LINK_ROUTE = 'route';

    /**
     * Link url.
     *
     * @var string
     */
    const LINK_URL = 'url';

    /**
     * Link url.
     *
     * @var string
     */
    const LINK_INSECURE_URL = 'insecure_url';

    /**
     * Link external url.
     *
     * @var string
     */
    const LINK_EXTERNAL_URL = 'external_url';

    /**
     * Link type (default used is empty).
     *
     * @var string
     */
    private $link_type = self::LINK_EMPTY;

    /**
     * Object reference to the menu.
     *
     * @var \Bluora\LaravelNavigationBuilder\Menu
     */
    private $menu;

    /**
     * Item data.
     *
     * - title
     * - nickname
     * - active
     *
     * @var array
     */
    private $data = [];

    /**
     * Item options.
     *
     * - open_new_window (setOptionOpenNewWindow)
     * - hide_if_not_active (setOptionHideIfNotActive)
     * - show_in_breadcrumb_if_active (setOptionShowInBreadcrumbIfActive)
     *
     * @var array
     */
    private $option = [];

    /**
     * Item attributes.
     */
    private $item_attribute = [];

    /**
     * Link attributes.
     */
    private $link_attribute = [];

    /**
     * Initializing the menu item.
     *
     * @param Menu   $menu
     * @param string $title
     *
     * @return void
     */
    public function __construct($menu, $title)
    {
        $this->menu = $menu;
        $this->title = $title;
        $this->id = uniqid(rand());
    }

    /**
     * Add a menu item as a child.
     *
     * @param string $title
     */
    public function add($title)
    {
        $item = $this->menu->addItem($title);
        $item->parent_id = $this->id;
        $item->parent = $this;

        return $item;
    }

    /**
     * Checks if the item has any children.
     *
     * @return bool
     */
    public function hasChildren()
    {
        return count($this->menu->whereParent($this->id)) or false;
    }

    /**
     * Returns children of the item.
     *
     * @param bool $depth
     *
     * @return \Bluora\LaravelNavigationBuilder\Collection
     */
    public function children($depth = false)
    {
        return $this->menu->whereParent($this->id, $depth);
    }

    /**
     * Modify an attribute on the item.
     * Alias for addItemAttribute($name, $value).
     *
     * @param string $name
     * @param string $value
     * @param string $action
     *
     * @return Item
     */
    public function item($name, $value, $action = 'add')
    {
        $method_name = $action.ucfirst($name).'Attribute';
        $item->$method_name($value);

        return $this;
    }

    /**
     * Modify an attribute on the link.
     * Alias for addLinkAttribute($name, $value).
     *
     * @param string $name
     * @param string $value
     * @param string $action
     *
     * @return Item
     */
    public function link($name, $value, $action = 'add')
    {
        $method_name = $action.ucfirst($name).'Attribute';
        $item->$method_name($value);

        return $this;
    }

    /**
     * Set the item to be a action.
     *
     * @param string $route_name
     * @param array  $parameters
     *
     * @return void
     */
    public function action($name, ...$parameters)
    {
        $this->link_type = self::LINK_ACTION;
        $this->link_value = [$name, $parameters];

        return $this;
    }

    /**
     * Set the item to be a route.
     *
     * @param string $name
     * @param array  $parameters
     *
     * @return void
     */
    public function route($name, ...$parameters)
    {
        $this->link_type = self::LINK_ROUTE;
        $this->link_value = [$name, $parameters];

        return $this;
    }

    /**
     * Set the item to be a url.
     *
     * @param string $url
     * @param array  $parameters
     *
     * @return void
     */
    public function url($url, ...$parameters)
    {
        $this->link_type = self::LINK_URL;
        $this->link_value = [$url, $parameters];

        return $this;
    }

    /**
     * Set the item to be a insecure url.
     *
     * @param string $url
     * @param array  $parameters
     *
     * @return void
     */
    public function insecureUrl($url, ...$parameters)
    {
        $this->link_type = self::LINK_INSECURE_URL;
        $this->link_value = [$url, $parameters];

        return $this;
    }

    /**
     * Set the item be an external url.
     *
     * @param string $url
     *
     * @return void
     */
    public function externalUrl($url)
    {
        $this->link_type = self::LINK_EXTERNAL_URL;
        $this->link_value = [$url];
        $this->setOptionOpenNewWindow();

        return $this;
    }

    /**
     * Set the title.
     *
     * @param string $value
     *
     * @return void
     */
    public function setTitle($value)
    {
        $current_title = array_get($this->data, 'title', '');
        $this->data['title'] = $value;
        if (array_get($this->data, 'nickname', '') == $current_title) {
            $this->nickname = $value;
        }

        return $this;
    }

    /**
     * Set the nickname.
     *
     * @param string $value
     *
     * @return void
     */
    public function setNickanme($value)
    {
        $this->data['nickname'] = strtolower(Str::ascii($value));

        return $this;
    }

    /**
     * Get the nickname.
     *
     * @param string $value
     *
     * @return void
     */
    public function getNickname()
    {
        return strtolower(Str::ascii($this->data['nickname']));
    }

    /**
     * Set this item active.
     *
     * @param bool $active
     */
    public function setActive($active = true)
    {
        $this->data['active'] = $active;
        $this->addLinkAttribute('class', 'active');

        // Activate parents.
        if (!is_null($this->parent)) {
            $this->parent->active = $active;
        }

        return $this;
    }

    /**
     * Render this item.
     *
     * @return string
     */
    public function render($text_only = false)
    {
        // Standard tag, or if option setTag used, use that.
        $item_tag = array_get($this->option, 'tag', 'li');

        $html = ($text_only) ? $this->html : $this->title;
        if ($this->link_type !== self::LINK_EMPTY) {
            // Create the link.
            $html = Html::a($html)->addAttributes($this->link_attribute);
            $html->openNew(!$this->getOptionOpenNewWindow());

            // Create the URL.
            switch ($this->link_type) {
                case self::LINK_ACTION:
                    $html->actionHref(...$this->link_value);
                    break;
                case self::LINK_ROUTE:
                    $html->routeHref(...$this->link_value);
                    break;
                case self::LINK_URL:
                    $html->href(secure_url(...$this->link_value));
                    break;
                case self::LINK_INSECURE_URL:
                    $html->href(url(...$this->link_value));
                    break;
                case self::LINK_EXTERNAL_URL:
                    $url = stripos($this->link_value[0], 'http') === false ? 'http://' : '';
                    $url .= $this->link_value[0];
                    $html->href($url);
                    break;
            }
        }

        // Create the container and allocate the link.
        return Html::$item_tag($html)->addAttributes($this->item_attribute)->s();
    }

    /**
     * Check if this item has the given property.
     *
     * @param string $property_name
     *
     * @return bool
     */
    public function __isset($property_name)
    {
        return isset($this->data[$property_name]);
    }

    /**
     * Set or get calls.
     *
     * @param string $method_name
     * @param array  $arguments
     *
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        $method_name = snake_case($name);
        list($action, $method_name, $key) = array_pad(explode('_', $method_name, 3), 3, '');

        // Get calls.
        if ($action == 'get' || $action == 'set') {
            $array_func = 'array_'.$action;
            if (($method_name == 'item' || $method_name == 'link') && $key == 'attribute') {
                $result = $array_func($this->{$method_name.'_'.$key}, $arguments[0], array_get($arguments, 1, null));

                return $action == 'get' ? $result : $this;
            }

            if ($method_name == 'option') {
                $default = $action == 'get' ? false : true;
                $result = $array_func($this->option, $key, array_get($arguments, 0, $default));

                return $action == 'get' ? $result : $this;
            }

            $name = $method_name;
        }

        // Manipulate values.
        if (($action == 'add' || $action == 'remove' || $action == 'append' || $action == 'prepend')
            && ($method_name == 'item' || $method_name == 'link') && $key == 'attribute') {

            $input_value = array_get($arguments, 1, '');
            $current_value = array_get($this->{$method_name.'_'.$key}, $arguments[0], '');
            $whitespace = ($arguments[0] == 'class') ? ' ' : '';

            if ($arguments[0] == 'class'|| $action == 'remove') {
                $current_value = str_replace($input_value, '', $current_value);
            }

            switch ($action) {
                case 'add':
                case 'append':
                    $current_value .= $whitespace.$input_value;
                    break;
                case 'prepend':
                    $current_value = $input_value.$whitespace.$current_value;
                    break;
            }

            array_set($this->{$method_name.'_'.$key}, $arguments[0], trim($current_value));

            return $this;
        }

        // Use the magic get/set instead
        if (count($arguments) == 0) {
            return $this->$name;
        }

        $this->$name = array_get($arguments, 0, '');

        return $this;
    }

    /**
     * Set a data value by a name.
     *
     * @param string $name
     * @param string $value
     *
     * @return void
     */
    public function __set($name, $value)
    {
        $name = snake_case($name);
        $set_method = 'set'.studly_case($name);
        if (method_exists($this, $set_method)) {
            $this->$set_method($value);

            return;
        }

        $this->data[$name] = $value;
    }

    /**
     * Return the value of data by name.
     *
     * @param string $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        $name = snake_case($name);
        $get_method = 'get'.studly_case($name);
        if (method_exists($this, $get_method)) {
            return $this->$get_method();
        }

        return array_get($this->data, $name, '');
    }
}
