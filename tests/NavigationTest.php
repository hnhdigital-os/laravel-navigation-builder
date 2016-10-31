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

        $this->assertEquals($navigation->getMenu('main'), $menu);
        $this->assertEquals($navigation->getMenus()->values(), $collection->values());
    }

    /**
     * Assert that created object creates the correct output.
     */
    public function testCreateMenuWithCallback()
    {
        $navigation = new Navigation();
        $menu = $navigation->createMenu('main', function ($menu) {
            $menu->add('Home');
        });

        $item = $menu->first();

        $this->assertNotEquals($item, null);
        $this->assertEquals('Home', $item->title);
        $this->assertEquals('home', $item->nickname);
    }
}
