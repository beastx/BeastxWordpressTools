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
        $this->pluginBaseName = str_replace(' ', '-', $this->pluginName);
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
        $this->addFilter('pre_get_posts', '_onGetPosts' );
        $this->addAction('admin_print_scripts', '_printScripts');
        $this->addAction('admin_print_styles', '_printStyles');
        $this->addFilter('plugin_action_links_' . $this->pluginBaseFileName, '_addPluginActionLink');
        $this->addFilter('plugin_row_meta', '_addPluginMetaLink');
        $this->addAction('admin_menu', '_addMenuItems');
        
        //~ $this->addFilter('manage_edit-' . $this->postType . '_columns', 'customPostColumns');
        //~ $this->addAction('manage_posts_custom_column', 'custonPostRowValues');
    }
    
    
    /*****************************************************
    Plugin Links Helpers (links for plugin in plugins page)
    *****************************************************/
    
    function _addPluginActionLink($links) {
        if (method_exists($this, 'getPluginActionLinks')) {
            $actionsLinks = $this->getPluginActionLinks();
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
    
    function _onInit() { if (method_exists($this, 'onInit')) { $this->onInit(); } }
    function _onAdminInit() { if (method_exists($this, 'onAdminInit')) { $this->onAdminInit(); } }
    function _onPluginLoad() { if (method_exists($this, 'onPluginLoad')) { $this->onPluginLoad(); } }
    function _onPluginActivate() { if (method_exists($this, 'onPluginActivate')) { $this->onPluginActivate(); } }
    function _onPluginDeactivate() { if (method_exists($this, 'onPluginDeactivate')) { $this->onPluginDeactivate(); } }
    function _onSavePost() { if (method_exists($this, 'onSavePost')) { $this->onSavePost(); } }
    function _onGetContent() { if (method_exists($this, 'onGetContent')) { $this->onGetContent(); } }
    function _onGetPosts() { if (method_exists($this, 'onGetPosts')) { $this->onGetPosts(); } }
    
    
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
    
    
    public function registerPostType($postType, $postTypeArgs, $dontReflushRules = false) {
        register_post_type($postType, $postTypeArgs);
        if (!$dontReflushRules) {
            global $wp_rewrite;
            $wp_rewrite->flush_rules();
        }
    }
    
    public function addMetaBox($id, $label, $callBack, $postType, $column= 'normal', $priority = 'normal') {
        add_meta_box($id, $label, $callBack, $postType, $column, $priority);
    }
    
    public function addShortCode($shortCode, $callBack) {
        
    }
    
    public function addCustomHelp() {
        //~ add_action('load-page-new.php','add_custom_help_page');
        //~ add_action('load-page.php','add_custom_help_page');
        //~ function add_custom_help_page() {
            //~ //the contextual help filter
            //~ add_filter('contextual_help','custom_page_help');
        //~ }
        //~ function custom_page_help($help) {
            //~ //keep the existing help copy
            //~ echo $help;
            //~ //add some new copy
            //~ echo "<h5>Custom Features</h5>";
            //~ echo "<p>Content placed above the more divider will appear in column 1. Content placed below the divider will appear in column 2.</p>";
        //~ }
    }
    
    public function addCustomFavoriteOption() {
        //~ function custom_favorite_menu($actions) {
            //~ # Removing #
            //~ unset($actions['edit-comments.php']);
            //~ unset($actions['media-new.php']);
            //~ # Adding #
            //~ $actions['admin.php?your-plugin'] = array('Your Plugin', 'manage_options');
            //~ return $actions;
        //~ }
        //~ add_filter('favorite_actions', 'custom_favorite_menu');
    }
    
    public function addDashboardWidget() {
        //~ function your_dashboard_widget() {
            //~ echo '<p>Fill this with HTML or PHP.</p>';
        //~ };
        //~ function add_your_dashboard_widget() {
            //~ wp_add_dashboard_widget( 'your_dashboard_widget', __( 'Hello WordPress user!' ), 'your_dashboard_widget' );
        //~ }
        //~ add_action('wp_dashboard_setup', 'add_your_dashboard_widget' );
    }
    
    
    /*****************************************************
    Assets methods (Scripts and Styles)
    *****************************************************/
    
    public function registerStyle($id, $fileName, $loadOn = array(), $depends = array(), $version = false, $media = 'all') {
        $realId = $this->pluginBaseName . '_' . $id . '_style';
        $realFileName = WP_PLUGIN_URL . '/assets/styles/' . $fileName;
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
        $realFileName = WP_PLUGIN_URL . '/assets/scripts/' . $fileName;
        $this->_registerScript('plugin', $realId, $realFileName, $loadOn, $depends, $version, $media);
    }
    
    public function registerExternalScript($id, $fileName, $loadOn = array(), $depends = array(), $version = false, $in_footer = false) {
        $realId = $this->pluginBaseName . '_' . $id . '_script';
        $this->_registerScript('external', $realId, $fileName, $loadOn, $depends, $version, $media);
    }
    
    public function registerBuiltInScript($id, $loadOn = array(), $depends = array(), $version = false, $in_footer = false) {
        $this->_registerScript('builtIn', $id, null, $loadOn, $depends, $version, $media);
    }
    
    public function registerInlineStyle($code, $loadOn = array(), $in_footer = false) {
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
    
    public function _printScript() {
        for ($i = 0; $i < count($this->scripts); ++$i) {
            $script = $this->scripts[$i];
            if (empty($script['loadOn'])) {
                if ($script['type'] == 'inline') {
                    echo '<script type="text/javascript">' . $script['data'] . '</script>';
                } else {
                    wp_enqueue_script($script['data'], $script['fileName'], $script['depends'], $script['version'], $script['in_footer']);
                }
            }
        }
    }
    
    public function _printStyle() {
        for ($i = 0; $i < count($this->styles); ++$i) {
            $style = $this->styles[$i];
            if (empty($style['loadOn'])) {
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
    
    public function _addMenus($menuItems) {
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