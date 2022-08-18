<?php

/*
 * This file is part of Laravel Navigation Builder.
 *
 * (c) Rocco Howard <rocco@hnh.digital>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HnhDigital\NavigationBuilder;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * This is the menu class.
 *
 * @author Rocco Howard <rocco@hnh.digital>
 */
class Menu
{
    /**
     * Item collection.
     *
     * @var Illuminate\Support\Collection
     */
    private $item_collection;

    /**
     * Original menu.
     *
     * @var Illuminate\Support\Collection
     */
    private $original_menu;

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
    public function __construct($name, $collections = false)
    {
        $this->name = $name;
        $this->item_collection = ($collections !== false) ? $collections : new Collection();
    }

    /**
     * Add item to the menu.
     *
     * @param string $title
     *
     * @return Item
     */
    public function addItem($title)
    {
        if (is_object($title) && $title instanceof Item) {
            $item = $title;
        } else {
            $item = new Item($this, $title);
        }

        $this->item_collection->push($item);

        return $item;
    }

    /**
     * Alias for addItem.
     *
     * @param string $title
     *
     * @return Item
     */
    public function add($title = '')
    {
        return $this->addItem($title);
    }

    /**
     * Return all menu items.
     *
     * @return Item
     */
    public function allItems()
    {
        return $this->item_collection;
    }

    /**
     * Alias for allItems.
     *
     * @return Item
     */
    public function all()
    {
        return $this->allItems();
    }

    public function count()
    {
        return $this->allItems()->count();
    }

    /**
     * Check menu's are all active.
     *
     * @return Menu
     */
    public function checkActive()
    {
        foreach ($this->item_collection as $item) {
            $item->checkItemIsActive($item);
        }

        return $this;
    }

    /**
     * Filter items by property.
     *
     * @param string $property_name
     * @param string $value
     * @param bool   $include_children
     *
     * @return \HnhDigital\NavigationBuilder\Collection
     */
    public function filter($property_name, $value, $include_children = false)
    {
        // Result collection.
        $filter_result = new Collection();

        $this->item_collection->filter(function ($item) use ($property_name, $value, $include_children, &$filter_result) {
            if (! isset($item->$property_name)) {
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
     * @return Item
     */
    public function firstItem()
    {
        return $this->item_collection->first();
    }

    /**
     * Alias for firstItem.
     *
     * @return Item
     */
    public function first()
    {
        return $this->firstItem();
    }

    /**
     * Return the last item in the collection.
     *
     * @return Item
     */
    public function lastItem()
    {
        return $this->item_collection->last();
    }

    /**
     * Alias for lastItem.
     *
     * @return Item
     */
    public function last()
    {
        return $this->lastItem();
    }

    /**
     * Get attribute by name.
     *
     * @param string $name
     *
     * @return string
     */
    public function getAttribute($name, $default = null)
    {
        return Arr::get($this->attribute, $name, $default);
    }

    /**
     * Get attribute by name.
     *
     * @param string $name
     *
     * @return string
     */
    public function getAttributes()
    {
        return $this->attribute;
    }

    /**
     * Set attribute by name.
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return Item
     */
    public function setAttribute($name, ...$value)
    {
        $value = is_array(Arr::get($value, 0, '')) ? Arr::get($value, 0) : $value;
        $this->updateAttribute($name, $value, $this->getAttributeValueSeparator($name));

        return $this;
    }

    /**
     * Add attribute by name.
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return Item
     */
    public function addAttribute($name, ...$value)
    {
        $value = is_array(Arr::get($value, 0, '')) ? Arr::get($value, 0) : $value;
        [$current_value, $separator] = $this->manipulateAttribute($name, $value);

        foreach ($value as $attribute_value) {
            $current_value[] = $attribute_value;
        }

        $this->updateAttribute($name, $current_value, $separator);

        return $this;
    }

    /**
     * Remove attribute by name.
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return Item
     */
    public function removeAttribute($name, $value)
    {
        [$current_value, $separator] = $this->manipulateAttribute($name, $value);

        $this->updateAttribute($name, $current_value, $separator);

        return $this;
    }

    /**
     * Append to attribute by name.
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return Item
     */
    public function appendAttribute($name, ...$value)
    {
        [$current_value, $separator] = $this->manipulateAttribute($name, $value);

        foreach ($value as $attribute_value) {
            $current_value[] = $attribute_value;
        }

        $this->updateAttribute($name, $current_value, $separator);

        return $this;
    }

    /**
     * Prepend to attribute by name.
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return Item
     */
    public function prependAttribute($name, ...$value)
    {
        [$current_value, $separator] = $this->manipulateAttribute($name, $value);

        foreach ($value as $attribute_value) {
            array_unshift($current_value, $attribute_value);
        }

        $this->updateAttribute($name, $current_value, $separator);

        return $this;
    }

    /**
     * Clean attribute value before required change.
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return array
     */
    private function manipulateAttribute($name, $value)
    {
        $current_value = Arr::get($this->attribute, $name, '');
        $separator = (strlen(trim($current_value)) > 0) ? $this->getAttributeValueSeparator($name) : '';
        $current_value = $separator !== '' ? explode($separator, $current_value) : [$current_value];

        if ($name == 'class' || $name == 'style') {
            $value = is_array($value) ? $value : [$value];
            $current_value = array_diff($current_value, $value);
        }

        return [$current_value, $separator];
    }

    /**
     * Update attribute by name.
     *
     * @param string $name
     * @param array  $value
     *
     * @return Item
     */
    private function updateAttribute($name, $value, $separator)
    {
        Arr::set($this->attribute, $name, implode($separator, $value));

        return $this;
    }

    /**
     * Get the attribute value seperator.
     *
     * @param stirng $name
     *
     * @return string
     */
    private function getAttributeValueSeparator($name)
    {
        switch ($name) {
            case 'class':
                return ' ';
            case 'style':
                return ';';
        }

        return '';
    }

    /**
     * Store the original menu.
     *
     * @return \HnhDigital\NavigationBuilder\Menu
     */
    public function setOriginal($original_menu)
    {
        $this->original_menu = $original_menu;

        return $this;
    }

    /**
     * Reset the menu.
     *
     * @return \HnhDigital\NavigationBuilder\Menu
     */
    public function getOriginal()
    {
        return $this->original_menu;
    }

    /**
     * Set a class name to the class attribute.
     *
     * Alias for setAttribute.
     *
     * @param string ...$value
     *
     * @return Item
     */
    public function setClass(...$value)
    {
        return $this->setAttribute('class', $value);
    }

    /**
     * Add a class name to the class attribute.
     *
     * Alias for addAttribute.
     *
     * @param string $value
     *
     * @return Item
     */
    public function addClass(...$value)
    {
        return $this->addAttribute('class', $value);
    }

    /**
     * Remove a class name from the class attribute.
     *
     * Alias for removeAttribute.
     *
     * @param string $value
     *
     * @return Item
     */
    public function removeClass(...$value)
    {
        return $this->removeAttribute('class', $value);
    }

    /**
     * Set a style to the style attribute.
     *
     * Alias for setAttribute.
     *
     * @param string ...$value
     *
     * @return Item
     */
    public function setStyle(...$value)
    {
        return $this->setAttribute('style', $value);
    }

    /**
     * Add a style to the style attribute.
     *
     * Alias for addAttribute.
     *
     * @param string $value
     *
     * @return Item
     */
    public function addStyle(...$value)
    {
        return $this->addAttribute('style', $value);
    }

    /**
     * Remove a style from the style attribute.
     *
     * Alias for removeAttribute.
     *
     * @param string $value
     *
     * @return Item
     */
    public function removeStyle(...$value)
    {
        return $this->removeAttribute('style', $value);
    }

    /**
     * Return the last item in the collection.
     *
     * @return Item
     */
    public function get($nickname)
    {
        return $this->getByNickname($nickname);
    }

    /**
     * Render this menu and it's children.
     *
     * @param string $tag
     *
     * @return string
     */
    public function render($parent_id = false)
    {
        // Available options for this menu.
        $container_tag = Arr::get($this->option, 'tag', 'ul');
        $item_tag = Arr::get($this->option, 'item_tag', 'li');
        $item_callback = Arr::get($this->option, 'item_callback', null);
        $text_only = Arr::get($this->option, 'text_only', false);
        $html = '';

        $items = $this->item_collection;

        // Render from a specific menu item.
        if ($parent_id !== false) {
            $items = $this->whereParentId($parent_id)->all();
        }

        // Generate each of the items.
        foreach ($items as $item) {
            $item->setItemTagOption($item_tag)
                ->setContainerTagOption($container_tag);

            if (! is_null($item_callback) && is_callable($item_callback)) {
                $item_callback($item);
                $item->setItemCallbackOption($item_callback);
            }
            $html .= $item->render(2, $text_only);
        }

        // Create the container and allocate the link.
        return $html;
    }

    /**
     * Search the menu based on a given attribute.
     *
     * @param string $method_name
     * @param array  $arguments
     *
     * @return \HnhDigital\NavigationBuilder\Collection|Item
     */
    public function __call($name, $arguments)
    {
        // $this->whereTitle(...)
        preg_match('/^[W|w]here([a-zA-Z0-9_]+)$/', $name, $matches);

        if (count($matches) > 0) {
            $property_name = Str::snake($matches[1]);
            $property_name = (stripos($property_name, 'data_') !== false) ? str_replace('_', '-', $property_name) : $property_name;

            $menu = new self($this->name.'_filtered', $this->filter($property_name, ...$arguments));
            $menu->setOriginal($this);

            return $menu;
        }

        // $this->getByTitle(...)
        preg_match('/^[G|g]et[B|b]y([a-zA-Z0-9_]+)$/', $name, $matches);

        if (count($matches) > 0) {
            $property_name = Str::snake($matches[1]);

            $property_name = (stripos($property_name, 'data_') !== false) ? str_replace('_', '-', $property_name) : $property_name;
            $result = $this->filter($property_name, ...$arguments);

            return (count($result)) ? $result->first() : null;
        }

        $original_method_name = Str::snake($name);
        preg_match('/^([a-z]+)_([a-z_]+)_([a-z]+)$/', $original_method_name, $matches);

        if (count($matches) === 4) {
            [$original_method_name, $action, $key, $method_name] = $matches;

            // $this->get[...]Option(), $this->set[...]Option()
            if ($action == 'get' || $action == 'set') {
                $array_func = $action;
                if ($method_name == 'option') {
                    $result = Arr::$array_func($this->option, $key, Arr::get($arguments, 0, ''));

                    return $action == 'get' ? $result : $this;
                }
            }
        }

        // Use the magic get/set instead
        if (count($arguments) == 0) {
            return $this->$name;
        }

        $this->$name = Arr::get($arguments, 0, '');

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
        $name = Str::snake($name);
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
        $name = Str::snake($name);

        return Arr::get($this->data, $name, '');
    }
}
