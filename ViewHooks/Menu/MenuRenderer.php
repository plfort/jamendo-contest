<?php
namespace Cogipix\CogimixJamendoBundle\ViewHooks\Menu;
use Cogipix\CogimixCommonBundle\ViewHooks\Menu\MenuItemInterface;

/**
 *
 * @author plfort - Cogipix
 *
 */
class MenuRenderer implements MenuItemInterface{

    public function getMenuItemTemplate()
    {
          return 'CogimixJamendoBundle:Menu:menu.html.twig';

    }
}