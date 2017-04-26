<?php

namespace Bluora\LaravelNavigationBuilder;

use Bluora\LaravelHtmlGenerator\Html;
use Bluora\PhpNumberConverter\NumberConverter;
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
     * Unique ID for this item.
     *
     * @var string
     */
    public $id = '';

    /**
     * Object reference to the parent.
     *
     * @var \Bluora\LaravelNavigationBuilder\Item
     */
    public $parent;

    /**
     * Reference to the parent.
     *
     * @var string
     */
    public $parent_id = '';

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
     *
     * @return Bluora\LaravelNavigationBuilder\Item
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
     * @return \Bluora\LaravelNavigationBuilder\Collection
     */
    public function children($depth = false)
    {
        return $this->menu->whereParentId($this->id, $depth)->all();
    }

    /**
     * Returns the parent of this item.
     *
     * @return Bluora\LaravelNavigationBuilder\Item
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
     * @return Bluora\LaravelNavigationBuilder\Item
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
     * @return Bluora\LaravelNavigationBuilder\Item
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
     * @return Bluora\LaravelNavigationBuilder\Item
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
     * @return Bluora\LaravelNavigationBuilder\Item
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
     * @return Bluora\LaravelNavigationBuilder\Item
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
     * @return Bluora\LaravelNavigationBuilder\Item
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
     * @return Bluora\LaravelNavigationBuilder\Item
     */
    public function externalUrl($url)
    {
        $this->link_type = self::LINK_EXTERNAL_URL;
        $this->link_value = [$url];
        $this->setOptionOpenNewWindow();
        $this->setActive(false);

        return $this;
    }

    /**
     * Check and activate or deactivate.
     *
     * @return Bluora\LaravelNavigationBuilder\Item
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
     * @return Bluora\LaravelNavigationBuilder\Item
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
     * @return Bluora\LaravelNavigationBuilder\Item
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
     * @return Bluora\LaravelNavigationBuilder\Item
     */
    public function getNickname()
    {
        return strtolower(Str::ascii(array_get($this->data, 'nickname', '')));
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
     * @return Bluora\LaravelNavigationBuilder\Item
     */
    public function setActive($active = true, $update_parents = true)
    {
        $this->data['active'] = $active;

        $method_name = $active ? 'add' : 'remove';
        $method_name .= $this->getOptionActiveOnLink() ? 'Link' : 'Item';
        $method_name .= 'Attribute';

        $this->$method_name('class', 'active');

        // Activate parents.
        if ($update_parents && !is_null($this->parent) && $active) {
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
     * @return Bluora\LaravelNavigationBuilder\Item
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
        if (empty($item_list = $item->getOptionActiveIfItemIsActive())) {
            return false;
        }

        if (!is_array($item_list)) {
            $item_list = [$item_list];
        }

        foreach ($item_list as $nickname) {
            $check_item = $item->getMenu()->get($nickname);

            if (!is_null($check_item) && $check_item->getActive()) {
                $item->setActive();

                return true;
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

        if (empty($item_list = $item->getOptionHideIfItemNotActive())) {
            return true;
        }

        if (!is_array($item_list)) {
            $item_list = [$item_list];
        }

        foreach ($item_list as $nickname) {
            $check_item = $item->getMenu()->get($nickname);

            if (!is_null($check_item) && $check_item->getActive()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Render this item.
     *
     * @return string
     */
    public function render($menu_level = 0)
    {
        static::activateIfItemIsActive($this);


        if ($this->checkItemIsActive($this)
            && (($this->getOptionHideIfNotActive() && $this->getActive())
            || !$this->getOptionHideIfNotActive())) {

            // Available options for this item.
            $container_tag = array_get($this->option, 'container_tag', 'ul');
            $container_class = array_get($this->option, 'container_class', 'nav');
            $item_tag = array_get($this->option, 'tag', 'li');
            $text_only = array_get($this->option, 'text_only', false);
            $hide_children = array_get($this->option, 'hide_children', false);
            $force_inactive = array_get($this->option, 'force_inactive', false);

            $html = (!$text_only && $this->html != '') ? $this->html : $this->title;

            // Force the menu items to not show active.
            if ($force_inactive) {
                $this->setActive(false);
            }

            // Link is not empty.
            if ($this->link_type !== self::LINK_EMPTY) {
                // Create the link.
                $html_link = Html::a()->text($html)
                    ->openNew(!$this->getOptionOpenNewWindow())
                    ->href($this->generateUrl())
                    ->title($this->title);

                if (!$text_only) {
                    $html_link->addAttributes($this->link_attribute);
                }

                $html = $html_link->s();
            } else {
                $html = Html::span($html)->title($this->title);

                if (!$text_only) {
                    $html->addAttributes($this->link_attribute);
                }
            }

            // Generate each of the children items.
            if (!$hide_children && $this->hasChildren()) {
                $child_html = '';

                // Generate each child menu item (repeat this method)
                foreach ($this->children() as $item) {
                    $item->setOptionItemTag($item_tag);
                    $child_html .= $item->render($menu_level + 1);
                }

                if ($child_html !== '') {
                    // Name the level
                    $number_as_word = (new NumberConverter())->ordinal($menu_level);

                    // Generate the list container
                    $html_container = Html::$container_tag($child_html)
                        ->addAttributes($this->item_attribute)
                        ->addClass($container_class)
                        ->addClass(sprintf('%s-%s-level', $container_class, $number_as_word));

                    $html .= $html_container->s();
                }
            }

            if ($this->generateUrl() == \Request::url()
                && !$this->hasChildren()) {
                $this->addItemAttribute('class', 'actual-link');
            }

            // Create the container and allocate the link.
            return Html::$item_tag($html)->addAttributes($this->item_attribute)->s();
        }

        return '';
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
     * @return Bluora\LaravelNavigationBuilder\Item|string
     */
    public function __call($name, $arguments)
    {
        $method_name = snake_case($name);
        list($action, $method_name, $key) = array_pad(explode('_', $method_name, 3), 3, '');

        // Get calls.
        if ($action == 'get' || $action == 'set') {
            $array_func = 'array_'.$action;
            if (($method_name == 'item' || $method_name == 'link') && $key == 'attribute') {
                $result = $array_func($this->{$method_name.'_'.$key}, array_get($arguments, 0, null), array_get($arguments, 1, null));

                return $action == 'get' ? $result : $this;
            }

            if ($method_name == 'option') {
                if (count($arguments) > 1) {
                    $data = $arguments;
                } else {
                    $default = $action == 'get' ? false : true;
                    $data = array_get($arguments, 0, $default);
                }
                $result = $array_func($this->option, $key, $data);

                return $action == 'get' ? $result : $this;
            }

            $name = $method_name;
        }

        // Manipulate values.
        if (($action == 'add' || $action == 'remove' || $action == 'append' || $action == 'prepend')
            && ($method_name == 'item' || $method_name == 'link') && $key == 'attribute') {
            $input_value = array_get($arguments, 1, '');
            $current_value = array_get($this->{$method_name.'_'.$key}, array_get($arguments, 0, null), '');

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
                array_set($this->{$method_name.'_'.$key}, array_get($arguments, 0, null), $current_value);
            } else {
                unset($this->{$method_name.'_'.$key}[array_get($arguments, 0, null)]);
            }

            return $this;
        }

        // Use the magic get/set instead
        if (count($arguments) == 0) {
            return $this->$name;
        }

        if (method_exists($this, 'set'.studly_case($action))) {
            $this->{'set'.studly_case($action)}(...$arguments);

            return $this;
        }

        if (isset($this->$name)) {
            $this->$name = array_get($arguments, 0, '');

            return $this;
        }

        $this->data[$name] = array_get($arguments, 0, '');

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
        $name = snake_case($name);
        $set_method = 'set'.studly_case($name);
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
        $name = snake_case($name);
        $get_method = 'get'.studly_case($name);
        if (method_exists($this, $get_method)) {
            return $this->$get_method();
        }

        return array_get($this->data, $name, '');
    }
}
