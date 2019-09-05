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
use HnhDigital\LaravelHtmlGenerator\Html;
use Bluora\PhpNumberConverter\NumberConverter;

/**
 * This is the item class.
 *
 * @author Rocco Howard <rocco@hnh.digital>
 */
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
     * Unique ID for this item.
     *
     * @var string
     */
    public $id = '';

    /**
     * Object reference to the parent.
     *
     * @var \HnhDigital\NavigationBuilder\Item
     */
    public $parent;

    /**
     * Reference to the parent.
     *
     * @var string
     */
    public $parent_id = '';

    /**
     * Item has authorization.
     *
     * @var string
     */
    public $authorized = true;

    /**
     * Object reference to the menu.
     *
     * @var \HnhDigital\NavigationBuilder\Menu
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
     * - open_new_window (setOpenNewWindowOption)
     * - hide_if_not_active (setHideIfNotActiveOption)
     * - hide_if_active (setHideIfActiveOption)
     * - show_in_breadcrumb_if_active (setShowInBreadcrumbIfActiveOption)
     *
     * @var array
     */
    private $option = [];

    /**
     * Child container attributes.
     *
     * @var array
     */
    private $container_attribute = [];

    /**
     * Item attributes.
     *
     * @var array
     */
    private $item_attribute = [];

    /**
     * Link attributes.
     *
     * @var array
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
     *
     * @return HnhDigital\NavigationBuilder\Item
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
        return count($this->menu->whereParentId($this->id)->all()) or false;
    }

    /**
     * Returns children of the item.
     *
     * @param bool $depth
     *
     * @return \HnhDigital\NavigationBuilder\Collection
     */
    public function children($depth = false)
    {
        return $this->menu->whereParentId($this->id, $depth)->all();
    }

    /**
     * Returns the parent of this item.
     *
     * @return HnhDigital\NavigationBuilder\Item
     */
    public function parent()
    {
        return $this->parent;
    }

    /**
     * Modify an attribute on the item.
     * Alias for addItemAttribute($name, $value).
     *
     * @param string $name
     * @param string $value
     * @param string $action
     *
     * @return HnhDigital\NavigationBuilder\Item
     */
    public function item($name, $value, $action = 'add')
    {
        $method_name = $action.'ItemAttribute';
        $this->$method_name($name, $value);

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
     * @return HnhDigital\NavigationBuilder\Item
     */
    public function link($name, $value, $action = 'add')
    {
        $method_name = $action.'LinkAttribute';
        $this->$method_name($name, $value);

        return $this;
    }

    /**
     * Set the item to be a action.
     *
     * @param string $route_name
     * @param array  $parameters
     *
     * @return HnhDigital\NavigationBuilder\Item
     */
    public function action($name, ...$parameters)
    {
        $this->link_type = self::LINK_ACTION;
        $this->link_value = [$name, $parameters];
        $this->checkActive();

        return $this;
    }

    /**
     * Set the item to be a route.
     *
     * @param string $name
     * @param array  $parameters
     *
     * @return HnhDigital\NavigationBuilder\Item
     */
    public function route($name, $parameters = [])
    {
        $this->link_type = self::LINK_ROUTE;
        $this->link_value = [$name, $parameters];
        $this->checkActive();

        return $this;
    }

    /**
     * Set the item to be a url.
     *
     * @param string $url
     * @param array  $parameters
     *
     * @return HnhDigital\NavigationBuilder\Item
     */
    public function url($url, ...$parameters)
    {
        $this->link_type = self::LINK_URL;
        $this->link_value = [$url, $parameters];
        $this->checkActive();

        return $this;
    }

    /**
     * Set the item to be a insecure url.
     *
     * @param string $url
     * @param array  $parameters
     *
     * @return HnhDigital\NavigationBuilder\Item
     */
    public function insecureUrl($url, ...$parameters)
    {
        $this->link_type = self::LINK_INSECURE_URL;
        $this->link_value = [$url, $parameters];
        $this->checkActive();

        return $this;
    }

    /**
     * Set the item be an external url.
     *
     * @param string $url
     *
     * @return HnhDigital\NavigationBuilder\Item
     */
    public function externalUrl($url)
    {
        $this->link_type = self::LINK_EXTERNAL_URL;
        $this->link_value = [$url];
        $this->setOpenNewWindowOption();
        $this->setActive(false);

        return $this;
    }

    /**
     * Check and activate or deactivate.
     *
     * @return HnhDigital\NavigationBuilder\Item
     */
    private function checkActive($update_parents = true)
    {
        $this->setActive($this->generateUrl() == \Request::url(), $update_parents);

        return $this;
    }

    /**
     * Set the title.
     *
     * @param string $value
     *
     * @return HnhDigital\NavigationBuilder\Item
     */
    public function setTitle($value)
    {
        $current_title = Arr::get($this->data, 'title', '');
        $this->data['title'] = $value;
        if (Arr::get($this->data, 'nickname', '') == $current_title) {
            $this->nickname = $value;
        }

        return $this;
    }

    /**
     * Set the nickname.
     *
     * @param string $value
     *
     * @return HnhDigital\NavigationBuilder\Item
     */
    public function setNickname($value)
    {
        $this->data['nickname'] = strtolower(Str::ascii($value));

        return $this;
    }

    /**
     * Get the nickname.
     *
     * @param string $value
     *
     * @return HnhDigital\NavigationBuilder\Item
     */
    public function getNickname()
    {
        return strtolower(Str::ascii(Arr::get($this->data, 'nickname', '')));
    }

    /**
     * Get the menu for this item.
     *
     * @return Menu
     */
    public function getMenu()
    {
        return $this->menu;
    }

    /**
     * Set this item active.
     *
     * @param bool $active
     *
     * @return HnhDigital\NavigationBuilder\Item
     */
    public function setActive($active = true, $update_parents = true)
    {
        $this->data['active'] = $active;

        $method_name = $active ? 'add' : 'remove';
        $method_name .= $this->getActiveOnLinkOption() ? 'Link' : 'Item';
        $method_name .= 'Attribute';

        $this->$method_name('class', 'active');

        // Activate parents.
        if ($update_parents && ! is_null($this->parent) && $active) {
            $this->parent()->setActive($active);
        }

        return $this;
    }

    /**
     * Check the html content for sprintf template before allocation.
     *
     * @param string $template
     * @param array  $replacements
     *
     * @return HnhDigital\NavigationBuilder\Item
     */
    public function setHtml($template, ...$replacements)
    {
        $this->data['html'] = count($replacements) ? sprintf($template, ...$replacements) : $template;

        return $this;
    }

    /**
     * Generate the url for this item.
     *
     * @return string
     */
    private function generateUrl()
    {
        $url = '';

        // Create the URL.
        switch ($this->link_type) {
            case self::LINK_ACTION:
                $url = action(...$this->link_value);
                break;
            case self::LINK_ROUTE:
                $url = route(...$this->link_value);
                break;
            case self::LINK_URL:
                $url = env('APP_NO_SSL', true) ? url(...$this->link_value) : secure_url(...$this->link_value);
                break;
            case self::LINK_INSECURE_URL:
                $url = url(...$this->link_value);
                break;
            case self::LINK_EXTERNAL_URL:
                $url = stripos($this->link_value[0], 'http') === false ? 'http://' : '';
                $url .= $this->link_value[0];
                break;
        }

        return $url;
    }

    /**
     * Activate if listed is item is active.
     *
     * @param Item $item
     *
     * @return bool
     */
    public static function activateIfItemIsActive($item)
    {
        // No menu items provided.
        if (empty($list = $item->getActiveIfItemIsActiveOption())) {
            return false;
        }

        // Only a string value has been provided.
        if (! is_array($list)) {
            $list = [$list];
        }

        // Move all local menu items to a self key.
        // We can have another navigation menu list provided.
        $list['self'] = [];
        foreach ($list as $key => $value) {
            if (is_int($key)) {
                $list['self'][] = $value;
                unset($list[$key]);
            }
        }

        // Check until we find an active menu item.
        foreach ($list as $menu_name => $items) {
            // Check this navigation menu (self), or access the provided navigation menu.
            $menu = $menu_name == 'self' ? $item->getMenu() : app('Nav')->menu($menu_name);

            if (! is_iterable($items)) {
                continue;
            }

            // Check each item.
            foreach ($items as $nickname) {
                // Get the menu item for this nickname.
                $check_item = $menu->get($nickname);

                // Item exists, get it to check if it is active, and then,
                // if active, set this current item active.
                if (! is_null($check_item) && $check_item->checkActive(false) && $check_item->getActive()) {
                    $item->setActive();

                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check if listed items are active.
     *
     * @param Item $item
     *
     * @return bool
     */
    public function checkItemIsActive($item)
    {
        if ($this->getActive()) {
            return true;
        }

        if (empty($item_list = $item->getHideIfItemNotActiveOption())) {
            return true;
        }

        if (! is_array($item_list)) {
            $item_list = [$item_list];
        }

        foreach ($item_list as $nickname) {
            $check_item = $item->getMenu()->get($nickname);

            if (! is_null($check_item) && $check_item->getActive()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Add menu as a dropdown.
     *
     * @param string|array $menu_source
     *
     * @return HnhDigital\NavigationBuilder\Item
     */
    public function makeDropdown($menu_source, $config = [])
    {
        if (! is_array($menu_source)) {
            $menu_source = [$menu_source];
        }

        $this->setDropdownSourceOption($menu_source)
            ->setDropdownConfigOption($config);

        return $this;
    }

    /**
     * Render dropdown.
     *
     * @return HnhDigital\NavigationBuilder\Item
     */
    public function renderDropdown()
    {
        $menu_source = Arr::get($this->option, 'dropdown_source', null);
        $config = Arr::get($this->option, 'dropdown_config', null);

        if (is_null($menu_source)) {
            return $this;
        }

        $menu_container = '';

        $item_callback = Arr::get($this->option, 'item_callback', null);

        foreach ($menu_source as $menu_name) {
            $menu = app('Nav')->get($menu_name);

            if (is_null($menu) || empty($menu)) {
                continue;
            }

            $menu_container .= $menu->setItemCallbackOption(function (&$item) use ($item_callback) {
                $item->addLinkAttribute('class', 'dropdown-item')
                    ->setItemTagOption('div');

                if (! is_null($item_callback) && is_callable($item_callback)) {
                    $item_callback($item);
                    $item->setItemCallbackOption($item_callback);
                }
            })->render('');
        }

        $menu_container = Html::div($menu_container)
            ->addClass(implode(' ', array_merge(['dropdown-menu'], Arr::get($config, 'container.class', []))));

        $this->setAfterTagOption($menu_container)
            ->addItemAttribute('class', 'dropdown')
            ->addLinkAttribute('class', 'dropdown-toggle')
            ->addLinkAttribute('data-toggle', 'dropdown');

        return $this;
    }

    /**
     * Check if user can use this menu item.
     *
     * @return HnhDigital\NavigationBuilder\Item
     */
    public function can($ability, $model, $user = false)
    {
        if ($user === false) {
            $user = auth()->user();
        }

        $this->authorized = $user->can($ability, $model, $user);

        return $this;
    }

    /**
     * Render this item.
     *
     * @return string
     */
    public function render($menu_level = 0)
    {
        static::activateIfItemIsActive($this);

        // Not authorized for this menu.
        if (! $this->authorized) {
            return '';
        }

        // Item or parent is not active.
        if (! $this->checkItemIsActive($this)) {
            return '';
        }

        // Hide this menu if not active.
        if ($this->getHideIfNotActiveOption()) {
            return '';
        }

        // Item is not directly active and marked to hide if not active.
        if (! $this->getActive() && $this->getHideIfNotActiveOption()) {
            return '';
        }

        // Item is directly active and marked to hide if active.
        if ($this->getActive() && $this->getHideIfItemActiveOption()) {
            return '';
        }

        // Render dropdown if has been requested.
        $this->renderDropdown();

        // Available options for this item.
        $container_tag = Arr::get($this->option, 'container_tag', 'ul');
        $container_class = Arr::get($this->option, 'container_class', 'nav');
        $item_tag = Arr::get($this->option, 'item_tag', 'li');
        $text_only = Arr::get($this->option, 'text_only', false);
        $hide_children = Arr::get($this->option, 'hide_children', false);
        $force_inactive = Arr::get($this->option, 'force_inactive', false);
        $before_tag_html = Arr::get($this->option, 'before_tag', '');
        $after_tag_html = Arr::get($this->option, 'after_tag', '');
        $no_title = Arr::get($this->option, 'no_title', '');

        $html = (! $text_only && $this->html != '') ? $this->html : $this->title;

        // Force the menu items to not show active.
        if ($force_inactive) {
            $this->setActive(false);
        }

        // Link is not empty.
        if ($this->link_type !== self::LINK_EMPTY) {
            // Create the link.
            $html_link = Html::a()->text($html)
                ->openNew(! $this->getOpenNewWindowOption())
                ->href($this->generateUrl());

            if (! $no_title) {
                $html_link->title($this->title);
            }

            if (! $text_only) {
                $html_link->addAttributes($this->link_attribute);
            }

            $html = $html_link->s();
        } elseif (! empty($this->title)) {
            $html = Html::span($html);

            if (! $no_title) {
                $html->title($this->title);
            }

            if (! $text_only) {
                $html->addAttributes($this->link_attribute);
            }
        }

        // Generate each of the children items.
        if (! $hide_children && $this->hasChildren()) {
            $child_html = '';

            // Grab the callback, we pass this down each child item.
            $item_callback = Arr::get($this->option, 'item_callback', null);

            // Generate each child menu item (repeat this method)
            foreach ($this->children() as $item) {
                $item->setItemTagOption($item_tag);

                if (! is_null($item_callback) && is_callable($item_callback)) {
                    $item_callback($item);
                    $item->setItemCallbackOption($item_callback);
                }

                $child_html .= $item->render($menu_level + 1);
            }

            if ($child_html !== '') {
                // Name the level
                $number_as_word = (new NumberConverter())->ordinal($menu_level);

                // Generate the list container
                $html_container = Html::$container_tag($child_html)
                    ->addAttributes($this->container_attribute)
                    ->addClass($container_class)
                    ->addClass(sprintf('%s-%s-level', $container_class, $number_as_word));

                $html .= $html_container->s();
            }
        }

        if ($this->generateUrl() == \Request::url()
            && ! $this->hasChildren()) {
            $this->addItemAttribute('class', 'actual-link');
        }

        // Create the container and allocate the link.
        return Html::$item_tag($before_tag_html.$html.$after_tag_html)->addAttributes($this->item_attribute)->s();
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
     * @return HnhDigital\NavigationBuilder\Item|string
     */
    public function __call($name, $arguments)
    {
        $original_method_name = Str::snake($name);
        preg_match('/^([a-z]+)_([a-z_]+)_([a-z]+)$/', $original_method_name, $matches);

        if (count($matches) === 4) {
            [$original_method_name, $action, $key, $method_name] = $matches;

            $allowed_actions = ['get', 'set', 'add', 'remove', 'append', 'prepend'];
            $allowed_keys = ['item', 'link', 'container', 'link'];
            $allowed_method_names = ['attribute', 'option'];

            if (in_array($action, $allowed_actions)
                && in_array($method_name, $allowed_method_names)) {

                // Get calls.
                if ($action == 'get' || $action == 'set') {
                    $array_func = $action;
                    if (($key == 'item' || $key == 'link') && $method_name == 'attribute') {
                        $result = Arr::$array_func($this->{$key.'_'.$method_name}, Arr::get($arguments, 0, null), Arr::get($arguments, 1, null));

                        return $action == 'get' ? $result : $this;
                    }

                    if ($method_name == 'option') {
                        if (count($arguments) > 1) {
                            $data = $arguments;
                        } else {
                            $default = $action == 'get' ? false : true;
                            $data = Arr::get($arguments, 0, $default);
                        }
                        $result = Arr::$array_func($this->option, $key, $data);

                        return $action == 'get' ? $result : $this;
                    }

                    $name = $method_name;
                }

                // Manipulate values.
                if (($action == 'add' || $action == 'remove' || $action == 'append' || $action == 'prepend')
                    && ($key == 'item' || $key == 'container' || $key == 'link') && $method_name == 'attribute') {
                    $input_value = Arr::get($arguments, 1, '');
                    $current_value = Arr::get($this->{$key.'_'.$method_name}, Arr::get($arguments, 0, null), '');

                    // Class attributes
                    if ($arguments[0] == 'class') {
                        // Convert string to array, trim input and remove possible duplicates.
                        $current_value_array = explode(' ', $current_value);
                        $input_value = trim($input_value);
                        $current_value_array = array_unique($current_value_array);

                        // Remove class from list
                        if ($action == 'remove') {
                            if (($index = array_search($input_value, $current_value_array)) !== false) {
                                unset($current_value_array[$index]);
                            }

                            // Add class to list
                        } elseif ($action != 'remove') {
                            $current_value_array[] = $input_value;
                        }

                        // Remove duplicates, sort and assign string value.
                        $current_value_array = array_unique($current_value_array);
                        sort($current_value_array);
                        $current_value = trim(implode(' ', $current_value_array));

                    // Other attributes
                    } elseif ($arguments[0] != 'class') {
                        if ($action == 'remove') {
                            $current_value = str_replace($input_value, '', $current_value);
                        }

                        switch ($action) {
                            case 'add':
                            case 'append':
                                $current_value .= $input_value;
                                break;
                            case 'prepend':
                                $current_value = $input_value.$current_value;
                                break;
                        }

                        $current_value = trim($current_value);
                    }

                    if (strlen($current_value)) {
                        Arr::set($this->{$key.'_'.$method_name}, Arr::get($arguments, 0, null), $current_value);
                    } else {
                        unset($this->{$key.'_'.$method_name}[Arr::get($arguments, 0, null)]);
                    }

                    return $this;
                }
            }
        } else {
            [$action, $method_name, $key] = array_pad(explode('_', $original_method_name, 3), 3, '');
        }

        $name = $method_name;

        // Use the magic get/set instead
        if (count($arguments) == 0) {
            return $this->$name;
        }

        if (method_exists($this, 'set'.Str::studly($action))) {
            $this->{'set'.Str::studly($action)}(...$arguments);

            return $this;
        }

        if (isset($this->$name)) {
            $this->$name = Arr::get($arguments, 0, '');

            return $this;
        }

        $this->data[$name] = Arr::get($arguments, 0, '');

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
    public function data($name, $value)
    {
        $this->data[$name] = $value;

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
        $set_method = 'set'.Str::studly($name);
        if (method_exists($this, $set_method)) {
            $this->$set_method($value);

            return $this;
        }

        $this->data[$name] = $value;

        return $this;
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
        $get_method = 'get'.Str::studly($name);
        if (method_exists($this, $get_method)) {
            return $this->$get_method();
        }

        return Arr::get($this->data, $name, '');
    }
}
