<?php

abstract class Nip_Form_Abstract
{
    const ENCTYPE_URLENCODED = 'application/x-www-form-urlencoded';
    const ENCTYPE_MULTIPART = 'multipart/form-data';

    protected $_methods = array('delete', 'get', 'post', 'put');
    protected $_elementsTypes = array(
        'input', 'hidden', 'password', 'hash', 'file',
        'multiElement',
        'dateinput', 'dateselect', 
        'timeselect', 
        'textarea', 'texteditor', 'textSimpleEditor', 'textMiniEditor',
        'select', 'radio', 'radioGroup', 'checkbox', 'checkboxGroup',
        'html',
    );
    protected $_attribs = array();
    protected $_options = array();
    protected $_displayGroups = array();
    
    protected $_elements = array();
    protected $_elementsLabel;
    protected $_elementsOrder = array();
    
    protected $_buttons;
    
    protected $_decorators = array();
    protected $_renderer;
    protected $_messages = array(
        'error' => array()
    );
    protected $_messageTemplates = array();
    protected $_cache;

    protected $__controllerView = false;

    public function __construct()
    {
        $this->init();
        $this->postInit();
    }

    public function __call($name, $arguments)
    {
        if (strpos($name, 'add') === 0) {
            $type = str_replace('add', '', $name);
            $type[0] = strtolower($type[0]);
            if (in_array($type, $this->_elementsTypes)) {
                $name = $arguments[0];
                $label = $arguments[1];
                $isRequired = $arguments[2];
                return $this->add($name, $label, $type, $isRequired);
            } else {
                trigger_error('Undefined element type for add operation: ['.$type.']', E_USER_ERROR);
            }
        }

        trigger_error('Call to undefined method: ['.$name.']', E_USER_ERROR);
    }

    public function __get($name)
    {
        $element = $this->getElement($name);
        if ($element) {
            return $element;
        }
        return NULL;
    }

    public function init()
    {
        $this->setAction(CURRENT_URL);
    }

    public function postInit()
    {

    }

    public function add($name, $label = false, $type = 'input', $isRequired = false)
    {
        $label = ($label) ? $label : ucfirst($name);
        $element = $this->getNewElement($type)
                        ->setName($name)
                        ->setLabel($label)
                        ->setRequired($isRequired);        
        $this->addElement($element);
        return $this;
    }

    public function addCustom($className, $name, $label = false, $isRequired = false)
    {
        $label = ($label) ? $label : ucfirst($name);
        $element = $this->getNewElementByClass($className)
                        ->setName($name)
                        ->setLabel($label)
                        ->setRequired($isRequired);
        $this->addElement($element);
        return $this;
    }

    public function addElement(Nip_Form_Element_Abstract $element)
    {
        $name = $element->getUniqueId();
        $this->_elements[$name] = $element;
        $this->_elementsLabel[$element->getLabel()] = $name;
        $this->_elementsOrder[] = $name;
        return $this;
    }

    public function removeElement($name)
    {
        unset($this->_elements[$name]);

        $key = array_search($name, $this->_elementsOrder);
        if ($key) {
            unset($this->_elementsOrder[$key]);
        }
        return $this;
    }

    /**
     * Add a display group
     * Groups named elements for display purposes.
     */
    public function addDisplayGroup(array $elements, $name)
    {
        $group = $this->newDisplayGroup();
        foreach ($elements as $element) {
            if (isset($this->_elements[$element])) {
                $add = $this->getElement($element);
                if (null !== $add) {
                    $group->addElement($add);
                }
            }
        }
        if (empty($group)) {
            trigger_error('No valid elements specified for display group');
        }

        $name = (string) $name;
        $group->setLegend($name);

        $this->_displayGroups[$name] = $group;

        return $this;
    }

    /**
     * @return Nip_Form_DisplayGroup
     */
    public function getDisplayGroup($name)
    {
        if (array_key_exists($name, $this->_displayGroups)) {
            return $this->_displayGroups[$name];
        }
        return null;
    }

    public function newDisplayGroup()
    {
        $group = new Nip_Form_DisplayGroup();
        $group->setForm($this);
        return $group;
    }

    public function getDisplayGroups()
    {
        return $this->_displayGroups;
    }

    public function addButton($name, $label = false, $type = 'button')
    {
        $class = 'Nip_Form_Button_'.ucfirst($type);        
        $this->_buttons[$name] = new $class($this);
        $this->_buttons[$name]->setName($name)
                ->setLabel($label);
        return $this;
    }

    /**
     * @return Nip_Form_Element_Abstract
     */
    public function getButton($name)
    {
        if (array_key_exists($name, $this->_buttons)) {
            return $this->_buttons[$name];
        }
        return null;
    }

    /**
     * @return Nip_Form_Element_Abstract
     */
    public function getElement($name)
    {
        if (array_key_exists($name, $this->_elements)) {
            return $this->_elements[$name];
        }
        return null;
    }

    /**
     * @return Nip_Form_Element_Abstract
     */
    public function getElementByLabel($label)
    {
        if (array_key_exists($label, $this->_elementsLabel)) {
            return $this->_elements[$this->_elementsLabel[$label]];
        }
        return null;
    }

    public function setElementOrder($element, $neighbour, $type = 'bellow')
    {
        if (in_array($element, $this->_elementsOrder) && in_array($neighbour, $this->_elementsOrder)) {
            $newOrder = array();            
            foreach ($this->_elementsOrder as $current) {
                if ($current == $element) {
                    
                } elseif ($current == $neighbour) {
                    if ($type == 'above') {
                        $newOrder[] = $element;
                        $newOrder[] = $neighbour;
                    } else {
                        $newOrder[] = $neighbour;
                        $newOrder[] = $element;
                    }
                } else {
                    $newOrder[] = $current;
                }
            }
            $this->_elementsOrder = $newOrder;
        }
        return $this;
    }

    public function getElements()
    {
        $return = array();
        foreach ($this->_elementsOrder as $current) {
            $return[$current] = $this->_elements[$current];
        }

        return $return;
    }

    public function getButtons()
    {
        return $this->_buttons;
    }

    public function findElements($params = false)
    {
        $elements = array();
        foreach ($this->_elements as $element) {
            if (isset($params['type'])) {
                if ($element->getType() != $params['type']) {
                    continue;
                }
            }
            if (isset($params['attribs']) && is_array($params['attribs'])) {
                foreach ($params['attribs'] as $name => $value) {
                    if ($element->getAttrib($name) != $value) {
                        continue(2);
                    }
                }
            }
            $elements[$element->getUniqueId()] = $element;
        }
        return $elements;
    }

    /**
     * @return Nip_Form_Element_Abstract
     */
    public function getNewElement($type)
    {
        $className = $this->getElementClassName($type);
        return $this->getNewElementByClass($className);
    }

    /**
     * @return Nip_Form_Element_Abstract
     */
    public function getNewElementByClass($className)
    {
        $element = new $className($this);
        return $this->initNewElement($element);
    }

    /**
     * @return Nip_Form_Element_Abstract
     */
    public function initNewElement($element)
    {
        $element->setForm($this);
        return $element;
    }

    /**
     * @return Nip_Form_Element_Abstract
     */
    public function getElementClassName($type)
    {
        return 'Nip_Form_Element_'.ucfirst($type);
        $element = new $className($this);
        return $element;
    }

    /**
     * @return Nip_Form
     */
    public function setOption($key, $value)
    {
        $key = (string) $key;
        $this->_options[$key] = $value;
        return $this;
    }

    public function getOption($key)
    {
        $key = (string) $key;
        if (!isset($this->_options[$key])) {
            return null;
        }

        return $this->_options[$key];
    }

    public function addClass() {
        $classes = func_get_args();
        if (is_array($classes)) {
            $oldClasses = explode(' ', $this->getAttrib('class'));
            $classes = array_merge($classes, $oldClasses);
            $this->setAttrib('class', implode(' ', $classes));
        }
        return $this;
    }

    public function removeClass() {
        $removeClasses = func_get_args();
        if (is_array($removeClasses)) {
            $classes = explode(' ', $this->getAttrib('class'));
            foreach ($removeClasses as $class) {
                $key = array_search($class, $classes);                
                if ($key !== false) {
                    unset($classes[$key]);
                }
            }            
            $this->setAttrib('class', implode(' ', $classes));
        }
        return $this;
    }

    public function hasClass($class) {        
        return in_array($class, explode(' ', $this->getAttrib('class')));
    }

    /**
     * @return Nip_Form
     */
    public function setAttrib($key, $value)
    {
        $key = (string) $key;
        $this->_attribs[$key] = $value;
        return $this;
    }

    /**
     * @param  array $attribs
     * @return Nip_Form
     */
    public function addAttribs(array $attribs)
    {
        foreach ($attribs as $key => $value) {
            $this->setAttrib($key, $value);
        }
        return $this;
    }

    /**
     * @param  array $attribs
     * @return Nip_Form
     */
    public function setAttribs(array $attribs)
    {
        $this->clearAttribs();
        return $this->addAttribs($attribs);
    }

    public function getAttrib($key)
    {
        $key = (string) $key;
        if (!isset($this->_attribs[$key])) {
            return null;
        }

        return $this->_attribs[$key];
    }

    public function getAttribs()
    {
        return $this->_attribs;
    }

    /**
     * @return bool
     */
    public function removeAttrib($key)
    {
        if (isset($this->_attribs[$key])) {
            unset($this->_attribs[$key]);
            return true;
        }

        return false;
    }

    /**
     * @return Nip_Form
     */
    public function clearAttribs()
    {
        $this->_attribs = array();
        return $this;
    }

    public function setAction($action)
    {
        return $this->setAttrib('action', (string) $action);
    }

    public function setMethod($method)
    {
        if (in_array($method, $this->_methods)) {
            return $this->setAttrib('method', $method);
        }
        trigger_error('Method is not valid', E_USER_ERROR);
    }

    public function submited()
    {
        $request = $this->getAttrib('method') == 'post' ? $_POST : $_GET;
        if (count($request)) {
            return true;
        }
        return false;
    }

    public function execute()
    {            
        if ($this->submited()) {            
            return $this->processRequest();
        }
        return false;
    }

    public function processRequest()
    {
        if ($this->validate()) {
            $this->process();
            return true;
        }
        return false;
    }

    public function process()
    {

    }

    public function validate()
    {
        $request = $this->getAttrib('method') == 'post' ? $_POST : $_GET;
        $this->getDataFromRequest($request);
        $this->processValidation();
        return $this->isValid();
    }

    public function processValidation()
    {
        $elements = $this->getElements();
        if (is_array($elements)) {
            foreach ($elements as $name => $element) {
                $element->validate();
            }
        }
    }

    protected function getData()
    {
        $data = array();
        $elements = $this->getElements();
        if (is_array($elements)) {
            foreach ($elements as $name => $element) {
                $data[$name] = $element->getValue();
            }
        }
        return $data;
    }

    protected function getDataFromRequest($request)
    {
        $elements = $this->getElements();
        if (is_array($elements)) {
            foreach ($elements as $name => $element) {
                if ($element->isGroup() && $element->isRequestArray()) {                
                    $name = str_replace('[]', '', $name);
                    $data = is_array($request[$name]) ? $request[$name] : array($request[$name]);
                    $element->getData($data, 'request');
                } else {
                    $value = $request[$name];
                    if (strpos($name, '[') && strpos($name, ']')) {
                        $arrayPrimary = substr($name, 0, strpos($name, '['));
                        $arrayKeys = str_replace($arrayPrimary, '', $name);

                        preg_match_all('/\[([^\]]*)\]/', $arrayKeys, $arr_matches, PREG_PATTERN_ORDER);
                        $value = $request[$arrayPrimary];
                        foreach ($arr_matches[1] as $dimension) {
                            $value = $value[$dimension];
                        }
                    }
                    $element->getData($value, 'request');
                }
            }
        }
    }

    public function isValid()
    {
        return count($this->getErrors()) > 0 ? false : true;
    }

    public function getErrors()
    {
        $errors = array_merge((array) $this->getMessagesType('error'), $this->getElementsErrors());
        return $errors;
    }

    public function addError($message)
    {
        $this->_messages['error'][] = $message;
        return $this;
    }

    public function getMessagesType($type = 'error')
    {
        return $this->_messages[$type];
    }

    public function addMessage($message, $type = 'error')
    {
        $this->_messages[$type][] = $message;
        return $this;
    }

    public function getElementsErrors()
    {
        $elements = $this->getElements();
        $errors = array();
        if (is_array($elements)) {
            foreach ($elements as $name => $element) {
                $errors = array_merge($errors, $element->getErrors());
            }
        }
        return $errors;
    }

    public function getMessages()
    {
        $messages = $this->_messages;
        $messages['error'] = $this->getErrors();
        return $messages;
    }

    public function getMessageTemplate($name)
    {
        return $this->_messageTemplates[$name];
    }

    public function render()
    {
        return $this->getRenderer()->render();
    }

    /**
     * @return Nip_Form_Renderer
     */
    public function getRenderer()
    {
        if (!$this->_renderer) {
            $this->_renderer = $this->getNewRenderer();
        }
        return $this->_renderer;
    }

    /**
     * @return Nip_Form_Renderer
     */
    public function getNewRenderer($type = 'basic')
    {
        $name = 'Nip_Form_Renderer_'.ucfirst($type);
        $renderer = new $name();
        $renderer->setForm($this);
        return $renderer;
    }

    /**
     * @return Nip_Form_Renderer
     */
    public function setRendererType($type)
    {
        $this->_renderer = $this->getNewRenderer($type);
        return $this;
    }

    public function getCache($key)
    {
        return $this->_cache[$key];
    }

    public function setCache($key, $value)
    {
        $this->_cache[$key] = $value;
    }

    public function isCache($key)
    {
        return isset($this->_cache[$key]);
    }

    public function getName()
    {
        return get_class($this);
    }

    public function __toString()
    {
        return $this->render();
    }

    public function getControllerView()
    {
		if (!$this->_controllerView) {
			$this->_controllerView = Nip_FrontController::instance()->getDispatcher()->getCurrentController()->getView();
		}

		return $this->_controllerView;
    }

}