<?php

namespace Bluora\LaravelNavigationBuilder;

class Menu
{
    /**
     * The menu name.
     *
     * @var string
     */
    private $name;

    /**
     * Item collection.
     *
     * @var Illuminate\Support\Collection
     */
    private $item_collection;

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
    public function addMenu($title)
    {
        $item = new Item($this, $title);
        $this->item_collection->push($item);

        return $item;
    }

    /**
     * Alias for addMenu.
     *
     * @param string $title
     *
     * @return Bluora\LaravelNavigationBuilder\Item
     */
    public function add($title)
    {
        return $this->addMenu($title);
    }

    /**
     * Filter items by attribute.
     *
     * @param string $attribute_name
     * @param string $value
     * @param bool   $include_children
     *
     * @return \Bluora\LaravelNavigationBuilder\Collection
     */
    public function filter($attribute_name, $value, $include_children = false)
    {
        // Result collection.
        $filter_result = new Collection();


        $this->item_collection->each(function ($item) use ($attribute_name, $value, &$filter_result) {
            if (!$item->hasProperty($attribute_name)) {
                return false;
            }

            if ($item->$attribute_name == $value) {
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
     * Search the menu based on a given attribute.
     *
     * @param string $method_name
     * @param array  $arguments
     *
     * @return \Bluora\LaravelNavigationBuilder\Collection|\Bluora\LaravelNavigationBuilder\Item
     */
    public function __call($method_name, $arguments)
    {
        // $this->whereTitle(...)
        preg_match('/^[W|w]here([a-zA-Z0-9_]+)$/', $method_name, $where_matches);

        if (count($where_matches) > 0) {
            $attribute_name = snake_case($where_matches[1]);
            $attribute_name = (stripos($attribute_name, 'data_') !== false) ? str_replace('_', '-', $attribute_name) : $attribute_name;

            return $this->filter($attribute_name, ...$arguments);
        }

        // $this->getByTitle(...)
        preg_match('/^[G|g]etBy([a-zA-Z0-9_]+)$/', $method_name, $get_by_matches);

        if (count($get_by_matches) > 0) {
            $attribute_name = snake_case($get_by_matches[1]);
            $attribute_name = (stripos($attribute_name, 'data_') !== false) ? str_replace('_', '-', $attribute_name) : $attribute_name;
            $result = $this->filter($attribute_name, ...$arguments);

            return (count($result)) ? $result->first() : null;
        }
    }
}
