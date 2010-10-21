<?

class BeastxFilesHelper {
    
    public function mkdir($pathname) { // Path absoluto desde el folder content del wordpress..
        $pathname = WP_CONTENT_DIR . $pathname;
        BeastxFilesHelper::_mkdirr($pathname, 0777);
    }
    
    private function _mkdir($pathname, $mode) {
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

}

?>