<?php
namespace Enola\Support\Cache;

/**
 * Esta clase representa al driver para APC (Alternative PHP Cache)
 * Implementa la interface cache llamando a las funciones provistas por la extension APC
 * @author Eduardo Sebastian Nola <edunola13@gmail.com>
 * @category Enola\Cache
 */
class CacheApc extends CacheUtils implements CacheInterface{
    /**
     * Constructor
     */
    public function __construct() {
    }
    /**
     * Devuelve si existe un dato guardado en cache con esa clave
     * @param string $key
     * @return boolean
     */
    public function exists($key) {
        //Version viejas no contiene la funcion apc_exists
        if(function_exists("apc_exists")){
            return apc_exists($key);
        }else{
            return (bool)apc_fetch($key);
        }
    }
    /**
     * Devuelve un valor guardado en cache o null si no existe
     * @param string $key
     * @return type
     */
    public function get($key){
        return $this->unPrepareData(apc_fetch($key));
    }
    /**
     * Almacena un valor en cache asociado a una clave
     * @param string $key
     * @param type $data
     * @param int $ttl
     */
    public function store($key, $data, $ttl=0){
        return apc_store($key, $this->preperaData($data), $ttl);
    }
    /**
     * Elimina un valor en cache asociado a una clave
     * @param string $key
     * @return boolean
     */
    public function delete($key){
        return apc_delete($key);
    }
}