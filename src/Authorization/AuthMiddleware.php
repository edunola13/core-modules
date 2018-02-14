<?php
namespace Enola\Support\Authorization;
/**
 * Interface para los distintos middlewares que puede tener la clase Authorization
 * Esta nos sirve para abstraer a la clase principal del metodo de almacenamiento utilizado
 * @author Eduardo Sebastian Nola <edunola13@gmail.com>
 * @category Enola\Support
 */
interface AuthMiddleware{
    /**
     * Retorna todos los modulos de la aplicacion
     * @return array
     */
    public function getModules();
    /**
     * Retorna un determinado modulo o NULL si no existe
     * @param string $name
     * @return array
     */
    public function getModule($name);
    /**
     * Retorna todos los profiles de la aplicacion
     * @return array
     */
    public function getProfiles();
    /**
     * Retorna un determinado profile o NULL si no existe
     * @param string $name
     * @return array
     */
    public function getProfile($name);
}
