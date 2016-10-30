<?php

namespace Bluora\LaravelNavigationBuilder\Tests;

use Bluora\LaravelNavigationBuilder\Collection;
use Bluora\LaravelNavigationBuilder\Menu;
use PHPUnit\Framework\TestCase;

class MenuTest extends TestCase
{
    /**
     * Assert that created object creates the correct output.
     */
    public function testCreateMenuContainer()
    {
        $menu = new Menu('test');

        $this->assertEquals($menu->name, 'test');
        $this->assertEquals($menu->render(), '<ul></ul>');

        $menu->addAttribute('class', 'nav');

        $this->assertEquals($menu->render(), '<ul class="nav"></ul>');

        $menu->addAttribute('class', 'foo');
        $this->assertEquals($menu->render(), '<ul class="nav foo"></ul>');

        $menu->removeAttribute('class', 'foo');
        $this->assertEquals($menu->render(), '<ul class="nav"></ul>');

        $menu->setOptionTag('div');
        $this->assertEquals($menu->render(), '<div class="nav"></div>');

        $menu->setOptionTag('ul');
        $menu->appendAttribute('class', 'foo');
        $this->assertEquals($menu->render(), '<ul class="nav foo"></ul>');

        $menu = $menu->prependAttribute('class', 'foo');
        $this->assertEquals($menu->render(), '<ul class="foo nav"></ul>');
    }
    /**
     * Assert that created object creates the correct output.
     */
    public function testCreateMenuItems()
    {
        $menu = new Menu('test');

        $menu->add('Welcome');
        $this->assertEquals($menu->render(), '<ul><li>Welcome</li></ul>');

        $menu->add('First menu')->addItemAttribute('class', 'foo');
        $this->assertEquals($menu->render(), '<ul><li>Welcome</li><li class="foo">First menu</li></ul>');
    }
}
