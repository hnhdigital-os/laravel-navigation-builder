<?php

namespace Bluora\LaravelNavigationBuilder\Tests;

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

        $this->assertEquals('test', $menu->name);
        $this->assertEquals('<ul></ul>', $menu->render());

        $menu->addAttribute('class', 'nav');

        $this->assertEquals('<ul class="nav"></ul>', $menu->render());
        $this->assertEquals('nav', $menu->getAttribute('class'));

        $menu->addAttribute('class', 'foo');
        $this->assertEquals('<ul class="nav foo"></ul>', $menu->render());

        $menu->removeAttribute('class', 'foo');
        $this->assertEquals('<ul class="nav"></ul>', $menu->render());

        $menu->setOptionTag('div');
        $this->assertEquals('<div class="nav"></div>', $menu->render());

        $menu->setOptionTag('ul');
        $menu->appendAttribute('class', 'foo');
        $this->assertEquals('<ul class="nav foo"></ul>', $menu->render());
        $this->assertEquals('ul', $menu->getOptionTag('ul'));

        $menu = $menu->prependAttribute('class', 'foo');
        $this->assertEquals('<ul class="foo nav"></ul>', $menu->render());

        $menu->name('test1');
        $this->assertEquals('test1', $menu->name());
    }

    /**
     * Assert that created object creates the correct output.
     */
    public function testCreateTwoMenuItems()
    {
        $menu = new Menu('test');

        $menu->add('Home');
        $this->assertEquals('<ul><li>Home</li></ul>', $menu->render());

        $profile_item = $menu->add('Profile')->addItemAttribute('class', 'active');
        $this->assertEquals('<ul><li>Home</li><li class="active">Profile</li></ul>', $menu->render());
        $this->assertEquals('active', $profile_item->getItemAttribute('class'));
    }

    /**
     * Assert that created object creates the correct output.
     */
    public function testCreateMenuWithChildren()
    {
        $menu = new Menu('test');
        $menu->add('Home');

        // Add a child menu item.
        $home_item = $menu->get('home');

        $this->assertNotEquals($home_item, null);

        $home_item->add('Profile')
            ->route('profile::edit-profile')
            ->html('Profile')
            ->nickname('profile_edit_profile');

        $parent_id = $home_item->getId();

        $this->assertEquals('<ul><li><a target="_blank" href="profile::edit-profile">Profile</a></li></ul>', $menu->render($parent_id));

        // Find home item.
        $home_item = $menu->getByTitle('Home');
        $this->assertNotEquals($home_item, null);
        $this->assertEquals($parent_id, $home_item->id);
    }
}
