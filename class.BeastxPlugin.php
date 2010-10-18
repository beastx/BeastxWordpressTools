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

function debug($var, $title = null) {
    $firephp = FirePHP::getInstance(true);
    $firephp->log($var, $title);
}

if (!defined('ABSPATH')) die("Aren't you supposed to come here via WP-Admin?");

Class BeastxPlugin {

    private $optionsHelpers = array();
    
    public function __construct() {
        $this->pluginBaseName = str_replace(' ', '-', $this->pluginName);
        $this->actualPage = $this->getActualPluginPage();

        register_activation_hook($this->pluginBaseFileName, array($this, '_onPluginActivate'));
        register_deactivation_hook($this->pluginBaseFileName, array($this, '_onPluginDeactivate'));
        
        $this->addAction('init', '_onInit');
        $this->addAction('admin_init', '_onAdminInit');
        $this->addAction('plugins_loaded', '_onPluginLoad');
    }
    
    /*****************************************************
    Events Handlers
    *****************************************************/
    
    function _onInit() {
        if (method_exists($this, 'onInit')) {
            $this->onInit();
        }
        if (method_exists($this, 'addScripts')) {
            add_action('admin_print_scripts', array(&$this, 'addScripts'));
        }
        if (method_exists($this, 'addStyles')) {
            add_action('admin_print_styles', array(&$this, 'addStyles'));
        }
    }
    
    function _onAdminInit() {
        if (method_exists($this, 'onAdminInit')) {
            $this->onAdminInit();
        }
    }
    
    function _onPluginLoad() {
        if (method_exists($this, 'onPluginLoad')) {
            $this->onPluginLoad();
        }
        if (!empty($this->adminMenuOptions)) {
            $this->addAction('admin_menu', 'addAdminMenu');
        }
        if (!empty($this->actionsLinks)) {
            $this->addPluginActionLink();
        }
        if (!empty($this->metaLinks)) {
            $this->addPluginMetaLink();
        }
        $this->readOptions();
    }
    
    function _onPluginActivate() {
        if (method_exists($this, 'onPluginActivate')) {
            $this->onPluginActivate();
        }
    }
    
    function _onPluginDeactivate() {
        if (method_exists($this, 'onPluginDeactivate')) {
            $this->onPluginDeactivate();
        }
    }
    
    /*****************************************************
    Events Handlers
    *****************************************************/
    
    public function addAction($action, $methodName, $priority = 10, $parameters = 2) {
        add_action($action, array(&$this, $methodName), $priority, $parameters);
    }
    
    public function addFilter($action, $methodName, $priority = 10, $parameters = 2) {
        add_filter($action, array(&$this, $methodName), $priority, $parameters);
    }
    
    

    public function addPluginActionLink() {
        $this->addFilter('plugin_action_links_' . $this->pluginBaseFileName, '_addPluginActionLink');
    }
    
    public function addPluginMetaLink() {
        $this->addFilter('plugin_row_meta', '_addPluginMetaLink');
    }
    
    
    
    function _addPluginActionLink($links) {
        $newLinks = array();
        for ($i = 0; $i < count($this->actionsLinks); ++$i) {
            $metaLink = '<a href="'. $this->actionsLinks[$i]['url'] .'">'. $this->actionsLinks[$i]['label'] .'</a>';
            array_push($newLinks, $metaLink);
        }
        return array_merge($newLinks, $links);
    }  
    
    
    
    function _addPluginMetaLink($links, $file) {
        if ($file == $this->pluginBaseFileName) {
            for ($i = 0; $i < count($this->metaLinks); ++$i) {
                $metaLink = ($i == 0 ? '<br>' : '') . '<a style="color: black; font-weight: bold;" href="'. $this->metaLinks[$i]['url'] .'">'. $this->metaLinks[$i]['label'] .'</a>';
                array_push($links, $metaLink);
            }
        }
        return $links;
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
    
    
    
    
    
    
    
    
    
    
    
    /*****************************************************
    MySql helpers
    *****************************************************/    
    
    
    public function createSqlTables() {
        global $wpdb;
        require_once $this->pluginBasePath . '/dbSchema.php';
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        for ($i = 0; $i < count($BeastxWPProjectsDBSchema); ++$i) {
            $sql = "CREATE TABLE IF NOT EXISTS ";
            $sql.= $wpdb->prefix . str_replace('-', '', $this->pluginBaseName) . "_" . $BeastxWPProjectsDBSchema[$i]['tableName'];
            $sql.= " ( " . $BeastxWPProjectsDBSchema[$i]['schema'] . " )";
            dbDelta($sql);
        }
    }
    
    public function deleteSqlTables() {
        global $wpdb;
        require_once $this->pluginBasePath . '/dbSchema.php';
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        for ($i = 0; $i < count($BeastxWPProjectsDBSchema); ++$i) {
            $sql = "DROP TABLE IF EXISTS ";
            $sql.= $wpdb->prefix . str_replace('-', '', $this->pluginBaseName) . "_" . $BeastxWPProjectsDBSchema[$i]['tableName'];
            $wpdb->query($sql);
        }
    }
    
    
    /*****************************************************
    Folders helpers
    *****************************************************/
    
    
    public function createPluginFolders() {
        foreach ($this->folders as $folderId => $folder) {
                $this->mkdirr($folder);
            }
        }
    
    public function mkdirr($pathname) { // Path absoluto desde el folder content del wordpress..
        $pathname = WP_CONTENT_DIR . $pathname;
        $this->_mkdirr($pathname, 0777);
    }
    
    private function _mkdirr($pathname, $mode) {
        if (is_dir($pathname) || empty($pathname)) { return true; } // Check if directory already exists
        if (is_file($pathname)) { return false; } // Ensure a file does not already exist with the same name
        $next_pathname = substr($pathname, 0, strrpos($pathname, DIRECTORY_SEPARATOR));
        if ($this->_mkdirr($next_pathname, $mode)) {
            if (!file_exists($pathname)) {
                $rtn = mkdir($pathname, $mode);
                return $rtn;
            }
        }
        return false;
    }
    
    
    /*****************************************************
    Plugin Options Helpers
    *****************************************************/

    
    public function registerDefaultOptions() {
        $oldOptions = get_option($this->pluginBaseName . '_options');
        if (empty($oldOptions)) {
            require_once $this->pluginBasePath . '/options.php';
            $options = array();
            foreach ($BeastxWPProjectsOptions as $sectionId => $section) {
                if ($BeastxWPProjectsOptions[$sectionId]['type'] == 'section') {
                    foreach ($BeastxWPProjectsOptions[$sectionId]['options'] as $subSectionId => $subSection) {
                        $BeastxWPProjectsOptions[$sectionId]['options'][$subSectionId]['value'] = $BeastxWPProjectsOptions[$sectionId]['options'][$subSectionId]['defaultValue'];
                    }
                } else {
                    $BeastxWPProjectsOptions[$sectionId]['value'] = $BeastxWPProjectsOptions[$sectionId]['defaultValue'];
                }
            }
            update_option($this->pluginBaseName . '_options', json_encode($BeastxWPProjectsOptions)); // ver si hacer update or add....
            
            foreach ($this->optionsHelpers as $optionId => $helper) {
                $values = call_user_func(
                    array(&$this, $helper['defaultValue'])
                );
                call_user_func_array(
                    array(&$this, $helper['setter']),
                    array($values)
                );
            }
        }
        $this->readOptions();
    }
    
    private function readOptions() {
        $this->options = json_decode(get_option($this->pluginBaseName . '_options'), true);
        foreach ($this->optionsHelpers as $optionId => $helper) {
            $this->options[$optionId]['options'][$optionId]['value'] = call_user_func(array(&$this, $helper['getter'])); // TODO: ver esto.. el helper todavia no conoce de secciones dentro de las options
        }
    }
    
    public function saveOptions($options) {
        foreach ($this->optionsHelpers as $optionId => $helper) {
            call_user_func_array(
                array(&$this, $helper['setter']),
                array($options[$optionId]['options'][$optionId]['value'])
            );
        }
        $options[$optionId]['options'][$optionId]['value'] = array();
        update_option($this->pluginBaseName . '_options', json_encode($options));
        $this->readOptions();
    }
    
    private function resetOptions() {
        $this->options = $this->getDefaultOptions();
        $optionNames = array();
        for ($i = 0; $i < count($this->options); ++$i) {
            update_option($this->pluginBaseName . '_' . $this->options[$i]['id'], json_encode($this->options[$i]['value']));
            array_push($optionNames, $this->options[$i]['id']);
        }
        update_option($this->pluginBaseName . '_options', json_encode($optionNames));
    }
    
    public function getOption($optionType, $optionName = null) {
        if (empty($optionName)) {
            return $this->options[$optionType];
        } else {
            return $this->options[$optionType]['options'][$optionName];
        }
    }
    
    public function getOptionValue($optionType, $optionName = null) {
        $option =  $this->getOption($optionType, $optionName);
        return $option['value'];
    }
    
    public function getOptions() {
        return $this->options;
    }
    
    public function registerOptionHelper($optionName, $setter, $getter, $defaultValue) {
        $this->optionsHelpers[$optionName] = array(
            'setter' => $setter,
            'getter' => $getter,
            'defaultValue' => $defaultValue
        );
    }
    
}

?>