<?php
/*
Name: BeastxPlugin Helper Class
Description: Several functions/tools to make the plugins devlopment more easy.
Version: 1.0
Author: Beastx
Author URI: http://www.beastxblog.com/
*/

/*
    Copyright 2010 Beastx (Leandro Asrilevich) (http://beastxblog.com/)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

Class BeastxPlugin {

    public function __construct() {
        $this->addWordpressEventHandlers();
        $this->addPluginLinks();
    }
    
    
    /*****************************************************
    Plugin Links Helpers (links for plugin in plugins page)
    *****************************************************/
    
    private function addPluginLinks() {
        $this->addFilter('plugin_action_links_' . $this->pluginBaseFileName, '_addPluginActionLink');
        $this->addFilter('plugin_row_meta', '_addPluginMetaLink');
    }
    
    function _addPluginActionLink($links) {
        if (method_exists($this, 'getPluginActionLinks')) {
            $actionsLinks = $this->getPluginActionLinks();
            $newLinks = array();
            for ($i = 0; $i < count($this->actionsLinks); ++$i) {
                $metaLink = '<a href="'. $actionsLinks[$i]['url'] .'">'. $actionsLinks[$i]['label'] .'</a>';
                array_push($newLinks, $metaLink);
            }
            return array_merge($newLinks, $links);
        } else {
            return $links;
        }
    }  
    
    function _addPluginMetaLink($links, $file) {
        if (method_exists($this, 'getPluginMetaLinks')) {
            $metaLinks = $this->getPluginMetaLinks();
            if ($file == $this->pluginBaseFileName) {
                for ($i = 0; $i < count($metaLinks); ++$i) {
                    $metaLink = ($i == 0 ? '<br>' : '') . '<a style="color: black; font-weight: bold;" href="'. $metaLinks[$i]['url'] .'">'. $metaLinks[$i]['label'] .'</a>';
                    array_push($links, $metaLink);
                }
            }
            return $links;
        } else {
            return $links;
        }
    }
    
    
    /*****************************************************
    Events Handlers
    *****************************************************/
    
    private function addWordpressEventHandlers() {
        register_activation_hook($this->pluginBaseFileName, array($this, '_onPluginActivate'));
        register_deactivation_hook($this->pluginBaseFileName, array($this, '_onPluginDeactivate'));
        $this->addAction('init', '_onInit');
        $this->addAction('admin_init', '_onAdminInit');
        $this->addAction('plugins_loaded', '_onPluginLoad');
    }
    
    function _onInit() {
        if (method_exists($this, 'onInit')) { $this->onInit(); }
    }
    
    function _onAdminInit() {
        if (method_exists($this, 'onAdminInit')) { $this->onAdminInit(); }
    }
    
    function _onPluginLoad() {
        if (method_exists($this, 'onPluginLoad')) { $this->onPluginLoad(); }
    }
    
    function _onPluginActivate() {
        if (method_exists($this, 'onPluginActivate')) { $this->onPluginActivate(); }
    }
    
    function _onPluginDeactivate() {
        if (method_exists($this, 'onPluginDeactivate')) { $this->onPluginDeactivate(); }
    }
    
    
    /*****************************************************
    Common helpers methods
    *****************************************************/
    
    public function addAction($action, $methodName, $priority = 10, $parameters = 2) {
        add_action(
            $action,
            is_string($methodName) ? array(&$this, $methodName) : $methodName,
            $priority,
            $parameters
        );
    }
    
    public function addFilter($action, $methodName, $priority = 10, $parameters = 2) {
        add_filter(
            $action,
            is_string($methodName) ? array(&$this, $methodName) : $methodName,
            $priority,
            $parameters
        );
    }
    
    

    
    
    
    
    
    

    
    public function addAdminMenu() {
        add_submenu_page(
            $this->pluginBaseFileName,
            $this->pluginName . ' - ' . $this->adminMenuOptions['subOptions'][0]['title'],
            $this->adminMenuOptions['subOptions'][0]['link'],
            8,
            $this->pluginBaseFileName,
            array(&$this, 'get' . mb_convert_case($this->adminMenuOptions['subOptions'][0]['id'], MB_CASE_TITLE))
        );
        $this->adminPage = add_menu_page(
            $this->pluginName . ' - ' . $this->adminMenuOptions['title'],
            $this->adminMenuOptions['title'],
            8,
            $this->pluginBaseFileName,
            array(&$this, 'get' . mb_convert_case($this->adminMenuOptions['subOptions'][0]['id'], MB_CASE_TITLE))
        );
        
        if (count($this->adminMenuOptions['subOptions']) > 1) {
            for ($i = 1; $i < count($this->adminMenuOptions['subOptions']); ++$i) {
                add_submenu_page(
                    $this->pluginBaseFileName,
                    $this->pluginName . ' - ' . $this->adminMenuOptions['subOptions'][$i]['title'],
                    $this->adminMenuOptions['subOptions'][$i]['link'],
                    8,
                    $this->pluginBaseName . '-' . $this->adminMenuOptions['subOptions'][$i]['id'],
                    array(&$this, 'get' . mb_convert_case($this->adminMenuOptions['subOptions'][$i]['id'], MB_CASE_TITLE))
                );
            }
        }
    }
    
    private function getActualPluginPage() {
        if (!empty($_GET['page'])) {
            if (preg_match("/" . $this->pluginBaseName . "/i", $_GET['page']) != false) {
                return str_replace($this->pluginBaseName . '-', '', $_GET['page']);
            } else if(preg_match("/" . str_replace('/', '\/', $this->pluginBaseFileName) . "/i", $_GET['page']) != false) {
                return 'mainPage';
            } else {
                return null;
            }
        } else {
            return null;
        }
    }
    
}

?>