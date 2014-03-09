<?php
namespace Cogipix\CogimixJamendoBundle\ViewHooks\Menu;
use Cogipix\CogimixCommonBundle\ViewHooks\Menu\MenuItemInterface;
use Cogipix\CogimixCommonBundle\ViewHooks\Menu\AbstractMenuItem;

/**
 * Use by the core to display the menu (login/logout)
 * @author plfort - Cogipix
 *
 */
class MenuItem extends AbstractMenuItem
{
    /**
     * Temaplet file containing the menu
     * @return string
     */
    public function getMenuItemTemplate()
    {
        return 'CogimixJamendoBundle:Menu:menu.html.twig';

    }

    public function getName(){
    	return 'jamendo';
    }
}
