<?php
namespace Enola\Support\Cache;

/**
 * Esta clase es para cuando no se desea utilizar cache entonces estan disponibles todos los metodos pero nunca
 * hay datos por lo que siempre va a tener que recargar los datos
 * @author Eduardo Sebastian Nola <edunola13@gmail.com>
 * @category Enola\Cache
 */
class CacheNone implements CacheInterface{
    /**
     * Constructor
     * Guarda la carpeta a utilizar, la carpeta se debe indicar desde PathApp
     * @param string $folder
     */
    public function __construct() {
    } 
    /**
     * Devuelve si existe un dato guardado en cache con esa clave
     * @param string $key
     */
    public function exists($key) {
        return FALSE;
    }
    /**
     * Devuelve un valor guardado en cache o null si no existe
     * @param string $key
     */
    public function get($key) {
        return NULL;
    }
    /**
     * Almacena un valor en cache asociado a una clave
     * @param string $key
     * @param type $data
     * @param int $ttl
     */
    public function store($key,$data,$ttl=0) {
        return TRUE;
    }
    /**
     * Elimina un valor en cache asociado a una clave
     * @param string $key
     */
    public function delete($key) {
        return TRUE;
    }    
}