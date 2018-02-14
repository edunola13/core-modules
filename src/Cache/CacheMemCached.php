<?php
namespace Enola\Support\Cache;

/**
 * Esta clase representa al driver para MemCached
 * Implementa la interface instanciando al manejador y ejecutando el comportamiento correspondiente del mismo.
 * @author Eduardo Sebastian Nola <edunola13@gmail.com>
 * @category Enola\Cache
 */
class CacheMemCached extends CacheUtils implements CacheInterface{
    /** Referencia a Memcached
     * @var \Memcached */
    public $connection;
    /**
     * Constructor - Instancia la clase Memcached y agrega los servidores indicados
     * @param arry $servers
     * @param type $persistentId 
     */
    public function __construct($servers = array(), $persistentId = NULL) {
        $this->connection= new \Memcached($persistentId);
        foreach ($servers as $value) {
            $this->addServer($value['host'], $value['port'], $value['weight']);
        }
    }
    /**
     * Devuelve si existe un dato guardado en cache con esa clave
     * @param string $key
     * @return boolean
     */
    public function exists($key) {
        return (bool)$this->connection->get($key);
    }
    /**
     * Devuelve un valor guardado en cache o null si no existe
     * @param string $key
     * @return type
     */
    public function get($key){
        return $this->unPrepareData($this->connection->get($key));
    }
    /**
     * Almacena un valor en cache asociado a una clave
     * @param string $key
     * @param type $data
     * @param int $ttl
     */
    public function store($key, $data, $ttl=0){
        return $this->connection->set($key, $this->preperaData($data), $ttl);
    }
    /**
     * Elimina un valor en cache asociado a una clave
     * @param string $key
     * @return boolean
     */
    public function delete($key){
        return $this->connection->delete($key);
    }
    /**
     * Agrega un servidor al sistema memcache
     * @param type $host
     * @param type $port
     * @param type $weight
     * @return boolean
     */
    public function addServer($host, $port, $weight=0){
        return $this->connection->addServer($host, $port, $weight);
    }
}