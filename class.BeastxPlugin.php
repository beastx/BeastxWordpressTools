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

if (!class_exists('BeastxPlugin')) {

Class BeastxPlugin {

    private $favoriteMenuActionsToAdd = array();
    private $favoriteMenuActionsToRemove = array();
    private $styles = array();
    private $scripts = array();
    private $dashboardWidgets = array();
    private $contextualHelps = array();
    private $pluginActionLinks = array();
    private $pluginMetaLinks = array();
    private $topMenus = array();
    private $subMenus = array();
    private $subMenuItemsToRemove = array();
    private $menuItemsToRemove = array();

    public function __construct() {
        $this->pluginBaseName = BeastxFileSystemHelper::getPluginFolder();
        $this->pluginBaseFileName = $this->pluginBaseName . '/' .$this->pluginBaseName . '.php';
        $this->pluginBaseUrl = WP_PLUGIN_URL.'/'.$this->pluginBaseName;
        $this->pluginBasePath = WP_PLUGIN_DIR.'/'.$this->pluginBaseName;
        $this->assetsPath = $this->pluginBaseUrl . '/assets/';
        $this->textDomain = $this->pluginBaseName;
    }
    
    public function registerCoreBuiltInAssets() {
        $coreScriptsFolder = $this->pluginBaseUrl . '/BeastxWordpressTools/assets/scripts/';
        $coreStylesFolder = $this->pluginBaseUrl . '/BeastxWordpressTools/assets/styles/';
        $coreThirdPartyFolder = $this->pluginBaseUrl . '/BeastxWordpressTools/assets/thirdParty/';
        // TODO: Ver como hacer con lo de version por el cache
        wp_register_script('Beastx', $coreScriptsFolder . 'Beastx.js');
        wp_register_script('BeastxDOM', $coreScriptsFolder . 'DOM.js');
        wp_register_script('BeastxFileAttacher', $coreScriptsFolder . 'FileAttacher.js');
        wp_register_script('BeastxImages', $coreScriptsFolder . 'Images.js');
        wp_register_script('BeastxJson', $coreScriptsFolder . 'Json.js');
        wp_register_script('BeastxRowEditor', $coreScriptsFolder . 'RowEditor.js');
        wp_register_script('BeastxRowEditorBaseItem', $coreScriptsFolder . 'RowEditorBaseItem.js');
        wp_register_script('BeastxString', $coreScriptsFolder . 'String.js');
        wp_register_script('BeastxVAR', $coreScriptsFolder . 'VAR.js');
        wp_register_script('BeastxAjaxUploader', $coreThirdPartyFolder . 'AjaxUploader/ajaxupload.js');
        wp_register_script('BeastxjQueryUI', $coreThirdPartyFolder . 'jQueryUI/jquery-ui.min.js');
        wp_enqueue_style('BeastxjQueryUI', $coreThirdPartyFolder . 'jQueryUI/jquery-ui.css');
    }
    
    public function addImageAttacherAssets($loadOn = array()) {
        $this->registerBuiltInScript('BeastxImages', $loadOn);
        $this->addFileAttacherAssets($loadOn);
    }
    
    public function addFileAttacherAssets($loadOn = array()) {
        $this->registerBuiltInScript('BeastxFileAttacher', $loadOn);
        $this->registerBuiltInScript('BeastxJson', $loadOn);
        $this->registerBuiltInScript('BeastxAjaxUploader', $loadOn);
    }
    
    public function addRowEditorAssets($loadOn = array()) {
        $this->registerBuiltInScript('BeastxRowEditorBaseItem', $loadOn);
        $this->registerBuiltInScript('BeastxRowEditor', $loadOn);
    }
    
    public function addRowJqueryUIAssets($loadOn = array()) {
        $this->registerBuiltInStyle('BeastxjQueryUI', $loadOn);
        $this->registerBuiltInScript('BeastxjQueryUI', $loadOn);
    }
    
    public function addWordpressCommonFiltersAndActions() {
        register_activation_hook($this->pluginBaseFileName, array($this, '_onPluginActivate'));
        register_deactivation_hook($this->pluginBaseFileName, array($this, '_onPluginDeactivate'));
        $this->addAction('init', '_onInit');
        $this->addAction('admin_init', '_onAdminInit');
        $this->addAction('plugins_loaded', '_onPluginLoad');
        $this->addAction('save_post', '_onSavePost');
        $this->addFilter('the_content', '_onGetContent');
        $this->addFilter('pre_get_posts', '_onGetPosts');
        $this->addAction('admin_print_scripts', '_printScripts');
        $this->addAction('admin_print_styles', '_printStyles');
        $this->addAction('wp_print_scripts', '_printScripts');
        $this->addAction('wp_print_styles', '_printStyles');
        $this->addFilter('plugin_action_links_' . $this->pluginBaseFileName, '_addPluginActionLink');
        $this->addFilter('plugin_row_meta', '_addPluginMetaLink');
        $this->addAction('admin_menu', '_execMenuActions');
        $this->addFilter('favorite_actions', '_addCustomFavoriteOption');
        $this->addAction('wp_dashboard_setup', '_addDashboardWidget');
        $this->addFilter('contextual_help','_addCustomHelp');
    }
    
    
    /*****************************************************
    Plugin Links Helpers (links for plugin in plugins page)
    *****************************************************/
    
    public function registerPluginActionLink($label, $url) {
        array_push($this->pluginActionLinks, array('label' => $label, 'url' => $url));
    }
    
    public function _addPluginActionLink($links) {
        if (count($this->pluginActionLinks) > 0) {
            $actionsLinks = $this->pluginActionLinks;
            $newLinks = array();
            for ($i = 0; $i < count($actionsLinks); ++$i) {
                $metaLink = '<a href="'. $actionsLinks[$i]['url'] .'">'. $actionsLinks[$i]['label'] .'</a>';
                array_push($newLinks, $metaLink);
            }
            return array_merge($newLinks, $links);
        } else {
            return $links;
        }
    }
    
    public function registerPluginMetaLink($label, $url) {
        array_push($this->pluginMetaLinks, array('label' => $label, 'url' => $url));
    }
    
    public function _addPluginMetaLink($links, $file) {
        if (count($this->pluginMetaLinks) > 0) {
            $metaLinks = $this->pluginMetaLinks;
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
    
    public function _onInit() { if (method_exists($this, 'onInit')) { $this->onInit(); } }
    public function _onAdminInit() { if (method_exists($this, 'onAdminInit')) { $this->onAdminInit(); } }
    public function _onPluginLoad() { if (method_exists($this, 'onPluginLoad')) { $this->onPluginLoad(); } }
    public function _onPluginActivate() { if (method_exists($this, 'onPluginActivate')) { $this->onPluginActivate(); } }
    public function _onPluginDeactivate() { if (method_exists($this, 'onPluginDeactivate')) { $this->onPluginDeactivate(); } }
    public function _onSavePost($id) { if (method_exists($this, 'onSavePost')) { $this->onSavePost($id); } }
    public function _onGetContent($content) { if (method_exists($this, 'onGetContent')) { $this->onGetContent($content); } }
    public function _onGetPosts($query) { if (method_exists($this, 'onGetPosts')) { $this->onGetPosts($query); } }
    
    
    /*****************************************************
    Common helpers methods
    *****************************************************/
    
    public function addAction($action, $methodName, $priority = 10, $parameters = 2) {
        $callBack = is_string($methodName) ? array(&$this, $methodName) : $methodName;
        add_action($action, $callBack, $priority, $parameters);
    }
    
    public function addFilter($action, $methodName, $priority = 10, $parameters = 2) {
        $callBack = is_string($methodName) ? array(&$this, $methodName) : $methodName;
        add_filter($action, $callBack, $priority, $parameters);
    }
    
    
    
    public function addMetaBox($id, $label, $callBack, $postType, $column= 'normal', $priority = 'normal') {
        add_meta_box($id, $label, $callBack, $postType, $column, $priority);
    }
    
    public function addShortCode($shortCode, $callBack) {
        
    }
    
    public function registerCustomHelp($onEnviroment, $callBack, $hideDefaultHelp = false) {
        array_push(
            $this->contextualHelps,
            array(
                'onEnviroment' => $onEnviroment,
                'callBack' => $callBack,
                'hideDefaultHelp' => $hideDefaultHelp
            )
        );
    }
    
    public function _addCustomHelp($defaultHelp) {
        global $beastxEnviroment;
        if (count($this->contextualHelps) > 0) {
            global $pagenow;
            for ($i = 0; $i < count($this->contextualHelps); ++$i) {
                $help = $this->contextualHelps[$i];
                if (empty($help['onEnviroment']) || $beastxEnviroment->check($help['onEnviroment'])) {
                    if (!$help['hideDefaultHelp']) {
                        echo $defaultHelp;
                    }
                    call_user_func($help['callBack']);
                } else {
                    echo $defaultHelp;
                }
            }
        } else {
            echo $defaultHelp;
        }
    }
    
    public function removeFavoriteOption($action) {
        array_push($this->favoriteMenuActionsToRemove, $action);
    }
    
    public function addFavoriteOption($action, $label, $levelCapability = 'read') {
        array_push(
            $this->favoriteMenuActionsToAdd,
            array(
                'action' => $action,
                'label' => $label,
                'levelCapability' => $levelCapability
            )
        );
    }
    
    public function _addCustomFavoriteOption($actions) {
        for ($i = 0; $i < count($this->favoriteMenuActionsToRemove); ++$i) {
            unset($actions[$this->favoriteMenuActionsToRemove[$i]]);
        }
        for ($i = 0; $i < count($this->favoriteMenuActionsToAdd); ++$i) {
            $action = $this->favoriteMenuActionsToAdd[$i];
            $actions[$action['action']] = array($action['label'], $action['levelCapability']);
        }
        return $actions;
    }
    
    public function registerDashboardWidget($id, $title, $callBack) {
        array_push(
            $this->dashboardWidgets,
            array(
                'id' => $id,
                'title' => $title,
                'callBack' => $callBack
            )
        );
    }
    
    public function _addDashboardWidget() {
        for ($i = 0; $i < count($this->dashboardWidgets); ++$i) {
            wp_add_dashboard_widget($this->dashboardWidgets[$i]['id'], $this->dashboardWidgets[$i]['title'], $this->dashboardWidgets[$i]['callBack']);
        }
    }

    
    /*****************************************************
    Assets methods (Scripts and Styles)
    *****************************************************/
    
    public function registerStyle($id, $fileName, $loadOn = array(), $depends = array(), $version = false, $media = 'all') {
        $realId = $this->pluginBaseName . '_' . $id . '_style';
        $realFileName = $this->pluginBaseUrl . '/assets/styles/' . $fileName;
        $this->_registerStyle('plugin', $realId, $realFileName, $loadOn, $depends, $version, $media);
    }
    
    public function registerExternalStyle($id, $fileName, $loadOn = array(), $depends = array(), $version = false, $media = 'all') {
        $realId = $this->pluginBaseName . '_' . $id . '_style';
        $this->_registerStyle('external', $realId, $fileName, $loadOn, $depends, $version, $media);
    }
    
    public function registerBuiltInStyle($id, $loadOn = array(), $depends = array(), $version = false, $media = 'all') {
        $this->_registerStyle('builtIn', $id, null, $loadOn, $depends, $version, $media);
    }
    
    public function registerInlineStyle($code, $loadOn = array()) {
        $this->_registerStyle('inline', $code, null, $loadOn);
    }
    
    private function _registerStyle($type, $data, $fileName, $loadOn, $depends = array(), $version = false, $media = 'all') {
        array_push(
            $this->styles,
            array(
                'type' => $type,
                'fileName' => $fileName,
                'data' => $data,
                'loadOn' => $loadOn,
                'depends' => $depends,
                'version' => $version,
                'media' => $media
            )
        );
    }
    
    public function registerScript($id, $fileName, $loadOn = array(), $depends = array(), $version = false, $in_footer = false) {
        $realId = $this->pluginBaseName . '_' . $id . '_script';
        $realFileName = $this->pluginBaseUrl . '/assets/scripts/' . $fileName;
        $this->_registerScript('plugin', $realId, $realFileName, $loadOn, $depends, $version, $in_footer);
    }
    
    public function registerExternalScript($id, $fileName, $loadOn = array(), $depends = array(), $version = false, $in_footer = false) {
        $realId = $this->pluginBaseName . '_' . $id . '_script';
        $this->_registerScript('external', $realId, $fileName, $loadOn, $depends, $version, $in_footer);
    }
    
    public function registerBuiltInScript($id, $loadOn = array(), $depends = array(), $version = false, $in_footer = false) {
        $this->_registerScript('builtIn', $id, null, $loadOn, $depends, $version, $in_footer);
    }
    
    public function registerInlineScript($code, $loadOn = array(), $in_footer = false) {
        $this->_registerScript('inline', $code, null, $loadOn, array(), false, $in_footer);
    }
    
    private function _registerScript($type, $data, $fileName, $loadOn, $depends = array(), $version = false, $in_footer = false) {
        array_push(
            $this->scripts,
            array(
                'type' => $type,
                'fileName' => $fileName,
                'data' => $data,
                'loadOn' => $loadOn,
                'depends' => $depends,
                'version' => $version,
                'in_footer' => $in_footer
            )
        );
    }
    
    public function _printScripts() {
        global $beastxEnviroment;
        for ($i = 0; $i < count($this->scripts); ++$i) {
            $script = $this->scripts[$i];
            if (count($script['loadOn']) == 0 || $beastxEnviroment->checkIs($script['loadOn'])) {
                if ($script['type'] == 'inline') {
                    echo '<script type="text/javascript">' . $script['data'] . '</script>';
                } else {
                    wp_enqueue_script($script['data'], $script['fileName'], $script['depends'], $script['version'], $script['in_footer']);
                }
            }
        }
    }
    
    public function _printStyles() {
        global $beastxEnviroment;
        for ($i = 0; $i < count($this->styles); ++$i) {
            $style = $this->styles[$i];
            if (count($style['loadOn']) == 0 || $beastxEnviroment->checkIs($style['loadOn'])) {
                if ($style['type'] == 'inline') {
                    echo '<style>' . $style['data'] . '</style>';
                } else {
                    wp_enqueue_style($style['data'], $style['fileName'], $style['depends'], $style['version'], $style['media']);
                }
            }
        }
    }
    
    
    /*****************************************************
    Admin Menus Helper methods
    *****************************************************/
    
    
    public function registerSubMenu($id, $parentSlug, $menuTitle, $pageTitle, $showPageCallback, $capability = 'manage_options') {
        array_push(
            $this->subMenus,
            array(
                'id' => $id,
                'parentSlug' => $parentSlug,
                'title' => $menuTitle,
                'pageTitle' => $pageTitle,
                'capability' => $capability,
                'showPageCallback' => $showPageCallback
            )
        );
    }
    
    public function registerMenu($id, $menuTitle, $subMenus = array(), $capability = 'manage_options', $icon_url = null, $position = null) {
        array_push(
            $this->topMenus,
            array(
                'id' => $id,
                'title' => $menuTitle,
                'subMenus' => $subMenus,
                'capability' => $capability,
                'icon_url' => $icon_url,
                'position' => $position
            )
        );
    }
    
    public function _execMenuActions() {
        $this->_removeMenuItem();
        $this->_removeSubmenuItem();
        $this->_addMenuItems();
    }
    
    private function _addMenuItems() {
        for ($i = 0; $i < count($this->topMenus); ++$i) {
            $menuId = $this->topMenus[$i]['id'];
            add_menu_page(
                null,
                $this->topMenus[$i]['title'],
                $this->topMenus[$i]['capability'],
                $menuId,
                $this->topMenus[$i]['subMenus'][0]['callback']
            );
            add_submenu_page(
                $menuId,
                $this->topMenus[$i]['subMenus'][0]['title'],
                $this->topMenus[$i]['subMenus'][0]['title'],
                $this->topMenus[$i]['capability'],
                $menuId
            );
            $subMenus = $this->topMenus[$i]['subMenus'];
            for ($j = 1; $j < count($subMenus); ++$j) {
                add_submenu_page(
                    $menuId,
                    $subMenus[$j]['title'],
                    $subMenus[$j]['title'],
                    $this->topMenus[$i]['capability'],
                    $subMenus[$j]['id'],
                    $subMenus[$j]['callback']
                );
            }
            
            for ($i = 0; $i < count($this->subMenus); ++$i) {
                $parentMenuFiles = $this->getMenuFile($this->subMenus[$i]['parentSlug']);
                $subMenuId = $this->subMenus[$i]['id'];
                add_submenu_page(
                    $parentMenuFiles['menuFile'],
                    $this->subMenus[$i]['title'],
                    $this->subMenus[$i]['title'],
                    $this->subMenus[$i]['capability'],
                    $subMenuId,
                    $this->subMenus[$i]['showPageCallback']
                );
            }
        }
    }
    
    private function getMenuFile($menuSlug, $subMenuSlug = null) {
        global $beastxEnviroment;
        return $beastxEnviroment->getMenuFile($menuSlug, $subMenuSlug);
    }
    
    private function _removeSubmenuItem() {
        global $submenu;
        for ($i = 0; $i < count($this->subMenuItemsToRemove); ++$i) {
            $parentMenuFiles = $this->getMenuFile($this->subMenuItemsToRemove[$i][0], $this->subMenuItemsToRemove[$i][1]);
            foreach ($submenu[$parentMenuFiles['menuFile']] as $index => $menu) {
                if ($menu[2] == $parentMenuFiles['subMenuFile']) {
                    unset($submenu[$parentMenuFiles['menuFile']][$index]);
                }
            }
        }
    }
    
    private function _removeMenuItem() {
        global $menu;
        for ($i = 0; $i < count($this->menuItemsToRemove); ++$i) {
            $parentMenuFiles = $this->getMenuFile($this->menuItemsToRemove[$i]);
            for ($j = 0; $j < count($menu); ++$j) {
                if (!empty($menu[$j]) && $menu[$j][2] == $parentMenuFiles['menuFile']) {
                    unset($menu[$j]);
                }
            }
        }
    }
    
    public function removeMenuItem($menuSlug) {
        array_push($this->menuItemsToRemove, $menuSlug);
    }
    
    public function removeSubMenuItem($menuSlug, $subMenuSlug) {
        array_push($this->subMenuItemsToRemove, array($menuSlug, $subMenuSlug));
    }
    
}

}


?>