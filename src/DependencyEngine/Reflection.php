<?php
namespace Enola\Support\DependencyEngine;
/**
 * Esta clase se encarga de acceder y setar las propiedades de los distintos objetos y clases.
 * @author Eduardo Sebastian Nola <edunola13@gmail.com>
 * @category Enola\Support
 */
class Reflection{
    protected $object;
    /** @var \ReflectionObject */
    protected $reflection;
    /**
     * Cosntructor - Se le pasa el objeto a tratar
     * @param type $object
     */
    public function __construct($object) {
        $this->setObject($object);
    }
    public function setObject($object){
        $this->object= $object;
        //Crea un Reflection para acceder a las caracteristicas del objeto
        $this->reflection= new \ReflectionObject($object); 
    }
    /**
     * Lee una propiedad de un objeto. Para poder la propiedad debe exister el metodo get public y/o ser una variable
     * de acceso public. Primero intenta con el metodo get.
     * @param string $property
     * @return type
     */
    public function getProperty($property){        
        //Primero Busco por get
        $getMethod= 'get' . strtoupper($property[0]) . substr($property, 1);                     
        if($this->reflection->hasMethod($getMethod)){
            $reflectionMethod= $this->reflection->getMethod($getMethod);
            //Si existe el metodo set y es public lo seteo
            if($reflectionMethod->isPublic()){
                return $this->object->$getMethod();
            }            
        }else if($this->reflection->hasProperty($property)){
            //Si existe la propiedad y es public la seteo
            $reflectionProperty= $this->reflection->getProperty($property);
            if($reflectionProperty->isPublic()){
                return $this->object->$property;
            }
        }
        return NULL;
    }
    /**
     * Retorna un array con todos los valores de las propiedades.
     * Para leer cada propiedad llama al metodo getProperty.
     * @param array $properties
     * @return array - propertyName => value
     */
    public function getProperties($properties){
        $values= array();
        foreach ($properties as $key) {
            $values[$key]= $this->getProperty($key);
        }
        return $values;
    }
    /**
     * Setea la propiedad de un objeto. Para ahcer esto busca que el metodo set exista y sea publica, despues que la
     * variable tenga visibilidad publica y por ultimo si se indica que se cree la propiedad se crea (en caso de que no exista).
     * Si la variable existe pero no se puede aceder no la setea
     * @param string $property
     * @param type $value
     * @param boolean $create
     */
    public function setProperty($property, $value, $create = FALSE){
        //Primero Busco por set
        $setMethod= 'set' . strtoupper($property[0]) . substr($property, 1);                     
        if($this->reflection->hasMethod($setMethod)){
            $reflectionMethod= $this->reflection->getMethod($setMethod);
            //Si existe el metodo set y es public lo seteo
            if($reflectionMethod->isPublic()){
                $this->object->$setMethod($value);
            }            
        }else if($this->reflection->hasProperty($property)){
            //Si existe la propiedad y es public la seteo
            $reflectionProperty= $this->reflection->getProperty($property);
            if($reflectionProperty->isPublic()){
                $this->object->$property= $value;
            }
        }else if($create){
            //Si no existe la variable y esta seteado que se cree, la misma es creada y seteada
            $this->object->$property= $value;
        }
    }
    /**
     * Setea las propiedades de un objeto.
     * Para setear cada valor llama al metodo setProperty
     * @param array $properties
     * @param boolean $create 
     */
    public function setProperties($properties, $create = FALSE){
        foreach ($properties as $key => $value) {
            $this->setProperty($key, $value, $create);
        }
    }   
}