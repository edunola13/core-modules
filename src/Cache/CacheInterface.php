<?php
namespace Enola\Support\Cache;

/**
 * Esta interface define los metodos de acceso a la cache, digamos que puede hacer el sistema de cache
 * @author Eduardo Sebastian Nola <edunola13@gmail.com>
 * @category Enola\Cache
 */
interface CacheInterface {
    /**
     * Devuelve si existe un dato guardado en cache con esa clave
     * @param string $key
     */
    public function exists($key);
    /**
     * Devuelve un valor guardado en cache o null si no existe
     * @param string $key
     */
    public function get($key);
    /**
     * Almacena un valor en cache asociado a una clave
     * @param string $key
     * @param type $data
     * @param int $ttl
     */
    public function store($key, $data, $ttl=0);
    /**
     * Elimina un valor en cache asociado a una clave
     * @param string $key
     */
    public function delete($key);
}
