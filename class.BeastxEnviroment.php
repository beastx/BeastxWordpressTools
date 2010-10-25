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


//~ posible values:
        //~     - enviroment: public|admin
        //~     - postType: postType|null
        //~     - section: deshboard|posts(post, pages, custom)|media|comments|appearance|plugins|users|tools|settings|custom (only in enviroment: admin)
        //~     - subsection: pageName into the section (only in enviroment: admin)
        

Class BeastxEnviroment {

    private $adminSections = array(
        'admin-ajax' => array('ajax', null),
        'index-extra.php' => array('ajax', 'dashboardwidget'),
        'index.php' => array('dashboard', 'dashboard'),
        'update-core.php' => array('dashboard', 'updated'),
        'edit.php' => array('posts', 'posts'),
        'post-new.php' => array('posts', 'addnew'),
        'post.php' => array('posts', 'edit'),
        'edit-tags.php' => array('posts', 'taxonomy'),
        'upload.php' => array('media', 'library'),
        'media-new.php' => array('media', 'addnew'),
        'link-manager.php' => array('links', 'links'),
        'link-add.php' => array('links', 'addnew'),
        'edit-link-categories.php' => array('links', 'categories'),
        'edit-comments.php' => array('comments', 'comments'),
        'themes.php' => array('appearance', 'themes'),
        'widgets.php' => array('appearance', 'widgets'),
        'nav-menus.php' => array('appearance', 'menus'),
        'theme-editor.php' => array('appearance', 'editor'),
        'plugins.php' => array('plugins', 'plugins'),
        'plugin-install.php' => array('plugins', 'addnew'),
        'plugin-editor.php' => array('plugins', 'editor'),
        'users.php' => array('users', 'users'),
        'user-new.php' => array('users', 'addnew'),
        'profile.php' => array('users', 'profile'),
        'tools.php' => array('tools', 'tools'),
        'import.php' => array('tools', 'import'),
        'export.php' => array('tools', 'export'),
        'options-general.php' => array('settings', 'general'),
        'options-writing.php' => array('settings', 'writing'),
        'options-reading.php' => array('settings', 'reading'),
        'options-discussion.php' => array('settings', 'discussion'),
        'options-media.php' => array('settings', 'media'),
        'options-privacy.php' => array('settings', 'privacy'),
        'options-permalink.php' => array('settings', 'permalink')
    );
    private $actualEnviroment = array();

    public function __construct() {
        if (is_admin()) {
            $this->getAdminEnviroment();
        } else {
            add_filter('pre_get_posts', array(&$this, 'getPublicEnviroment'));
        }
    }
        
        
    public function getPublicEnviroment() {
        global $wp_query;
        $postType = null;
        $subSection = null;
        
        if (is_home()) {
            $section = 'home';
        } else if (is_front_page()) {
            $section = 'frontpage';
        } else if (is_single()) {
            $section = 'single';
            $postType = get_post_type($post->ID);
        } else  if (is_author()) {
            $section = 'author';
            $subSection = $wp_query->query_vars['author_name'];
        } else if (is_page()) {
            $section = 'page';
            $subSection = $wp_query->query_vars['page_name'];
        } else if (is_archive()) {
            $section = 'archive';
            if (is_category()) {
                $subSection = 'category';
            } else if (is_tag()) {
                $subSection = 'tag';
            } else if (is_tax()) {
                $subSection = 'taxonomy';
            } else if (is_date()) {
                $subSection = 'date';
            }
        } else if (is_search()) {
            $section = 'search';
        } else  if (is_preview()) {
            $section = 'preview';
        } else  if (is_404()) {
            $section = '404';
        } 
        
        $this->actualEnviroment = array(
            'enviroment' => 'public',
            'section' => $section,
            'subSection' => $subSection,
            'postType' => $postType
        );
    }
        
    private function getAdminEnviroment() {
        global $pagenow;
        $section = $this->adminSections[$pagenow][0];
        $subSection = $this->adminSections[$pagenow][1];
        $postType = ($section == 'posts') ? !empty($_REQUEST['post_type']) ? $_REQUEST['post_type'] : get_post_type($_REQUEST['post']) : null;
        $this->actualEnviroment = array(
            'enviroment' => 'admin',
            'section' => $section,
            'subSection' => $subSection,
            'postType' => $postType
        );
    }
    
    public function getActualEnviroment() {
        return $this->actualEnviroment;
    }
    
    public function checkIs($data) {
        for ($i = 0; $i < count($data); ++$i) {
            if ($this->check($data[$i])) {
                return true;
            }
        }
        return false;
    }
    
    public function check($data) {
        if (empty($data['enviroment']) || ($data['enviroment'] == $this->actualEnviroment['enviroment'])) {
            if (empty($data['section']) || ($data['section'] == $this->actualEnviroment['section'])) {
                if (empty($data['subSection']) || ($data['subSection'] == $this->actualEnviroment['subSection'])) {
                    if (empty($data['postType']) || ($data['postType'] == $this->actualEnviroment['postType'])) {
                        return true;
                    } else {
                        return false;
                    }
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
    
}

$beastxEnviroment = new BeastxEnviroment();

?>