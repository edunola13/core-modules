<?php
namespace Enola\Support\Cache;
use Enola\Support\DataBaseAR;

/**
 * Esta clase representa al driver para la cache mediante Base de Datos
 * Implementa la interface cache accediendo a la base de datos especificada mediante la clase provista por el framework
 * @author Eduardo Sebastian Nola <edunola13@gmail.com>
 * @category Enola\Cache
 */
class CacheDataBase extends CacheUtils implements CacheInterface{
    /** Conexion a Base de Datos a utilizar
     * @var string */
    public $nameDB;
    /** Tabla de la Base de Datos a utilizar
     * @var string */
    public $table;
    /** Referencia a la DataBaseAR 
     * @var DataBaseAR */
    public $connection;
    /**
     * Constructor - Inicia una conexion a la base de datos en base a la definicion seleccionada
     * @param string $folder
     */
    public function __construct($nameDB, $table) {
        $this->nameDB= $nameDB;
        $this->table= $table;
        $this->connection= new DataBaseAR(TRUE, $nameDB);
    }
    /**
     * Setea la conexion a la base de datos en base a la definicion seleccionada y la tabla indicada
     * @param string $nameDB
     * @param string $table
     */
    public function setConnection($nameDB, $table){
        $this->nameDB= $nameDB;
        $this->table= $table;
        $this->connection= $this->connection->changeConnection($nameDB);
    }
    /**
     * Devuelve si existe un dato guardado en cache con esa clave
     * @param string $key
     * @return boolean
     */
    public function exists($key){
        return (bool)$this->get($key);
    }
    /**
     * Devuelve un valor guardado en cache o null si no existe
     * @param string $key
     * @return type
     */
    public function get($key){
        $result= $this->connection->getFromWhere($this->table, 'keyCache = :key', array('key' => $key));
        $fila= $result->fetch();
        if($fila != NULL){
            //Unserialize los datos y veo que no esten corrompidos o se haya expirado el tiempo
            $data = $this->unPrepareData($fila['data']);
            if (!$data) {
                //Datos corrompidos, elimino la fila
                $this->delete($key);
                return NULL;
            }else if(time() > $data[0] && $data[0] != 0){
                //Se expiraron los datos, elimino el archivo
                $this->delete($key);
                return NULL;
            }
            return $data[1];
        }
        return NULL;
    }
    /**
     * Almacena un valor en cache asociado a una clave
     * @param string $key
     * @param type $data
     * @param int $ttl
     */
    public function store($key, $data, $ttl = 0) {
        if($ttl != 0){
            $ttl= time() + $ttl;
        }
        $data = $this->preperaData(array($ttl,$data));
        $this->delete($key);
        return $this->connection->insert($this->table, array('keyCache' => $key, 'data' => $data));
    }
    /**
     * Elimina un valor en cache asociado a una clave
     * @param string $key
     * @return boolean
     */
    public function delete($key){
        $this->connection->where('keyCache = :key', array('key' => $key));
        return $this->connection->delete($this->table);
    }
}