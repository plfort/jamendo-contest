<?php
namespace Cogipix\CogimixJamendoBundle\ViewHooks\Javascript;
use Cogipix\CogimixCommonBundle\ViewHooks\Javascript\JavascriptImportInterface;

use Cogipix\CogimixCommonBundle\ViewHooks\Menu\MenuItemInterface;

/**
 * Use by the core to include plugin's JS
 * @author plfort - Cogipix
 *
 */
class JavascriptImportRenderer implements JavascriptImportInterface
{

    /**
     * Template file containing JS
     * @return string
     */
    public function getJavascriptImportTemplate()
    {
        return 'CogimixJamendoBundle::js.html.twig';
    }

}
