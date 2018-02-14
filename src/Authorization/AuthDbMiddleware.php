<?php
namespace Enola\Support\Authorization;

/**
 * Middleware correspondiente al almacenamiento de las configuracion de autorizacion en base de datos
 * @author Eduardo Sebastian Nola <edunola13@gmail.com>
 * @category Enola\Support
 */
class AuthDbMiddleware implements AuthMiddleware{
    /** Conexion a Base de Datos a utilizar
     * @var string */
    public $nameDB;
    /** Tabla usuario
     * @var string */
    public $tableUser;
    /** Tabla usuario-perfil
     * @var string */
    public $tableUserProfile;
    /** Tabla perfil
     * @var string */
    public $tableProfile;
    /** Tabla modulo-permitido
     * @var string */
    public $tableModulePermit;
    /** Tabla modulo-denegado
     * @var string */
    public $tableModuleDeny;
    /** Tabla modulo
     * @var string */
    public $tableModule;
    /** Tabla llave
     * @var string */
    public $tableKey;
    /** Referencia a la DataBaseAR 
     * @var \Enola\DB\DataBaseAR */
    public $connection;
    /** Definicion de todos los modulos por clave
     * @var mixed[] */
    protected $modules;
    /** Todos los modulos que ya se cargaron desde la base
     * @var mixed */
    protected $loadModules= array();
    /** Definicion de todos los perfiles por clave
     * @var mixed[] */
    protected $profiles;
    /** Todos los perfiles que ya se cargaron desde la base
     * @var mixed */
    protected $loadProfiles= array();

    /**
     * Constructor - Inicia una conexion a la base de datos en base a la definicion
     * @param string $nameDB
     * @param string $tableUser
     * @param string $tableUserProfile
     * @param string $tableProfile
     * @param string $tableModulePermit
     * @param string $tableModuleDeny
     * @param string $tableModule
     * @param string $tableKey
     */
    public function __construct($nameDB, $tableUser, $tableUserProfile, $tableProfile, $tableModulePermit,
            $tableModuleDeny, $tableModule, $tableKey) {
        $this->nameDB= $nameDB;
        $this->tableUser= $tableUser;
        $this->tableUserProfile= $tableUserProfile;
        $this->tableProfile= $tableProfile;
        $this->tableModulePermit= $tableModulePermit;
        $this->tableModuleDeny= $tableModuleDeny;
        $this->tableModule= $tableModule;
        $this->tableKey= $tableKey;
        $this->connection= new \Enola\DB\DataBaseAR(TRUE, $nameDB);
    }
    /**
     * Retorna todos los modulos de la aplicacion
     * @return array
     */
    public function getModules(){
        if($this->loadModules != 'ALL'){
            $this->connection->select('name');
            $this->connection->from($this->tableModule);
            $modules= $this->connection->get()->fetchAll(\PDO::FETCH_ASSOC);
            foreach ($modules as $module) {
                $this->getModule($module['name']);
            }
            $this->loadModules= 'ALL';
        }
        return $this->modules;
    }
    /**
     * Retorna un determinado modulo o NULL si no existe
     * @param string $name
     * @return array
     */
    public function getModule($name){
        if($this->loadModules != 'ALL' && !in_array($name, $this->loadModules)){
            $this->connection->select('k.url, k.method');
            $this->connection->from($this->tableModule . ' m');
            $this->connection->join($this->tableKey . ' k', 'm.id = k.moduleId');
            $this->connection->where('m.name = :name', array('name' => $name));
            $module= $this->connection->get()->fetchAll(\PDO::FETCH_ASSOC);
            $this->modules[$name]= $module;
            $this->loadModules[]= $name;
        }
        return $this->modules[$name];
    }
    /**
     * Retorna todos los profiles de la aplicacion
     * @return array
     */
    public function getProfiles(){
        if($this->loadProfiles != 'ALL'){
            $this->connection->select('name');
            $this->connection->from($this->tableProfile);
            $profiles= $this->connection->get()->fetchAll(\PDO::FETCH_ASSOC);
            foreach ($profiles as $profile) {
                $this->getProfile($profile['name']);
            }
            $this->loadModules= 'ALL';
        }
        return $this->profiles;
    }
    /**
     * Retorna un determinado profile o NULL si no existe
     * @param string $name
     * @return array
     */
    public function getProfile($name){
        if($this->loadProfiles != 'ALL' && !in_array($name, $this->loadProfiles)){
            $this->connection->select('id, name, join, error_redirect, error_forward');
            $this->connection->from($this->tableProfile);
            $this->connection->where('name = :name', array('name' => $name));
            $profile= $this->connection->get()->fetch(\PDO::FETCH_ASSOC);
            
            $this->connection->select('m.name');
            $this->connection->from($this->tableModulePermit . ' p');
            $this->connection->join($this->tableModule . ' m', 'p.moduleId = m.id');
            $this->connection->where('p.profileId = :id', array('id' => $profile['id']));
            $rta= $this->connection->get();
            $permitModules= array();
            while($module= $rta->fetch()){
                $permitModules[]= $module[0]; 
            }
            
            $this->connection->select('m.name');
            $this->connection->from($this->tableModuleDeny . ' p');
            $this->connection->join($this->tableModule . ' m', 'p.moduleId = m.id');
            $this->connection->where('p.profileId = :id', array('id' => $profile['id']));
            $rta= $this->connection->get();
            $denyModules= array();
            while($module= $rta->fetch()){
                $denyModules[]= $module[0]; 
            }
            
            $this->profiles[$name]= array('permit' => $permitModules, 'deny' => $denyModules, 'error-redirect' => $profile['error_redirect'], 'error-forward' => $profile['error_forward']);
            $this->loadProfiles[]= $name;
        }
        return $this->profiles[$name];
    }
}