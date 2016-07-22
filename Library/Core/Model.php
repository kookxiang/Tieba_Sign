<?php
/**
 * KK-Framework
 * Author: kookxiang <r18@ikk.me>
 */

namespace Core;

use ReflectionObject;
use ReflectionProperty;

abstract class Model
{
    /**
     * @ignore
     * @var LazyLoadObject[] $lazyLoadObj
     */
    protected $_lazyLoad = array();

    const SAVE_AUTO = 0;
    const SAVE_INSERT = 1;
    const SAVE_UPDATE = 2;

    public function __construct()
    {
        $this->lazyLoad();
    }

    protected function lazyLoad()
    {
    }

    public function delete()
    {
        $reflection = new ReflectionObject($this);
        $primaryKey = $this->getPrimaryKeyName($reflection);
        $property = $reflection->getProperty($primaryKey);
        $property->setAccessible(true);
        $primaryValue = $property->getValue($this);
        if (!$primaryValue) {
            throw new Error('Cannot delete object without id');
        }
        $tableName = $this->getTableName($reflection);
        $statement = Database::getInstance()->prepare("DELETE FROM `{$tableName}` WHERE `{$primaryKey}`=:value");
        $statement->bindValue(':value', $primaryValue);
        $statement->execute();
    }

    public function update()
    {
        $reflection = new ReflectionObject($this);
        $map = $this->getTableMap($reflection);
        $primaryKey = $this->getPrimaryKeyName($reflection);
        $tableName = $this->getTableName($reflection);
        if (empty($map[$primaryKey])) {
            throw new Error('Cannot update a record without id');
        }
        $sql = "UPDATE `{$tableName}` SET ";
        foreach ($map as $key => $value) {
            $sql .= "`{$key}` = :{$key},";
        }
        $sql = rtrim($sql, ',');
        $sql .= " WHERE {$primaryKey} = :id";
        $statement = Database::getInstance()->prepare($sql);
        $statement->bindValue(':id', $map[$primaryKey]);
        foreach ($map as $key => $value) {
            $statement->bindValue(":{$key}", $value);
        }
        $statement->execute();
    }

    public function insert()
    {
        $reflection = new ReflectionObject($this);
        $map = $this->getTableMap($reflection);
        $primaryKey = $this->getPrimaryKeyName($reflection);
        $tableName = $this->getTableName($reflection);

        $sql = "INSERT INTO `{$tableName}` SET ";
        foreach ($map as $key => $value) {
            $sql .= "`{$key}` = :{$key},";
        }
        $sql = rtrim($sql, ',');
        $statement = Database::getInstance()->prepare($sql);
        foreach ($map as $key => $value) {
            $statement->bindValue(":{$key}", $value);
        }
        $statement->execute();
        $insertId = Database::getInstance()->lastInsertId();
        if ($insertId) {
            $reflection->getProperty($primaryKey)->setValue($this, $insertId);
        }
    }

    public function save($mode = self::SAVE_AUTO)
    {
        if ($mode == self::SAVE_UPDATE) {
            $this->update();
        } elseif ($mode == self::SAVE_INSERT) {
            $this->insert();
        } else {
            $reflection = new ReflectionObject($this);
            $primaryKeyName = $this->getPrimaryKeyName($reflection);
            $primaryKey = $reflection->getProperty($primaryKeyName);
            $primaryKey->setAccessible(true);
            $identifier = $primaryKey->getValue($this);
            if ($identifier) {
                $this->update();
            } else {
                $this->insert();
            }
        }
    }

    private function getPrimaryKeyName(ReflectionObject $reflection)
    {
        $primaryKeyName = 'id';
        $reflectionProp = $reflection->getProperties(ReflectionProperty::IS_PROTECTED | ReflectionProperty::IS_PUBLIC);
        foreach ($reflectionProp as $property) {
            if (stripos($property->getDocComment(), '@PrimaryKey')) {
                $primaryKeyName = $property->getName();
            }
        }
        return $primaryKeyName;
    }

    private function getTableName(ReflectionObject $reflection)
    {
        $docComment = $reflection->getDocComment();
        if (!preg_match('/@table ?([A-Za-z\-_0-9]+)/i', $docComment, $matches) || !$matches[1]) {
            return strtolower($reflection->getShortName());
        } else {
            return $matches[1];
        }
    }

    private function getTableMap(ReflectionObject $reflection)
    {
        $map = array();
        $reflectionProp = $reflection->getProperties(ReflectionProperty::IS_PRIVATE | ReflectionProperty::IS_PROTECTED | ReflectionProperty::IS_PUBLIC);
        foreach ($reflectionProp as $property) {
            if (strpos($property->getDocComment(), '@ignore')) {
                continue;
            }
            $propertyName = $property->getName();
            if (isset($this->_lazyLoad[$propertyName])) {
                $map[$propertyName] = $this->_lazyLoad[$propertyName]->getValue();
                continue;
            }
            $property->setAccessible(true);
            $propertyValue = $property->getValue($this);
            $map[$propertyName] = $propertyValue;
        }
        return $map;
    }

    protected function setLazyLoad($propertyName, callable $callback)
    {
        // First, save the value
        $value = $this->$propertyName;
        // Create LazyLoadObject
        $this->_lazyLoad[$propertyName] = new LazyLoadObject($value, $callback);
        // Remove the property to active getter and setter
        unset($this->$propertyName);
    }

    public function __get($propertyName)
    {
        if (isset($this->_lazyLoad[$propertyName])) {
            return ($this->_lazyLoad[$propertyName])();
        }
        return null;
    }

    public function __set($propertyName, $value)
    {
        if (isset($this->_lazyLoad[$propertyName])) {
            ($this->_lazyLoad[$propertyName])->updateValue($value);
        } else {
            $this->$propertyName = $value;
        }
    }

    public function __wakeup()
    {
        $this->lazyLoad();
    }
}
