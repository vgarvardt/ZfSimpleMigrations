<?php
/**
 * User: Vladimir Garvardt
 * Date: 2/14/13
 * Time: 5:24 PM
 */

namespace ZendDbMigrations\Model;


class MigrationVersion
{
    /**
     * @var int
     */
    protected $id;
    /**
     * @var int
     */
    protected $version;

    public function exchangeArray($data)
    {
        foreach (array_keys(get_object_vars($this)) as $property) {
            $this->{$property} = (isset($data[$property])) ? $data[$property] : null;
        }
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $version
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }

    /**
     * @return int
     */
    public function getVersion()
    {
        return $this->version;
    }
}