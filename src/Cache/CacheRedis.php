<?php
namespace Enola\Support\Cache;

/**
 * Esta clase representa al driver para Redis
 * Implementa la interface instanciando al manejador y ejecutando el comportamiento correspondiente del mismo.
 * @author Eduardo Sebastian Nola <edunola13@gmail.com>
 * @category Enola\Cache
 */
class CacheRedis extends CacheUtils implements CacheInterface{
    /** Referencia a Memcached
     * @var \Predis\Client */
    public $connection;
    /**
     * Constructor - Instancia la clase Memcached y agrega los servidores indicados
     * @param arry $servers
     * @param type $persistentId 
     */
    public function __construct($schema, $host, $port) {
        //PredisAutoloader::register();
        $this->connection= new \Predis\Client(array('schema' => $schema, 'host' => $host, 'port' => $port));

    }
    /**
     * Devuelve si existe un dato guardado en cache con esa clave
     * @param string $key
     * @return boolean
     */
    public function exists($key) {
        return $this->connection->exists($key);
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
        $this->connection->set($key, $this->preperaData($data));
        return $this->connection->expire($key, $ttl);
    }
    /**
     * Elimina un valor en cache asociado a una clave
     * @param string $key
     * @return boolean
     */
    public function delete($key){
        return $this->connection->del($key);
    }
}