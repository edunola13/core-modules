<?php 
namespace Enola\Support\Authorization;
use Enola\EnolaContext;
/**
 * Esta clase realiza los controles de autorizacion para los diferentes modulos de su aplicacion.
 * Este servira para controlar los accesos a diferentes funcionalidades de la aplicacion como puede ser un controlador,
 * componentes, etc.
 * @author Eduardo Sebastian Nola <edunola13@gmail.com>
 * @category Enola\Support
 */
class Authorization {
    /** Instancia unica de autorizacion
     * @var Authorization */
    protected static $instance;
    /** Referencia al contexto de la App
     * @var \Enola\EnolaContext */
    protected $context;
    /** Instancia del middleware indicado
     * @var AuthMiddleware */
    protected $authMiddleware;
    /** Nombre de la variable de sesion que contiene los perfiles del usuario
     * @var string */
    protected $sessionProfile;
    /** Perfiles del usuario actual
     * @var mixed */
    protected $userProfiles;
        
    protected function __construct() {
        $this->context= EnolaContext::getInstance();
        $config= $this->context->readConfigurationFile($this->context->getAuthorizationFile());
        $this->sessionProfile= $config['session-profile'];
        $actualAuthorization= $config['actual-authorization'];
        $config= $config[$actualAuthorization];
        if($config['authorization-type'] == 'database'){
            $this->authMiddleware= new AuthDbMiddleware($config['connection'], $config['tables']['user'], $config['tables']['user-profile'],
                    $config['tables']['profile'], $config['tables']['profile-permit'], $config['tables']['profile-deny'], $config['tables']['module'], $config['tables']['key']);
        }else{
            $this->authMiddleware= new AuthFileMiddleware($config);        
        }
    }
    /** 
     * @return Authorization
     */
    public static function getInstance(){
        if(Authorization::$instance == NULL){
            Authorization::$instance= new Authorization();
        }
        return Authorization::$instance;
    }
    /**
     * Retorna todos los modulos de la aplicacion
     * @return array
     */
    public function getModules(){
        return $this->authMiddleware->getModules();
    }
    /**
     * Retorna un determinado modulo o NULL si no existe
     * @param string $name
     * @return array
     */
    public function getModule($name){
        return $this->authMiddleware->getModule($name);
    }
    /**
     * Retorna todos los profiles de la aplicacion
     * @return array
     */
    public function getProfiles(){
        return $this->authMiddleware->getProfiles();
    }
    /**
     * Retorna un determinado profile o NULL si no existe
     * @param string $name
     * @return array
     */
    public function getProfile($name){
        return $this->authMiddleware->getProfile($name);
    }
    /**
     * Retorna los perfiles del usuario actual del sistema
     * Por defecto 'default'
     * @param Request $request
     * @return mixed
     */
    public function getUserProfiles(Request $request){
        if($this->userProfiles === NULL){
            $this->userProfiles= $request->session->exist($this->sessionProfile) ? $request->session->get($this->sessionProfile) : 'default';
        }
        return $this->userProfiles;
    }
    
    /*
     * AUTORIZACION DE CONTROLADORES / MODULOS-PERFILES
     */
    /**
     * Indica si el usuario logueado tiene acceso a un modulo
     * @param Request $request
     * @param string $moduleName
     * @return boolean
     */
    public function userHasAccess($request, $moduleName){
        //Tipo por defecto
        $userProfile= $this->getUserProfiles($request);
        $maps= FALSE;
        if(is_array($userProfile)){
            $maps= $this->profilesHasAccess($userProfile, $moduleName);
        }else{
            $maps= $this->profileHasAccess($userProfile, $moduleName);
        }
        return $maps;
    }
    /**
     * Indica si un conjunto de perfiles tienen acceso a un modulo
     * Si un perfil tiene acceso, el conjunto lo tiene
     * @param string[] $profilesName
     * @param string $moduleName
     * @return boolean
     */
    public function profilesHasAccess($profilesName, $moduleName){
        $maps= FALSE;
        foreach ($profilesName as $profile) {
            $maps= $this->profileHasAccess($profile, $moduleName);
        }
        return $maps;
    }
    /**
     * Indica si un perfil tiene acceso a un modulo
     * @param string $profileName
     * @param string $moduleName
     * @return boolean
     */
    public function profileHasAccess($profileName, $moduleName){
        $maps= FALSE;
        //Compruebo que exista la configuracion para el tipo de usuario logueado
        if($this->getProfile($profileName) != NULL){            
            //Seteo la configuracion del usuario correpondiente
            $config_seguridad= $this->getProfile($profileName);
            if(isset($config_seguridad['join'])){
                $maps= $this->profileHasAccess($config_seguridad['join'], $moduleName);
            }
            if(!$maps){
                //Seteo los permisos del usuario
                $permisos= $config_seguridad['permit'];
                //Veo si el profile contiene el modulo en su seccion de permitidos            
                $maps= in_array($moduleName, $permisos);            
            }
            if($maps){
                //Si hubo mapeo, recorro las url denegadas para el usuario
                $denegados= $config_seguridad['deny'];
                $maps= ! in_array($moduleName, $denegados);
            }            
        }        
        return $maps;
    }
    /**
     * Indica si el usuario logueado tiene acceso a un url y request method
     * @param Request $request
     * @param string $url
     * @param string $method
     * @return boolean
     */    
    public function userHasAccessToUrl($request, $url, $method){
        //Tipo por defecto
        $userProfile= $this->getUserProfiles($request);
        $maps= FALSE;
        if(is_array($userProfile)){
            $maps= $this->profilesHasAccessToUrl($userProfile, $url, $method);
        }else{
            $maps= $this->profileHasAccessToUrl($userProfile, $url, $method);
        }
        return $maps;
    }
    /**
     * Indica si un conjunto de perfiles tienen acceso a una url y request method
     * Si un perfil tiene acceso, el conjunto lo tiene
     * @param string[] $profilesName
     * @param string $url
     * @param string $method
     * @return boolean
     */
    public function profilesHasAccessToUrl($profilesName, $url, $method){
        $maps= FALSE;
        foreach ($profilesName as $profile) {
            $maps= $this->profileHasAccessToUrl($profile, $url, $method);
        }
        return $maps;
    }
    /**
     * Indica si un perfil tiene acceso a una url y request method
     * @param string $profileName
     * @param string $url
     * @param string $method
     * @return boolean
     */
    public function profileHasAccessToUrl($profileName, $url, $method){
        $maps= FALSE;
        //Compruebo que exista la configuracion para el tipo de usuario logueado
        if($this->getProfile($profileName) != NULL){            
            //Seteo la configuracion del usuario correpondiente
            $config_seguridad= $this->getProfile($profileName);
            //Veo si el profile padre tiene permisos
            if(isset($config_seguridad['join'])){
                $maps= $this->profileHasAccessToUrl($config_seguridad['join'], $url, $method);
            }
            if(!$maps){
                //Seteo los permisos del usuario
                $permisos= $config_seguridad['permit'];            
                //Recorro sus permisos y veo si alguno coincice
                foreach ($permisos as $permiso) {
                    if($this->mapsModule($permiso, $url, $method)){
                        //Cuando alguno coincide salgo del for
                        $maps= TRUE; break;
                    }
                }
            }
            if($maps){
                //Si hubo mapeo, recorro las url denegadas para el usuario
                $denegados= $config_seguridad['deny'];
                foreach ($denegados as $denegado) {
                    if($this->mapsModule($denegado, $url, $method)){
                        //Si la url es denegada salgo del for
                        $maps= FALSE; break;
                    }
                }
            }  
        }
        return $maps;
    }
    /**
     * Indica si para un modulo dado la url y el request method mapean con el
     * @param array $moduleName
     * @param string $url
     * @param string $method
     * @return boolean
     */
    protected function mapsModule($moduleName, $url, $method){
        $maps= FALSE;
        foreach ($this->getModule($moduleName) as $key) {
            $maps= $this->mapsKey($key, $url, $method);
            if($maps){break;}
        }
        return $maps;
    }
    /**
     * Indica si para la llave de un modulo dado la url y el request method mapean con el
     * @param array $key
     * @param string $url
     * @param string $method
     * @return boolean
     */
    public function mapsKey($key, $url, $method){
        return (\Enola\Http\UrlUri::mapsActualUrl($key['url'], $url) && \Enola\Http\UrlUri::mapsActualMethod($key['method'], $method));
    }
    
    /*
     * AUTORIZACION DE COMPONENTES
     */
    /**
     * Indica si el usuario logueado tiene acceso a la definicion de un componente
     * @param Request $request
     * @param array $component
     * @return boolean
     */
    public function userHasAccessToComponentDefinition($request, $component){
        if(isset($component['authorization-profiles']) && $component['authorization-profiles'] != ""){
            $profiles= str_replace(' ', '', $component['authorization-profiles']);
            $profiles= explode(',', $component['authorization-profiles']);
            $userProfile= $this->getUserProfiles($request);
            //Comprueba si el usuario logueado tiene o no multiples perfiles y en base a eso comprueba
            if(is_array($userProfile)){
                return (count(array_intersect($userProfile, $profiles)) > 0);
            }else{
                return in_array($userProfile, $profiles);
            }            
        }else{
            //Si no esta seteado o es vacio el componente es publico
            return TRUE;
        }
    }
    /**
     * Indica si el usuario logueado tiene acceso a un componente
     * @param Request $request
     * @param string $componentName
     * @return boolean
     */
    public function userHasAccessToComponent($request, $componentName){
        $component= $this->context->getComponentsDefinition()[$componentName];
        return $this->userHasAccessToComponentDefinition($request, $component);
    }    
    /**
     * Indica si un conjunto de perfiles tienen acceso a un componente
     * Si un perfil tiene acceso, el conjunto lo tiene
     * @param string $profilesName
     * @param string $componentName
     * @return boolean
     */
    public function profilesHasAccessToComponent($profilesName, $componentName){
        $component= $this->context->getComponentsDefinition()[$componentName];
        if(isset($component['authorization-profiles']) && $component['authorization-profiles'] != ""){
            $profiles= str_replace(' ', '', $component['authorization-profiles']);
            $profiles= explode(',', $component['authorization-profiles']);
            return (count(array_intersect($profilesName, $profiles)) > 0);
        }else{
            return TRUE;
        }
    }
    /**
     * Indica si un perfil tiene acceso a un componente
     * @param string $profileName
     * @param string $componentName
     * @return boolean
     */
    public function profileHasAccessToComponent($profileName, $componentName){
        $component= $this->context->getComponentsDefinition()[$componentName];
        if(isset($component['authorization-profiles']) && $component['authorization-profiles'] != ""){
            $profiles= str_replace(' ', '', $component['authorization-profiles']);
            $profiles= explode(',', $component['authorization-profiles']);
            return in_array($profileName, $profiles);
        }else{
            return TRUE;
        }
    }
}