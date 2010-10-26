<?php
/*
Name: BeastxCustomPostType Helper Class
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

Class BeastxCustomPostType extends BeastxPlugin {
    
    private $postType = null;
    private $postTypeArgs = null;
    private $dontReflushRules = null;
    private $metaBoxes = array();
    private $vars = array();

    public function registerPostType($postType, $postTypeArgs = null, $dontReflushRules = false) {
        $this->postType = strtolower($postType);
        $this->postTypeArgs = $postTypeArgs;
        $this->dontReflushRules = $dontReflushRules;
    }
    
    public function registerPostTypeVar($varName, $defaultValue = null, $varType = 'string') {
        array_push($this->vars, array('varName' => $varName, 'defaultValue' => $defaultValue, 'varType' => $varType));
    }
    
    public function onInit() {
        if (empty($this->postType)) {
            die('FALTO REGISTRAR EL POST TYPE');
        }
        $postTypeArgs = empty($this->postTypeArgs) ? $this->getDefaultArgs() : $this->postTypeArgs;
        register_post_type($this->postType, $postTypeArgs);
        $this->reflushRewriteRules();
        if (method_exists($this, 'customPostColumns')) {
            $this->addFilter('manage_edit-' . $this->postType . '_columns', 'customPostColumns');
        }
        if (method_exists($this, 'custonPostRowValues')) {
            $this->addAction('manage_posts_custom_column', 'custonPostRowValues');
        }
        
    }
    
    public function onAdminInit() {
        $this->_addMetaBoxes();
    }
    
    private function reflushRewriteRules() {
        if (!$this->dontReflushRules) {
            global $wp_rewrite;
            $wp_rewrite->flush_rules();
        }
    }
    
    private function getDefaultArgs() {
        return array(
            'label' => ucfirst($this->postType),
            'public' => true,
            'show_ui' => true,
            'publicly_queryable' => true,
            'exclude_from_search' => false,
            'menu_position' => 5,
            'hierarchical' => false,
            'query_var' => true,
            'can_export' => true
        );
    }
    
    public function addMetaBox($label, $column= 'normal', $priority = 'low') {
        $id = str_replace(' ', '-', strtolower($label));
        debug($id);
        if (method_exists($this, $id . 'MetaBox')) {
            array_push(
                $this->metaBoxes,
                array(
                    'id' => $id,
                    'label' => $label,
                    'column' => $column,
                    'priority' => $priority
                )
            );
        } else {
            die('FALTA EL METABOX CALLBACK');
        }
    }
    
    public function _addMetaBoxes() {
        for ($i = 0; $i < count($this->metaBoxes); ++$i) {
            $metaBox = $this->metaBoxes[$i];
            add_meta_box($this->postType . '_' . $metaBox['id'], $metaBox['label'], array(&$this, $metaBox['id'] . "MetaBox"), $this->postType, $metaBox['column'], $metaBox['priority']);
        }
    }
    
    public function onSavePost($id) {
        global $post;
        if (wp_is_post_revision($id) || wp_is_post_autosave($id)) {
            return;
        }
        for ($i = 0; $i < count($this->vars); ++$i) {
            update_post_meta(
                $post->ID,
                $this->getInputName($this->vars[$i]['varName']),
                $_POST[$this->getInputName($this->vars[$i]['varName'])]
            );
        }
    }
    
    public function getRegisterVar($varName) {
        for ($i = 0; $i < count($this->vars); ++$i) {
            if ($this->vars[$i]['varName'] == $varName) {
                return $this->vars[$i];
            }
        }
    }
    
    public function getInputName($varName) {
        return $this->postType . '_' . $varName;
    }
    
    public function getVarValue($varName) {
        global $post;
        $custom = get_post_custom($post->ID);
        $var = $this->getRegisterVar($varName);
        if (empty($custom[$this->getInputName($varName)][0])) {
            return $var['defaultValue'];
        } else {
            switch ($var['varType']) {
                case 'string':
                    return $custom[$this->getInputName($varName)][0];
                    break;
                case 'list':
                    return json_decode(stripcslashes($custom[$this->getInputName($varName)][0]), true);
                    break;
                default:
                    return $custom[$this->getInputName($varName)][0];
                    break;
            }
        }
    }

}

?>