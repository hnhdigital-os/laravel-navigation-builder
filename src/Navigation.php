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
     * @param string  $name
     * @param Closure $allocation_callback
     *
     * @return HnhDigital\NavigationBuilder\Menu;
     */
    public function createMenu($name, \Closure $allocation_callback = null)
    {
        // Create menu.
        $menu = new Menu($name);

        // Store the menu object.
        $this->menu_collection->put($name, $menu);

        // Make available in all views.
        \view::share($name, $menu);

        // Allocate menu items, if provided.
        if (is_callable($allocation_callback)) {
            $allocation_callback($menu);
        }

        return $menu;
    }

    /**
     * Return menu instance by it's key.
     *
     * @param string $key
     *
     * @return \HnhDigital\NavigationBuilder\Menu
     */
    public function getMenu($key)
    {
        return $this->menu_collection->get($key);
    }

    /**
     * Alias for getMenu.
     *
     * @param string $key
     *
     * @return \HnhDigital\NavigationBuilder\Menu
     */
    public function get($key)
    {
        return $this->getMenu($key);
    }

    /**
     * Alias for getMenu.
     *
     * @param string $key
     *
     * @return \HnhDigital\NavigationBuilder\Menu
     */
    public function menu($key)
    {
        return $this->getMenu($key);
    }

    /**
     * Check if there are navigation items.
     *
     * @param string $key
     *
     * @return \HnhDigital\NavigationBuilder\Menu
     */
    public function has($key)
    {
        $menu = $this->getMenu($key);

        return !is_null($menu) && $menu->count() > 0;
    }

    /**
     * Return all menu instances.
     *
     * @param string $key
     *
     * @return \HnhDigital\NavigationBuilder\Collection
     */
    public function getMenus()
    {
        return $this->menu_collection;
    }
}
