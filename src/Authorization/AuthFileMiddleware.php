<?php
namespace Enola\Support\Authorization;

/**
 * Middleware correspondiente al almacenamiento de las configuracion de autorizacion en archivo de configuracion
 * @author Eduardo Sebastian Nola <edunola13@gmail.com>
 * @category Enola\Support
 */
class AuthFileMiddleware implements AuthMiddleware{
    /** Definicion de todos los modulos por clave
     * @var mixed */
    protected $modules;
    /** Definicion de todos los perfiles por clave
     * @var mixed */
    protected $profiles;    
    
    public function __construct($configFile) {
        if(!isset($configFile['modules']) || !isset($configFile['profiles'])){
            \Enola\Error::general_error('Configuration Error', 'The authorization configuration file is not available for File Middleware');
        }
        $this->modules= $configFile['modules'];
        $this->profiles= $configFile['profiles'];        
    }
    
    /**
     * Retorna todos los modulos de la aplicacion
     * @return array
     */
    public function getModules(){
        return $this->modules;
    }
    /**
     * Retorna un determinado modulo o NULL si no existe
     * @param string $name
     * @return array
     */
    public function getModule($name){
        if(isset($this->modules[$name])){
            return $this->modules[$name];
        }
        return NULL;
    }
    /**
     * Retorna todos los profiles de la aplicacion
     * @return array
     */
    public function getProfiles(){
        return $this->profiles;
    }
    /**
     * Retorna un determinado profile o NULL si no existe
     * @param string $name
     * @return array
     */
    public function getProfile($name){
        if(isset($this->profiles[$name])){
            return $this->profiles[$name];
        }
        return NULL;
    }    
}