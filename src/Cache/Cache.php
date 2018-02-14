<?php
namespace Enola\Support\Cache;
use Enola\EnolaContext;

/**
 * Esta clase implementa el sistema de cache. Implementa la interface de cache y responde a todos los metodos segun el
 * driver actual que tenga seteado.
 * Esta administra los nombres de las claves en base al prefijo utilizado
 * @author Eduardo Sebastian Nola <edunola13@gmail.com>
 * @category Enola\Cache
 */
class Cache implements CacheInterface{
    /** Contiene los datos del archivo de configuracion
     * @var array */
    private static $config;
    /** Prefijo a utilizar
     * @var string */
    public $prefix;
    /** Referencia al driver 
     * @var CacheInterface */
    public $store;
    /**
     * Constructor del sistema de cahce. Levanta la configuracion del archivo o de la variable estatica e instancia al
     * driver correspondiente.
     * @param string $store
     */
    public function __construct($store = "Default") {        
        $context= EnolaContext::getInstance();
        if(self::$config == NULL){
            self::$config= $context->readConfigurationFile('cache');
        }
        $this->prefix= self::$config["prefix"];
        $this->setCacheStore($store);
    }
    /**
     * Setea el driver indicado
     * @param string $store
     */
    public function setCacheStore($store = "Default"){
        if($store == "Default"){
            $store= self::$config['defaultStore'];            
        }
        if($store == 'none'){
            $this->store= new CacheNone();
        }else{
            $config= self::$config['stores'][$store];
            switch ($config['driver']) {
                case 'file':
                    $this->store= new CacheFileSystem($config['folder']);
                    break;
                case 'database':
                    $this->store= new CacheDataBase($config['connection'], $config['table']);
                    break;
                case 'apc':
                    $this->store= new CacheApc();
                    break;
                case 'memcached':
                    $persistenceId= isset($config['persistenceId']) ? $config['persistenceId'] : NULL;
                    $this->store= new CacheMemCached($config["servers"], $persistenceId);
                    break;
                case 'redis':
                    $this->store= new CacheRedis($config['schema'], $config['host'], $config['port']);
                    break;
                default:
                    \Enola\Error::general_error("Cache Configuration", "Driver specified unsupported");
                    break;
            }
        }
    }
    /**
     * Codifica la clave y la une al prefijo actual
     * @param string $key
     * @return string
     */
    protected function realKey($key){
        return $this->prefix . md5($key);
    }    
    /**
     * Devuelve si existe un dato guardado en cache con esa clave
     * @param string $key
     */
    public function exists($key){
        return $this->store->exists($this->realKey($key));
    }
    /**
     * Devuelve un valor guardado en cache o null si no existe
     * @param string $key
     */
    public function get($key){
        return $this->store->get($this->realKey($key));
    }
    /**
     * Almacena un valor en cache asociado a una clave
     * @param string $key
     * @param type $data
     * @param int $ttl
     */
    public function store($key, $data, $ttl = 0) {
        return $this->store->store($this->realKey($key), $data, $ttl);
    }
    /**
     * Elimina un valor en cache asociado a una clave
     * @param string $key
     */
    public function delete($key){
        return $this->store->delete($this->realKey($key));
    }    
}