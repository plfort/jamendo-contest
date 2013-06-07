<?php
namespace Cogipix\CogimixJamendoBundle\ViewHooks\Menu;
use Cogipix\CogimixCommonBundle\ViewHooks\Menu\MenuItemInterface;

/**
 * Use by the core to display the menu (login/logout)
 * @author plfort - Cogipix
 *
 */
class MenuRenderer implements MenuItemInterface
{
    /**
     * Temaplet file containing the menu
     * @return string
     */
    public function getMenuItemTemplate()
    {
        return 'CogimixJamendoBundle:Menu:menu.html.twig';

    }
}
