<?php

namespace Bluora\LaravelNavigationBuilder;

use Bluora\LaravelHtmlGenerator\Html;

class Menu
{
    /**
     * Item collection.
     *
     * @var Illuminate\Support\Collection
     */
    private $item_collection;

    /**
     * Menu data.
     *
     * @var array
     */
    private $data = [];

    /**
     * Menu options.
     *
     * @var array
     */
    private $option = [];

    /**
     * Menu attributes.
     *
     * @var array
     */
    private $attribute = [];

    /**
     * Initializing the menu.
     *
     * @return void
     */
    public function __construct($name)
    {
        $this->name = $name;
        $this->item_collection = new Collection();
    }

    /**
     * Add item to the menu.
     *
     * @param string $title
     *
     * @return Bluora\LaravelNavigationBuilder\Item
     */
    public function addItem($title)
    {
        $item = new Item($this, $title);
        $this->item_collection->push($item);

        return $item;
    }

    /**
     * Alias for addItem.
     *
     * @param string $title
     *
     * @return Bluora\LaravelNavigationBuilder\Item
     */
    public function add($title)
    {
        return $this->addItem($title);
    }

    /**
     * Filter items by property.
     *
     * @param string $property_name
     * @param string $value
     * @param bool   $include_children
     *
     * @return \Bluora\LaravelNavigationBuilder\Collection
     */
    public function filter($property_name, $value, $include_children = false)
    {
        // Result collection.
        $filter_result = new Collection();

        $this->item_collection->filter(function ($item) use ($property_name, $value, $include_children, &$filter_result) {

            if (!isset($item->$property_name)) {
                return false;
            }

            if ($item->$property_name == $value) {
                $filter_result->push($item);

                // Check if item has any children
                if ($include_children && $item->hasChildren()) {
                    $filter_result = $filter_result->merge($this->filter('parent_id', $item->id, $include_children));
                }
            }

            return false;
        });

        return $filter_result;
    }

    /**
     * Return the first item in the collection.
     *
     * @return \Bluora\LaravelNavigationBuilder\Item
     */
    public function firstItem()
    {
        return $this->item_collection->first();
    }

    /**
     * Alias for firstItem.
     *
     * @return \Bluora\LaravelNavigationBuilder\Item
     */
    public function first()
    {
        return $this->firstItem();
    }

    /**
     * Return the last item in the collection.
     *
     * @return \Bluora\LaravelNavigationBuilder\Item
     */
    public function lastItem()
    {
        return $this->item_collection->last();
    }

    /**
     * Alias for lastItem.
     *
     * @return \Bluora\LaravelNavigationBuilder\Item
     */
    public function last()
    {
        return $this->lastItem();
    }

    /**
     * Return the last item in the collection.
     *
     * @return \Bluora\LaravelNavigationBuilder\Item
     */
    public function get($nickname)
    {
        return $this->getByNickname($nickname);
    }

    /**
     * Render this menu and it's children.
     *
     * @param  string $tag
     *
     * @return string
     */
    public function render($parent_id = false)
    {
        // Standard tag, or if option setTag used, use that.
        $menu_tag = array_get($this->option, 'tag', 'ul');
        $item_tag = array_get($this->option, 'itemTag', 'li');
        $html = '';

        $items = $this->item_collection;

        // Render from a specific menu item.
        if ($parent_id !== false) {
            $items = $this->whereParentId($parent_id);
        }

        // Generate each of the items.
        foreach ($items as $item) {
            $item->setOptionItemTag($item_tag);
            $html .= $item->render();
        }

        // Create the container and allocate the link.
        return Html::$menu_tag($html)->addAttributes($this->attribute)->s();
    }

    /**
     * Search the menu based on a given attribute.
     *
     * @param string $method_name
     * @param array  $arguments
     *
     * @return \Bluora\LaravelNavigationBuilder\Collection|\Bluora\LaravelNavigationBuilder\Item
     */
    public function __call($name, $arguments)
    {
        // $this->whereTitle(...)
        preg_match('/^[W|w]here([a-zA-Z0-9_]+)$/', $name, $where_matches);

        if (count($where_matches) > 0) {
            $property_name = snake_case($where_matches[1]);
            $property_name = (stripos($property_name, 'data_') !== false) ? str_replace('_', '-', $property_name) : $property_name;

            return $this->filter($property_name, ...$arguments);
        }

        // $this->getByTitle(...)
        preg_match('/^[G|g]et[B|b]y([a-zA-Z0-9_]+)$/', $name, $get_by_matches);

        if (count($get_by_matches) > 0) {
            $property_name = snake_case($get_by_matches[1]);
            $property_name = (stripos($property_name, 'data_') !== false) ? str_replace('_', '-', $property_name) : $property_name;
            $result = $this->filter($property_name, ...$arguments);

            return (count($result)) ? $result->first() : null;
        }

        $method_name = snake_case($name);
        list($action, $method_name, $key) = array_pad(explode('_', $method_name, 3), 3, '');

        // $this->getAttribute('class') | $this->setAttribute('class', '')
        if ($action == 'get' || $action == 'set') {
            $array_func = 'array_'.$action;
            
            if ($method_name == 'attribute') {
                $result = $array_func($this->$method_name, $arguments[0], array_get($arguments, 1, null));
                return $action == 'get' ? $result : $this;
            }
            
            if ($method_name == 'option') {
                $result = $array_func($this->option, $key, array_get($arguments, 0, ''));
                return $action == 'get' ? $result : $this;
            }
        }

        // $this->addAttribute('class', '') | $this->removeAttribute('class')
        // || $this->appendAttribute('class', '') | $this->prependAttribute('class', '')
        if ($method_name == 'attribute'
            && ($action == 'add' || $action == 'remove' || $action == 'append' || $action == 'prepend')) {
            
            $input_value = array_get($arguments, 1, '');
            $current_value = array_get($this->$method_name, $arguments[0], '');
            $whitespace = (strlen(trim($current_value)) > 0 && $arguments[0] == 'class') ? ' ' : '';

            if ($arguments[0] == 'class' || $action == 'remove') {
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

            array_set($this->$method_name, $arguments[0], trim($current_value));

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
        return array_get($this->data, $name, '');
    }
}
