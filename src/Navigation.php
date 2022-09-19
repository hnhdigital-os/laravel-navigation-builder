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

/**
 * This is the navigation class.
 *
 * @author Rocco Howard <rocco@hnh.digital>
 */
class Navigation
{
    /**
     * Menu collection.
     *
     * @var Illuminate\Support\Collection
     */
    private $menu_collection;

    /**
     * Initializing the navigation builder.
     *
     * @return void
     */
    public function __construct()
    {
        $this->menu_collection = new Collection();
    }

    /**
     * Create new menu.
     *
     * @param  string  $name
     * @param  Closure  $allocation_callback
     * @return Menu
     */
    public function createMenu($name, \Closure $allocation_callback = null)
    {
        // Create menu.
        if (empty($menu = $this->get($name))) {
            $menu = new Menu($name);
        }

        // Store the menu object.
        $this->menu_collection->put($name, $menu);

        // Make available in all views.
        app('view')->share($name, $menu);

        // Allocate menu items, if provided.
        if (is_callable($allocation_callback)) {
            $allocation_callback($menu);
        }

        return $menu;
    }

    public function addToMenu($name, $menu_items)
    {
        // Create menu.
        if (empty($menu = $this->get($name))) {
            $menu = $this->createMenu($name);
        }

        // Store the menu object.
        foreach ($menu_items->all() as $item) {
            $menu->addItem($item);
        }

        return $menu;
    }

    /**
     * Return menu instance by it's key.
     *
     * @param  string|array  $key
     * @return Menu
     */
    public function getMenu($key)
    {
        if (is_array($key)) {
            $menu_items = $key;
        } else {
            $menu_items = explode(',', $key);
        }

        // Comma-deliminated string requested.
        if (count($menu_items) > 1 || is_array($key)) {
            $result = collect();

            foreach ($menu_items as $key) {
                $menu = $this->menu_collection->get($key);

                if (is_null($menu)) {
                    continue;
                }

                foreach ($menu->all() as $item) {
                    $result->push($item);
                }
            }

            // Create new menu and return.
            return new Menu($key, $result);
        }

        return $this->menu_collection->get($key);
    }

    /**
     * Return menu instance by it's key.
     *
     * @param  string  $key
     * @return Item
     */
    public function getMenuItem($key)
    {
        [$menu, $item] = explode('.', $key);

        $menu = $this->menu_collection->get($menu);

        if (is_null($menu)) {
            throw new \Exception('Menu can not be found.');
        }

        $item = $menu->get($item);

        if (is_null($item)) {
            throw new \Exception('Item can not be found.');
        }

        return $item;
    }

    /**
     * Alias for getMenu.
     *
     * @param  string  $key
     * @return Menu
     */
    public function get($key)
    {
        return $this->getMenu($key);
    }

    /**
     * Alias for getMenu.
     *
     * @param  string  $key
     * @return Menu
     */
    public function menu($key)
    {
        return $this->getMenu($key);
    }

    /**
     * Check if there are navigation items.
     *
     * @param  string  $key
     * @return Menu
     */
    public function has($key)
    {
        $menu = $this->getMenu($key);

        return ! is_null($menu) && $menu->count() > 0;
    }

    /**
     * Return all menu instances.
     *
     * @param  string  $key
     * @return Collection
     */
    public function getMenus()
    {
        return $this->menu_collection;
    }
}
