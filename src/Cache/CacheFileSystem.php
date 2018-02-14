<?php
namespace Enola\Support\Cache;
use Enola\EnolaContext;

/**
 * Esta clase representa al driver para la cache mediante FileSystem
 * Implementa la interface cache accediendo a la carpeta seleccionada
 * @author Eduardo Sebastian Nola <edunola13@gmail.com>
 * @category Enola\Cache
 */
class CacheFileSystem extends CacheUtils implements CacheInterface{
    /** Carpeta donde se almacenara la cache
     * @var string */
    public $folder;
    /**
     * Constructor
     * Guarda la carpeta a utilizar, la carpeta se debe indicar desde PathApp
     * @param string $folder
     */
    public function __construct($folder) {
        $this->folder= EnolaContext::getInstance()->getPathApp() . $folder . '/';
    } 
    /**
     * Retorna la ubicacion de la clave
     * @param string $key
     * @return string
     */
    protected function getFileName($key) {        
        return $this->folder . $key;
    }
    /**
     * Devuelve si existe un dato guardado en cache con esa clave
     * @param string $key
     */
    public function exists($key) {
        return (bool) $this->get($key);
    }
    /**
     * Devuelve un valor guardado en cache o null si no existe
     * @param string $key
     */
    public function get($key) {
        //Consigo la ubicacion del archivo
        $filename = $this->getFileName($key);
        //Si no existe devuelvo NULL
        if (!file_exists($filename)){return NULL;}
        //Abro el archivo en solo lectura
        $file = fopen($filename,'r');
        if(!$file){return NULL;}
        //Consigo un bloqueo compartido de solo lectura
        flock($file,LOCK_SH);
        //Leo el contenido y cierro el archivo
        $fileString = file_get_contents($filename);
        fclose($file);
        //Unserialize los datos y veo que no esten corrompidos o se haya expirado el tiempo
        $data = $this->unPrepareData($fileString);
        if (!$data) {
           //Datos corrompidos, elimino el archivo
           unlink($filename);
           return NULL;
        }else if(time() > $data[0] && $data[0] != 0){
           //Se expiraron los datos, elimino el archivo
           unlink($filename);
           return NULL;
        }
        return $data[1];
    }
    /**
     * Almacena un valor en cache asociado a una clave
     * @param string $key
     * @param type $data
     * @param int $ttl
     */
    public function store($key,$data,$ttl=0) {
        //Abro/Creo el archivo en modo lectura/escritura
        $file = fopen($this->getFileName($key),'a+');
        if(!$file){return FALSE;}
        //Consigo un bloqueo exclusivo
        flock($file,LOCK_EX);
        //Trunco el archivo en caso de que existieran datos viejos
        ftruncate($file,0);
        //Serializo los datos y los guardo con una fecha de expiracion , si es 0 se lo deja indefinidamente
        if($ttl != 0){
            $ttl= time() + $ttl;
        }
        $data = $this->preperaData(array($ttl,$data));
        if (fwrite($file,$data)===false) {return FALSE;}
        return fclose($file);
    }
    /**
     * Elimina un valor en cache asociado a una clave
     * @param string $key
     */
    public function delete($key) {
        $filename = $this->getFileName($key);
        if (file_exists($filename)) {
            return unlink($filename);
        }else{
            return false;
        }
    }    
}