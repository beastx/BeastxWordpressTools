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

    private $favoriteMenuActionsToAdd = array();
    private $favoriteMenuActionsToRemove = array();
    private $styles = array();
    private $scripts = array();
    private $dashboardWidgets = array();
    private $contextualHelps = array();
    private $pluginActionLinks = array();
    private $pluginMetaLinks = array();

    public function __construct() {
        $this->pluginBaseName = BeastxFileSystemHelper::getPluginFolder();
        $this->pluginBaseFileName = $this->pluginBaseName . '/' .$this->pluginBaseName . '.php';
        $this->pluginBaseUrl = WP_PLUGIN_URL.'/'.$this->pluginBaseName;
        $this->pluginBasePath = WP_PLUGIN_DIR.'/'.$this->pluginBaseName;
        $this->assetsPath = $this->pluginBaseUrl . '/assets/';
        $this->addWordpressCommonFiltersAndActions();
    }
    
    private function addWordpressCommonFiltersAndActions() {
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
        $this->addAction('admin_menu', '_addMenuItems');
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
    public function _onSavePost() { if (method_exists($this, 'onSavePost')) { $this->onSavePost(); } }
    public function _onGetContent() { if (method_exists($this, 'onGetContent')) { $this->onGetContent(); } }
    public function _onGetPosts() { if (method_exists($this, 'onGetPosts')) { $this->onGetPosts(); } }
    
    
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
        $this->_registerScript('plugin', $realId, $realFileName, $loadOn, $depends, $version, $media);
    }
    
    public function registerExternalScript($id, $fileName, $loadOn = array(), $depends = array(), $version = false, $in_footer = false) {
        $realId = $this->pluginBaseName . '_' . $id . '_script';
        $this->_registerScript('external', $realId, $fileName, $loadOn, $depends, $version, $media);
    }
    
    public function registerBuiltInScript($id, $loadOn = array(), $depends = array(), $version = false, $in_footer = false) {
        $this->_registerScript('builtIn', $id, null, $loadOn, $depends, $version, $media);
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
    
    
    public function registerMenuItem($menuItem) {
        //~ add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function);
    }
    
    public function registerMenu($menu) {
    
    }
    
    public function _addMenuItems() {
        //~ add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position );
        
        //~ add_submenu_page(
            //~ $this->pluginBaseFileName,
            //~ $this->pluginName . ' - ' . $this->adminMenuOptions['subOptions'][0]['title'],
            //~ $this->adminMenuOptions['subOptions'][0]['link'],
            //~ 8,
            //~ $this->pluginBaseFileName,
            //~ array(&$this, 'get' . mb_convert_case($this->adminMenuOptions['subOptions'][0]['id'], MB_CASE_TITLE))
        //~ );
        //~ $this->adminPage = add_menu_page(
            //~ $this->pluginName . ' - ' . $this->adminMenuOptions['title'],
            //~ $this->adminMenuOptions['title'],
            //~ 8,
            //~ $this->pluginBaseFileName,
            //~ array(&$this, 'get' . mb_convert_case($this->adminMenuOptions['subOptions'][0]['id'], MB_CASE_TITLE))
        //~ );
        
        //~ if (count($this->adminMenuOptions['subOptions']) > 1) {
            //~ for ($i = 1; $i < count($this->adminMenuOptions['subOptions']); ++$i) {
                //~ add_submenu_page(
                    //~ $this->pluginBaseFileName,
                    //~ $this->pluginName . ' - ' . $this->adminMenuOptions['subOptions'][$i]['title'],
                    //~ $this->adminMenuOptions['subOptions'][$i]['link'],
                    //~ 8,
                    //~ $this->pluginBaseName . '-' . $this->adminMenuOptions['subOptions'][$i]['id'],
                    //~ array(&$this, 'get' . mb_convert_case($this->adminMenuOptions['subOptions'][$i]['id'], MB_CASE_TITLE))
                //~ );
            //~ }
        //~ }
    }
    
    public function removeSubmenuItem() {
        //~ function remove_submenus() {
            //~ global $submenu;
            //~ unset($submenu['index.php'][10]); // Removes 'Updates'.
            //~ unset($submenu['themes.php'][5]); // Removes 'Themes'.
            //~ unset($submenu['options-general.php'][15]); // Removes 'Writing'.
            //~ unset($submenu['options-general.php'][25]); // Removes 'Discussion'.
        //~ }
        //~ add_action('admin_menu', 'remove_submenus');
    }
    
    public function removeMenuItem() {
        //~ function remove_menu_items() {
            //~ global $menu;
            //~ unset($menu[15]); // Removes 'Links'.
            //~ unset($menu[25]); // Removes 'Comments'.
        //~ }
        //~ add_action('admin_menu', 'remove_menu_items');
    }
    
    
    
    
    
    
    
    
    public function uninstall() {
        
    }
    
    
    
    
    
}


?>