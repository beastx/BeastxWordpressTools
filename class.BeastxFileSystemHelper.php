<?

class BeastxFileSystemHelper {

    public function getFileContent($fileName) {
    }

    public function getFileInfo($fileName) {
    }

    public function moveFile($sourceFileName, $destFileName) {
    }
    
    public function copyFile($sourceFileName, $destFileName) {
    }
    
    public function deleteFile($fileName) {
    }
    
    public function createFile($fileName, $mode = 0777) {
    }
    
    public function writeToFile($fileName, $content, $append = true, $createIfNotExists = true) {
    }
    
    public function deleteFolder($folderName, $forceIsNotEmpty = true) {
    }
    
    public function createFolder($folderName, $mode = 0777) { // Path absoluto desde el folder content del wordpress..
        $pathname = WP_CONTENT_DIR . $folderName;
        BeastxFileSystemHelper::_mkdirr($pathname, $mode);
    }
    
    public function getPluginFolder() {
        $baseName = plugin_basename(__FILE__);
        return substr($baseName, 0, strpos($baseName, '/'));
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