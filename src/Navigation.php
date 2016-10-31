<?php

namespace Bluora\LaravelNavigationBuilder;

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
     * @return Bluora\LaravelNavigationBuilder\Menu;
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
     * @return \Bluora\LaravelNavigationBuilder\Menu
     */
    public function getMenu($key)
    {
        return $this->menu_collection->get($key);
    }

    /**
     * Return all menu instances.
     *
     * @param string $key
     *
     * @return \Bluora\LaravelNavigationBuilder\Collection
     */
    public function getMenus()
    {
        return $this->menu_collection;
    }
}
