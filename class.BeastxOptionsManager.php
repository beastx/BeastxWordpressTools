<?

if (!class_exists('BeastxOptionsManager')) {

class BeastxOptionsManager {

    private $pluginName;
    private $options = array();
    private $initialOptions = array();
    private $lastValidationError;
    private $optionHelpers = array();

    public function __construct($pluginName) {
        $this->pluginName = $pluginName;
    }

    public function addNewOption($optionName, $optionLabel = null, $optionDescription = null, $optionDefaultValue = null, $optionValidators = array(), $customSetter = null, $customGetter = null) {
        $this->initialOptions[$optionName] = array(
            'name' => $optionName,
            'label' => $optionLabel,
            'description' => $optionDescription,
            'defaultValue' => $optionDefaultValue,
            'validators' => $optionValidators,
            'value' => $optionDefaultValue,
            'customSetter' => $customSetter,
            'customGetter' => $customGetter
        );
        if (!empty($customSetter) || !empty($customGetter)) {
            $this->registerOptionHelper($optionName, $customSetter, $customGetter);
        }
    }
    
    public function registerInitialOptions() {
        $this->options = $this->initialOptions;
        foreach ($this->options as $optionName => $option) {
            if (!empty($option['customSetter'])) {
                $this->executeCutomSetter($optionName, $optionValue);
            }
        }
        update_option($this->pluginName . '_options', json_encode($this->initialOptions));
    }
    
    public function updateOptions() {
        update_option($this->pluginName . '_options', json_encode($this->options));
    }
    
    public function resetOptions() {
        foreach($this->options as $optionName => $option) {
            if (!empty($this->options[$optionName]['customGetter'])) {
                $this->options[$optionName]['value'] = $this->executeCutomGetter($optionName);
            } else {
                $this->options[$optionName]['value'] = $this->options[$optionName]['defaultValue'];
            }
        }
        $this->updateOptions();
    }
    
    public function get($optionName) {
        return $this->options[$optionName];
    }
    
    public function isValid($optionName, $optionValue, $validator) {
        $this->lastValidationError = 'pepe';
        return true;
    }
    
    public function validateOption($optionName, $optionValue) {
        $option = $this->get($optionName);
        $validators = $option['validators'];
        for ($i = 0; $i < count($validators); ++$i) {
            if (!$this->isValid($optionName, $optionValue, $validators[$i])) {
                return false;
            }
        }
        return true;
    }
    
    public function set($optionName, $optionValue, $validate = true) {
        if ($validate) {
            if ($this->validateOption($optionName, $optionValue)) {
                return false;
            }
        }
        if (!empty($this->options[$optionName]['customSetter'])) {
            $this->executeCutomSetter($optionName, $optionValue);
        } else {
            $this->options[$optionName]['value'] = $optionValue;
        }
        return true;
    }
    
    public function getLastValidationError() {
        return $this->lastValidationError;
    }
    
    public function registerOptionHelper($optionName, $setter, $getter) {
        array_push(
            $this->optionHelpers,
            array(
                'optionNname' => $optionName,
                'setter' => $setter,
                'getter' => $getter
            )
        );
    }
    
    public function readOptions() {
        $this->options = json_decode(get_option($this->pluginName . '_options'), true);
        //~ $this->optionHelpers
    }
    
    private function executeCutomSetter($optionName, $value) {
        call_user_func_array(
            $this->options[$optionName]['customSetter'],
            array($value)
        );
    }
    
    private function executeCutomGetter($optionName) {
        return call_user_func($this->options[$optionName]['customGetter']);
    }
}

}

?>