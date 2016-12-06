<?php

namespace Bluora\LaravelNavigationBuilder\Tests;

use Bluora\LaravelNavigationBuilder\Menu;
use PHPUnit\Framework\TestCase;

class MenuTest extends TestCase
{
    /**
     * Assert that created object creates the correct output.
     */
    public function testCreateTwoMenuItems()
    {
        $menu = new Menu('test');

        $menu->add('Home');
        $this->assertEquals('<li><span title="Home">Home</span></li>', $menu->render());

        $profile_item = $menu->add('Profile')->addItemAttribute('class', 'active');
        $this->assertEquals('<li><span title="Home">Home</span></li><li class="active"><span title="Profile">Profile</span></li>', $menu->render());
        $this->assertEquals('active', $profile_item->getItemAttribute('class'));
    }

    /**
     * Assert that created object creates the correct output.
     */
    public function testCreateMenuWithDifferentLinks()
    {
        $menu = new Menu('test');
        $menu->add('Home');

        // Add a child menu item.
        $home_item = $menu->get('home');

        $this->assertNotEquals($home_item, null);

        $profile_item = $home_item->add('Profile')
            ->route('profile::edit-profile')
            ->html('Profile')
            ->nickname('profile_edit_profile');

        $parent_id = $home_item->getId();

        $this->assertEquals('<li class="active"><a href="profile::edit-profile" title="Profile">Profile</a></li>', $menu->render($parent_id));

        $profile_item->action('Profile@ProfileController');
        $this->assertEquals('<li class="active"><a href="Profile@ProfileController" title="Profile">Profile</a></li>', $menu->render($parent_id));

        $profile_item->url('profile/');
        $this->assertEquals('<li class="active"><a href="https://localhost/profile/" title="Profile">Profile</a></li>', $menu->render($parent_id));

        $profile_item->insecureUrl('profile/');
        $this->assertEquals('<li class="active"><a href="https://localhost/profile/" title="Profile">Profile</a></li>', $menu->render($parent_id));

        $profile_item->externalUrl('google.com');
        $this->assertEquals('<li><a target="_blank" href="http://google.com" title="Profile">Profile</a></li>', $menu->render($parent_id));

        $profile_item->setItemAttribute('class', 'class1');

        $profile_item->appendItemAttribute('class', 'class2');
        $this->assertEquals('class1 class2', $profile_item->getItemAttribute('class'));

        $profile_item->prependItemAttribute('class', 'class3');
        $this->assertEquals('class3 class1 class2', $profile_item->getItemAttribute('class'));

        // Find home item.
        $home_item = $menu->getByTitle('Home');
        $this->assertNotEquals($home_item, null);
        $this->assertEquals($parent_id, $home_item->id);
    }

    /**
     * Assert that created object creates the correct output.
     */
    public function testAttributeSetting()
    {
        $menu = new Menu('test');
        $menu->add('Home')->externalUrl('https://github.com');

        $home_item = $menu->get('home');

        $home_item->item('class', 'test');
        $this->assertEquals('<li class="test"><a target="_blank" href="https://github.com" title="Home">Home</a></li>', $home_item->render());

        $home_item->link('class', 'test');
        $this->assertEquals('<li class="test"><a class="test" target="_blank" href="https://github.com" title="Home">Home</a></li>', $home_item->render());

    }
}
