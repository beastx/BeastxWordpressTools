<?

if (!class_exists('BeastxInputs')) {

Class BeastxInputs {

    function getTextInput($input) {
        $returnValue = "<div>";
        if (!empty($input['label'])) { $returnValue.= '<label for="' . $input['name'] . '">' . $input['label'] . '</label>'; }
        $returnValue.= '<input type="text" value="' . $input['value'] . '" name="' . $input['name'] . '" id="' . $input['name'] . '" size="40" />';
        return $returnValue . "</div>\n";
    }

    function getFileInput($input) {
        $returnValue = "<div>";
        if (!empty($input['label'])) { $returnValue.= '<label for="' . $input['name'] . '">' . $input['label'] . '</label>'; }
        $returnValue.= '<input type="text" id="' . $input['name'] . '" name="' . $input['name'] . '" value="' . $input['value'] . '" class="text">';
        $returnValue.= '<input type="button" onclick="show_image_uploader(\'' . $input['name'] . '\');" value="' . __('Select Image', 'beastxTheme') . '" class="button">';
        return $returnValue . "</div>\n";
    }

    function getImageInput($input) {
        $returnValue = "<div>";
        if (!empty($input['label'])) { $returnValue.= '<label for="' . $input['name'] . '">' . $input['label'] . '</label>'; }
        $returnValue.= '<input type="text" id="' . $input['name'] . '" name="' . $input['name'] . '" value="' . $input['value'] . '" class="text">';
        $returnValue.= '<input type="button" onclick="show_image_uploader(\'' . $input['name'] . '\');" value="' . __('Select Image', 'beastxTheme') . '" class="button">';
        if (!empty($input['value'])) {
            $returnValue.= '<input type="button" onclick="reset_image_Input(\'' . $input['name'] . '\');" value="' . __('Remove Image', 'beastxTheme') . '" class="button">';
            $imgSize= getimagesize($input['value']);
            $height = $imgSize[1] > 80 ? 80 : $imgSize[1];
            $returnValue.= '<br>';
            $returnValue.= '&nbsp;&nbsp;<a id="' . $input['name'] . '_img" href="'.$input['value'].'" class="thickbox"><img style="height:' . $height . 'px; border: 1px solid #aaa;" src="'.$input['value'].'" alt=""/></a>';
        }
        return $returnValue . "</div>\n";
    }

    function getCheckInput($input) {
        $returnValue = "<div>";
        $returnValue.= '<input type="checkbox" ' . ($input['value'] ? 'checked="checked" ' : '') . 'name="' . $input['name'] . '" id="' . $input['name'] . '" />';
        if (!empty($input['label'])) { $returnValue.= '&nbsp;<label for="' . $input['name'] . '">' . $input['label'] . '</label>'; }
        if (!empty($input['extraOptions'])) { $returnValue.= '&nbsp;<a href="#" onclick="alert(\'pepe\'); return false;">Config</a>'; }
        return $returnValue . "</div>\n";
    }

    function getRadioInput($input) {
        $returnValue = "<div>";
        if (!empty($input['label'])) { $returnValue.= '<label for="' . $input['name'] . '">' . $input['label'] . '</label>'; }
        $returnValue.= '<input type="text" value="' . $input['value'] . '" name="' . $input['name'] . '" id="' . $input['name'] . '" />';
        return $returnValue . "</div>\n";
    }

    function getComboInput($input) {
        $returnValue = "<div>";
        if (!empty($input['label'])) { $returnValue.= '<label for="' . $input['name'] . '">' . $input['label'] . '</label>'; }
        $returnValue.= '<input type="text" value="' . $input['value'] . '" name="' . $input['name'] . '" id="' . $input['name'] . '" />';
        return $returnValue . "</div>\n";
    }

    function getSeparator() {
        return $returnValue . "<div class=\"inputSeparator\">&nbsp;</div>\n";
    }

    function getInputList($input) {
        $returnValue = '';
        for ($i = 0; $i < count($input['inputs']); ++$i) {
            $returnValue .= BeastxInputs::getInputByInputType($input['inputs'][$i]) . "\n";
        }
        return $returnValue;
    }
    
    function getInputByInputType($input) {
        switch ($input['type']) {
            case 'text':
                return BeastxInputs::getTextInput($input);
                break;
            case 'file':
                return BeastxInputs::getFileInput($input);
                break;
            case 'image':
                return BeastxInputs::getImageInput($input);
                break;
            case 'checkbox':
                return BeastxInputs::getCheckInput($input);
                break;
            case 'radio':
                return BeastxInputs::getRadioInput($input);
                break;
            case 'combo':
                return BeastxInputs::getComboInput($input);
                break;
            case 'inputList':
                return BeastxInputs::getInputList($input);
                break;
            case 'separator':
                return BeastxInputs::getSeparator();
                break;
            default:
                return BeastxInputs::getTextInput($input);
                break;
        }
    }
}

}
?>