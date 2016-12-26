ObjectContainerTrait
====================

Convierte a una clase PHP en contenedora de objetos.

Cuando una clase usa y configura correctamente este trait automáticamente
recibe unos métodos para las operaciones de insertar un objeto, obtener un objeto,
eliminar objetos existentes y obtener todos los objetos existentes. Los nombres de
esos métodos pueden ser configurables o deducidos automáticamente.

Si una clase es contenedora de otros tipos de objetos, por ejemplo una clase 'Post'
que sea contenedora de objetos 'Category', hacer uso de este trait puede resultar
útil pues simplifica las tareas repetitivas de implementar las operaciones de insertar,
buscar, listar y eliminar esos tipos de objeto.

## Ejemplo de configuración.

```php
<?php

class Category
{
    public $name;
}

class Post
{
    public $title;
    public $content;
}

/**
 * Clase contenedora.
 */
class MyContainerClass
{
    use Andaniel05\ObjectContainerTrait\ObjectContainerTrait;

    /**
     * Es obligatorio definir esta función pues es donde se configura
     * los tipos de objetos que van a ser contenidos en la clase.
     * Además es un contrato existente en el trait.
     *
     * En este ejemplo la clase va a contener objetos de tipo 'Post' y 'Category'.
     *
     * @return array
     */
    protected function oct_container_config() : array
    {
        return array(
            array(
                'class'         => Post::class,
                'singular_name' => 'post',
                'plural_name'   => 'posts',
                'methods'       => array(
                    'add'    => 'addPost',
                    'get'    => 'getPost',
                    'delete' => false, // En este caso no va a existir un método para la eliminación.
                    'list'   => 'getAllPosts',
                ),
            ),
            array(
                'class'         => Category::class,
                'singular_name' => 'category',
                'plural_name'   => 'categories',
            ),
        );
    }
}

```

## Ejemplo de uso.

```php
// ...

$post1 = new Post();
$post2 = new Post();

$category1 = new Category();
$category2 = new Category();

$container = new MyContainerClass();

$container->addPost('post1', $post1); // Insertar especificando el id.
$container->addPost('post2', $post2); // Insertar especificando el id.
$container->getPost('post1'); // Buscar objetos por su id.
$container->getAllPosts(); // Listar todos los objetos.

// En este caso se han deducido los nombres de los métodos.
$container->addCategory('category1', $category1);
$container->addCategory('category2', $category2);
$container->getCategory('category1');
$container->deleteCategory('category1');
$container->getAllCategories();

```