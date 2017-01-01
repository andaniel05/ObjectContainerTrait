<?php

namespace Andaniel05\ObjectContainerTrait;

use Andaniel05\ObjectContainerTrait\Exception\Config\{ClassNotSpecifiedException,
SingularNameNotSpecifiedException, PluralNameNotSpecifiedException};
use Andaniel05\ObjectContainerTrait\Exception\{TypeNotConfiguredException, NotAllowedTypeException};

/**
 * Convierte a una clase PHP en contenedora de objetos.
 *
 * Cuando una clase usa este trait y define una configuración adecuada para el mismo
 * entonces puede realizar operaciones de tipo "add", "get", "delete" y "list" sobre
 * dichos objetos. Se pueden usar todas o algunas de esas operaciones. Las mismas se
 * realizan a través de métodos virtuales donde los nombres de los mismos puede ser
 * configurable en la configuración.
 *
 * Para una mayor comprensión recomendamos ver la documentación.
 *
 * @author Andy D. Navarro Taño <andaniel05@gmail.com>
 * @version 1.0.0
 */
trait ObjectContainerTrait
{
    /**
     * Bandera que indica si el contenedor está o no inicializado.
     *
     * @var boolean
     */
    protected $oct_initialized = false;

    /**
     * Array de configuración especificado por el usuario.
     *
     * @var array
     */
    protected $oct_user_config = [];

    /**
     * Array de configuración.
     *
     * Se obtiene después de procesar el array de configuración especificado
     * por el usuario.
     *
     * @var array
     */
    protected $oct_config = [];

    /**
     * Contenedor de datos.
     *
     * @var array
     */
    protected $oct_data = [];

    /**
     * Devuelve la configuración especificada por el usuario.
     *
     * Ver la documentación para conocer la sintaxis.
     *
     * @abstract
     * @access protected
     *
     * @return array
     */
    abstract protected function oct_container_config() : array;

    /**
     * Indica si el contenedor está o no inicializado.
     *
     * @return bool
     */
    public function oct_is_initialized() : bool
    {
        return $this->oct_initialized;
    }

    /**
     * Inicializa el contenedor.
     *
     * @return null
     */
    public function oct_initialize()
    {
        if (true == $this->oct_initialized)
            return;

        $this->oct_user_config = $this->oct_container_config();

        if (true == is_array($this->oct_user_config)) {
            foreach ($this->oct_user_config as $config) {
                $this->oct_load_container_config($config);
            }
        }

        $this->oct_initialized = true;
    }

    /**
     * Ejecuta la acción determinada.
     *
     * Cuando se produce una llamada sobre un método inexistente este método es
     * invocado. Primeramente se obtiene información de la llamada y si se corresponde
     * con alguna de las relaciones entre acción y tipo existentes en la configuración
     * entonces se procesa dicha acción.
     *
     * @param  string $method    Nombre del método llamado.
     * @param  array $arguments  Argumentos de la llamada al método.
     *
     * @return mixed El tipo devuelto depende de la acción ejecutada.
     */
    public function __call($method, $arguments)
    {
        $this->oct_check_initialization();

        $callInfo = $this->oct_get_call_info($method);

        if (null == $callInfo) {
            return;
        }

        $actionMethod = '';
        switch ($callInfo['action']) {

            case 'add':
                $actionMethod = 'oct_add_action';
                break;

            case 'get':
                $actionMethod = 'oct_get_action';
                break;

            case 'delete':
                $actionMethod = 'oct_delete_action';
                break;

            case 'list':
                $actionMethod = 'oct_list_action';
                break;

            default:
                return;
        }

        $args = array($callInfo['type']);
        $args = array_merge($args, $arguments);

        if (true == method_exists($this, $actionMethod)) {
            return call_user_func_array(array($this, $actionMethod), $args);
        }
    }

    /**
     * Procesa un array de configuración.
     *
     * @param  array  $config
     * @return null
     */
    public function oct_load_container_config(array $config)
    {
        if (true == $this->oct_initialized) {
            return;
        }

        if (false == isset($config['class'])) {
            throw new ClassNotSpecifiedException();
        }

        if (false == isset($config['singular_name'])) {
            throw new SingularNameNotSpecifiedException();
        }

        if (false == isset($config['plural_name'])) {
            throw new PluralNameNotSpecifiedException();
        }

        $validConfig = $config;
        if (false == isset($validConfig['methods'])) {

            // Convierte a mayúscula la primera letra del nombre singular.
            $singularName = $config['singular_name'];
            $singularName[0] = strtoupper($singularName[0]);

            // Convierte a mayúscula la primera letra del nombre plural.
            $pluralName = $config['plural_name'];
            $pluralName[0] = strtoupper($pluralName[0]);

            $validConfig['methods'] = array(
                'add'    => "add$singularName",
                'get'    => "get$singularName",
                'delete' => "delete$singularName",
                'list'   => "getAll$pluralName",
            );
        }

        $this->oct_config[$validConfig['singular_name']] = $validConfig;
        $this->oct_data[$validConfig['singular_name']] = array();
    }

    /**
     * Devuelve el array configuración.
     *
     * @return array
     */
    public function oct_get_config() : array
    {
        $this->oct_check_initialization();

        return $this->oct_config;
    }

    /**
     * Devuelve información de una llamada a un método.
     *
     * El array devuelto indica el tipo y la acción.
     *
     * @param  string $method Nombre del método llamado.
     * @return array|null
     */
    public function oct_get_call_info(string $method)
    {
        $this->oct_check_initialization();

        $type = null;
        $action = null;

        foreach ($this->oct_config as $config) {
            foreach ($config['methods'] as $methodAction => $methodName) {
                if ($method == $methodName) {
                    $type   = $config['singular_name'];
                    $action = $methodAction;
                    break;
                }
            }

            if (null != $type || null != $action) {
                break;
            }
        }

        $result = null;
        if (null != $type || null != $action) {
            $result = array(
                'type'   => $type,
                'action' => $action,
            );
        }

        return $result;
    }

    /**
     * Devuelve el contenedor de datos.
     *
     * @return array
     */
    public function oct_get_data() : array
    {
        return $this->oct_data;
    }

    /**
     * Ejecuta la acción "add".
     *
     * @param  string $type   Tipo bajo el cual se va a insertar.
     * @param  string $id     Identificador con el que se va a insertar la entidad.
     * @param  object $entity Entidad a insertar.
     *
     * @throws TypeNotConfiguredException Se dispara esta excepción si el tipo especificado no existe.
     * @throws NotAllowedTypeException Se dispara esta excepción si el tipo de la entidad a insertar no se corresponde con el tipo permitido.
     *
     * @return null
     */
    public function oct_add_action(string $type, string $id, $entity)
    {
        $this->oct_check_initialization();
        $this->oct_check_type($type);

        if (false == $entity instanceOf $this->oct_config[$type]['class']) {
            throw new NotAllowedTypeException($type, get_class($entity));
        }

        $this->oct_data[$type][$id] = $entity;
    }

    /**
     * Ejecuta la acción "get".
     *
     * @param  string $type Tipo para el cuál se hará la búsqueda.
     * @param  string $id   Identificador.
     *
     * @throws TypeNotConfiguredException Se dispara esta excepción si el tipo especificado no existe.
     *
     * @return null
     */
    public function oct_get_action(string $type, string $id)
    {
        $this->oct_check_initialization();
        $this->oct_check_type($type);

        return $this->oct_data[$type][$id] ?? null;
    }

    /**
     * Ejecuta la acción "delete".
     *
     * @param  string $type Tipo para el cuál se hará la búsqueda.
     * @param  string $id   Identificador.
     *
     * @throws TypeNotConfiguredException Se dispara esta excepción si el tipo especificado no existe.
     *
     * @return null
     */
    public function oct_delete_action(string $type, string $id)
    {
        $this->oct_check_initialization();
        $this->oct_check_type($type);

        unset($this->oct_data[$type][$id]);
    }

    /**
     * Ejecuta la acción "list".
     *
     * @param  string $type Tipo para el cuál se hará la búsqueda.
     *
     * @throws TypeNotConfiguredException Se dispara esta excepción si el tipo especificado no existe.
     *
     * @return array
     */
    public function oct_list_action(string $type) : array
    {
        $this->oct_check_initialization();
        $this->oct_check_type($type);

        return $this->oct_data[$type];
    }

    protected function oct_check_initialization()
    {
        if (false == $this->oct_initialized) {
            $this->oct_initialize();
        }
    }

    protected function oct_check_type(string $type)
    {
        if (false == isset($this->oct_data[$type])) {
            throw new TypeNotConfiguredException($type);
        }
    }
}