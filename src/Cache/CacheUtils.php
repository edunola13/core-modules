<?php
namespace Enola\Support\Cache;

/**
 * Esta clase abstracta contiene funcionalidad compartida entre todas las instancias de cache
 * @author Eduardo Sebastian Nola <edunola13@gmail.com>
 * @category Enola\Cache
 */
abstract class CacheUtils{
    /**
     * Prepara los datos antes de guardarlos en cache
     * @param mixed $data
     * @return string
     */
    protected function preperaData($data){
        if(is_string($data)){
            return $data;
        }else{
            return serialize($data);
        }
    }
    /**
     * Retorna los datos exactamente como se cachearon
     * @param string $data
     * @return mixed
     */
    protected function unPrepareData($data){
        $dataUn = @unserialize($data);
        if ($data === 'b:0;' || $dataUn !== false) {
            return $dataUn;
        } else {
            echo $data;
        }
    }
}