
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
        $this->collection = new Collection();
    }

    /**
     * Create new menu.
     *
     * @param string  $name
     * @param Closure $item_allocation_callback
     *
     * @return Bluora\LaravelNavigationBuilder\Menu;
     */
    public function createMenu(string $name, \Closure $item_allocation_callback = null)
    {
        // Create menu.
        $menu = new Menu($name);

        // Store the menu object.
        $this->menu_collection->put($name, $menu);

        // Make available in all views.
        \View::share($name, $menu);

        // Allocate menu items, if provided.
        if (is_callable($item_allocation_callback)) {
            $item_allocation_callback($menu);
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
