<?php

namespace Bluora\LaravelNavigationBuilder\Tests;

use Bluora\LaravelNavigationBuilder\Collection;
use Bluora\LaravelNavigationBuilder\Navigation;
use PHPUnit\Framework\TestCase;

class NavigationTest extends TestCase
{
    /**
     * Assert that created object creates the correct output.
     */
    public function testCreateMenu()
    {
        $navigation = new Navigation();
        $menu = $navigation->createMenu('main');

        $collection = (new Collection())->push($menu, 'main');

        $this->assertEquals($menu, $navigation->getMenu('main'));
        $this->assertEquals($collection->values(), $navigation->getMenus()->values());
    }

    /**
     * Assert that created object creates the correct output.
     */
    public function testCreateMenuWithCallback()
    {
        $navigation = new Navigation();
        $menu = $navigation->createMenu('main', function($menu) {
            $menu->add('Home');
        });

        $item = $menu->first();

        $this->assertEquals($item->title, 'Home');
        $this->assertEquals($item->nickname, 'Home');
    }
}
