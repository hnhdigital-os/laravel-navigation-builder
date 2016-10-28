<?php

namespace Bluora\LaravelNavigationBuilder;

class Item
{
    /**
     * Object reference to the menu.
     *
     * @var \Bluora\LaravelNavigationBuilder\Menu
     */
    private $menu;

    /**
     * Reference to this menu parent id.
     *
     * @var string
     */
    private $parent_id;

    /**
     * Reference to this menu parent.
     *
     * @var string
     */
    private $parent;

    /**
     * Item attributes.
     *
     * @var array
     */
    private $attributes = [];

    /**
     * Item data attributes.
     *
     * @var array
     */
    private $data = [];

    /**
     * Item options.
     *
     * @var array
     */
    private $options = [];

    /**
     * Initializing the menu item.
     *
     * @param Menu $menu
     * @param string $title
     *
     * @return void
     */
    public function __construct($menu, $title)
    {
        $this->menu = $menu;
        $item->title = $title;
        $this->id = uniqid(rand());
    }

    /**
     * Add a menu item as a child.
     */
    public function add($title)
    {
        $item = $this->addMenu($title);
        $item->parent_id = $this->id;
        $item->parent_item = $this;
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
        $current_title = array_get($this->attributes, 'title', '');
        $this->attributes['title'] = $value;
        if (array_get($this->attributes, 'nickname', '') == $current_title) {
            $this->nickname = $nickname;
        }
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
        $this->attributes['nickname'] = strtolower($value);
    }

    /**
     * Checks if the item has any children.
     *
     * @return boolean
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
     * Set an attribute.
     *
     * @return void
     */
    public function __set($attribute_name, $value)
    {
        $attribute_name = snake_case($attribute_name);

        if (stripos($attribute_name, 'data_') !== false) {
            $this->data[substr($attribute_name, 5)] = $value;
            return;
        }

        $set_attribute_method = 'set'.studly_case($attribute_name);
        if (method_exists($this, $set_attribute_method)) {
            $this->$set_attribute_method($value);
            return;
        }

        $this->attributes[$attribute_name] = $value;
    }

    /**
     * Return the value of an attribute.
     *
     * @return mixed
     */
    public function __get($attribute_name)
    {
        $attribute_name = snake_case($attribute_name);

        if (stripos($attribute_name, 'data_') !== false) {
            return array_get($this->data, substr($attribute_name, 5), '');
        }

        $get_attribute_method = 'get'.studly_case($attribute_name);
        if (method_exists($this, $get_attribute_method)) {
            $this->$get_attribute_method($value);
            return;
        }

        return array_get($this->attributes, $attribute_name, '');
    }
}
