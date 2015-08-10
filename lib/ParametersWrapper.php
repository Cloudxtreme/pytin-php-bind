<?php

/**
 * Объект, враппер над параметрами.
 * Превращает любой ассоциативный массив в объект со свойствами.
 *
 * Можно объявить методы set<ИмяПараметра>/get<ИмяПараметра>, для
 * добавления логики при установке и получении значений.
 */
class ParametersWrapper {

    private $dataArray;
    private $changedFields = array();

    public function __construct($data = array()) {
        $this->dataArray = $data;
    }

    public static function fromJSON($jsonData) {
        return self::fromArray(json_decode($jsonData, true));
    }

    public static function fromArray($dataArray) {
        if (!is_array($dataArray)) {
            throw new InvalidArgumentException('dataArray');
        }

        $parametersClass = get_called_class();

        $parameters = new $parametersClass();
        $parameters->setData($dataArray);

        return $parameters;
    }

    public function setData(array $dataArray = array()) {
        if (!$this->isStringKeyedOrEmpty($dataArray)) {
            throw new InvalidArgumentException('dataArray.assoc');
        }

        $this->dataArray = $dataArray;
    }

    /**
     * Проверяет что все ключи массива - строки
     * @param type $dataArray
     * @return type
     * @throws InvalidArgumentException
     */
    private function isStringKeyedOrEmpty($dataArray) {
        if (empty($dataArray)) {
            return true;
        }

        if (!is_array($dataArray)) {
            return false;
        }

        // проверяем что числовые ключи отсутствуют
        return !((bool)count(array_filter(array_keys($dataArray), 'is_numeric')));
    }

    public function __get($name) {
        if (empty($name) || is_numeric($name)) {
            throw new InvalidArgumentException('name');
        }

        $getterName = 'get' . self::upperFirst($name);
        if (method_exists($this, $getterName)) {
            return $this->$getterName();
        }

        if (!isset($this->dataArray[$name])) {
            throw new OutOfRangeException("name: $name");
        }

        return $this->internalGet($name);
    }

    public function __set($name, $value) {
        if (empty($name) || is_numeric($name)) {
            throw new InvalidArgumentException('name');
        }

        $setterName = 'set' . self::upperFirst($name);
        if (method_exists($this, $setterName)) {
            $this->$setterName($value);
        } else {
            $this->internalSet($name, $value);
        }
    }

    protected function internalGet($name) {
        if (empty($name) || is_numeric($name)) {
            throw new InvalidArgumentException('name');
        }

        return $this->dataArray[$name];
    }

    protected function internalSet($name, $value) {
        if (empty($name) || is_numeric($name)) {
            throw new InvalidArgumentException('name');
        }

        if (isset($this->dataArray[$name])) {
            if ($this->dataArray[$name] != $value) {
                $this->changedFields[$name] = array($this->dataArray[$name], $value);
            }
        } else {
            $this->changedFields[$name] = array(null, $value);
        }

        $this->dataArray[$name] = $value;
    }

    public function __isset($name) {
        if (empty($name) || is_numeric($name)) {
            throw new InvalidArgumentException('name');
        }

        return isset($this->dataArray[$name]);
    }

    public function isEmpty() {
        return empty($this->dataArray);
    }

    public function getFields() {
        return array_keys($this->dataArray);
    }

    /**
     * Returns fields changes
     * @return array (field => array(old, new))
     */
    public function getChanges() {
        return $this->changedFields;
    }

    public function __toString() {
        $strOut = '';
        foreach ($this->getAsMap() as $optName => $optValue) {
            if (is_array($optValue)) {
                $optValue = '[' . join(',', $optValue) . ']';
            }

            $strOut .= "$optName:$optValue ";
        }

        return $strOut;
    }

    public function getAsMap() {
        return $this->dataArray;
    }

    public static function upperFirst($str) {
        $str = strtolower($str);
        $str{0} = strtoupper($str{0});

        return $str;
    }
}
