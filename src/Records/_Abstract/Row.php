<?php

namespace Nip\Records\_Abstract;

abstract class Row extends \Nip_Object
{

    protected $_name = null;
    protected $_manager = null;
    protected $_managerName = null;

    protected $_dbData = array();
    protected $_helpers = array();


    /**
     * The loaded relationships for the model.
     * @var array
     */
    protected $relations = [];

    public function __construct()
    {
    }


    /**
     * Overloads Ucfirst() helper
     *
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {

        if (substr($name, 0, 3) == "get") {
            $relation = $this->getRelation(substr($name, 3), $arguments[0]);
            if ($relation) {
                return $relation;
            }

            if ($related) {
                return $this->__getRecords($relationName, $arguments[0]);
            }
        }

        if ($name === ucfirst($name)) {
            $class = 'Nip_Helper_' . $name;

            if (!isset($this->helpers[$class])) {
                $this->_helpers[$class] = new $class;
            }
            return $this->_helpers[$class];
        }

        trigger_error("Call to undefined method $name", E_USER_ERROR);
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        if ($this->_name == null) {
            $this->_name = inflector()->unclassify(get_class($this));
        }
        return $this->_name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->_name = $name;
    }

    public function writeDBData($data = false)
    {
        foreach ($data as $key => $value) {
            $this->_dbData[$key] = $value;
        }
    }

    public function getPrimaryKey()
    {
        $pk = $this->getManager()->getPrimaryKey();
        return $this->$pk;
    }

    public function insert()
    {
        $pk = $this->getManager()->getPrimaryKey();
        $this->$pk = $this->getManager()->insert($this);
        return $this->$pk > 0;
    }

    public function update()
    {
        $return = $this->getManager()->update($this);
        return $return;
    }

    public function save()
    {
        $this->getManager()->save($this);
    }

    public function saveRecord()
    {
        $this->getManager()->save($this);
    }

    public function delete()
    {
        $this->getManager()->delete($this);
    }

    public function isInDB()
    {
        $pk = $this->getManager()->getPrimaryKey();
        return $this->$pk > 0;
    }

    public function exists()
    {
        return $this->getManager()->exists($this);
    }

    public function toArray()
    {
        $vars = get_object_vars($this);
        return $vars['_data'];
    }

    public function toJSON()
    {
        return json_encode($this->toArray());
    }

    public function toApiArray()
    {
        $data = $this->toArray();
        return $data;
    }

    public function writeData($data = false)
    {
        foreach ($data as $key => $value) {
            $this->__set($key, $value);
        }
    }

    /**
     * @return \Nip_Records
     */
    public function getManager()
    {
        if ($this->_manager == null) {
            $this->initManager();
        }

        return $this->_manager;
    }

    public function initManager()
    {
        $class = $this->getManagerName();
        $this->_manager = call_user_func(array($class, 'instance'));
    }

    public function setManager($manager)
    {
        $this->_manager = $manager;
    }

    public function getManagerName()
    {
        if ($this->_managerName == null) {
            $this->inflectManagerName();
        }
        return $this->_managerName;
    }

    public function initManagerName()
    {
        $this->_managerName = $this->inflectManagerName();
    }

    public function inflectManagerName()
    {
        return ucfirst(inflector()->pluralize(get_class($this)));
    }

    public function getRelation($relationName, $populate = true)
    {
        if (!$this->hasRelation($relationName)) {
            $this->initRelation($relationName, $populate);
        }
        return $this->relations[$relationName];
    }

    public function hasRelation($key)
    {
        return array_key_exists($key, $this->relations);
    }

    public function initRelation($relationName, $populate = true)
    {
        if (!$this->getManager()->hasRelation($relationName)) {
            return;
        }
        $this->relations[$relationName] = $this->newRelation($relationName);

        if ($populate === true) {
            $this->relations[$relationName]->populate();
        }
    }

    public function newRelation($relationName)
    {
        $relation = clone $this->getManager()->getRelation($relationName);
        $relation->setWith();
        return $relation;
    }

    protected function __getRecords($name, $populate)
    {

        $pk = $this->getManager()->getPrimaryKey();
        if (!$this->$pk) {
            $populate = false;
        }

        list($type, $params) = $this->getManager()->hasRelation($name);
        if ($type == 'belongsTo') {
            $populate = false;
        }

        if (!isset($this->_associated[$name])) {
            if ($type == 'belongsTo') {
                $manager = call_user_func(array($params['class'], "instance"));
                $this->setAssociated($name, $manager->findOne($this->$params['fk']));
            } else {
                $collectionClass = $params['associatedClass'] ? $params['associatedClass'] : $this->getManager()->getAssociatedClass($type,
                    $name);
                $collection = new $collectionClass();

                $collection->setParams($params);
                $collection->setItem($this);

                $this->setAssociated($name, $collection);
            }
        }

        if ($populate) {
            $this->_associated[$name]->populate();
        }

        return $this->_associated[$name];
    }
}