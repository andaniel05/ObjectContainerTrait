<?php

namespace Andaniel05\ObjectContainerTrait\tests;

use Andaniel05\ObjectContainerTrait\ObjectContainerTrait;

class Post {}

class Category {}

class ObjectContainerTraitTest extends \PHPUnit_Framework_TestCase
{
    protected $container;
    protected $userConfig;

    public function setUp()
    {
        $this->userConfig = array(
            array(
                'class'         => Post::class,
                'singular_name' => 'post',
                'plural_name'   => 'posts',
                'methods'       => array(
                    'add'    => 'addPost',
                    'get'    => 'getPost',
                    'delete' => 'deletePost',
                    'list'   => 'getAllPosts',
                ),
            ),
            array(
                'class'         => Category::class,
                'singular_name' => 'category',
                'plural_name'   => 'categories',
            ),
        );

        $this->container = $this->getMockBuilder(ObjectContainerTrait::class)
                ->setMethods(['oct_container_config'])
                ->getMockForTrait();
        $this->container->method('oct_container_config')
                ->willReturn($this->userConfig);
    }

    public function testObjectContainerTraitIsInitializedReturnFalseByDefault()
    {
        $this->assertFalse($this->container->oct_is_initialized());
    }

    public function testAutomaticInitializationOnAnyCall()
    {
        $this->container->call_any_method();

        $this->assertTrue($this->container->oct_is_initialized());
    }

    public function testAutomaticInitializationOnOtcGetCallInfo()
    {
        $this->container->oct_get_call_info('addPost');

        $this->assertTrue($this->container->oct_is_initialized());
    }

    /**
     * @expectedException Andaniel05\ObjectContainerTrait\Exception\Config\ClassNotSpecifiedException
     */
    public function testOctLoadContainerConfigThrowClassNotSpecifiedException()
    {
        $this->container->oct_load_container_config(array());
    }

    /**
     * @expectedException Andaniel05\ObjectContainerTrait\Exception\Config\SingularNameNotSpecifiedException
     */
    public function testOctLoadContainerConfigThrowSingularNameNotSpecifiedException()
    {
        $this->container->oct_load_container_config(array(
            'class' => Post::class,
        ));
    }

    /**
     * @expectedException Andaniel05\ObjectContainerTrait\Exception\Config\PluralNameNotSpecifiedException
     */
    public function testOctLoadContainerConfigThrowPluralNameNotSpecifiedException()
    {
        $this->container->oct_load_container_config(array(
            'class'         => Post::class,
            'singular_name' => 'post',
        ));
    }

    public function testOctLoadContainerConfigWhenInstanceIfInitializedNotIsExecuted()
    {
        // Inicializa
        $this->container->call_any_method();

        // Como ya está inicializado no lanza la excepción.
        $this->container->oct_load_container_config(array());
    }

    public function testInitializationInvokeOctLoadContainerConfigForEachConfigDefinition()
    {
        $container = $this->getMockBuilder(ObjectContainerTrait::class)
                ->setMethods(['oct_load_container_config'])
                ->getMockForTrait();
        $container->method('oct_container_config')
                ->willReturn($this->userConfig);
        $container->expects($this->exactly(2))
            ->method('oct_load_container_config')
            ->withConsecutive(
                [$this->equalTo($this->userConfig[0])],
                [$this->equalTo($this->userConfig[1])]
            );

        $container->call_any_method();
    }

    public function testConfigIsEqualToUserConfigInFirstTestCase()
    {
        $this->container->call_any_method();

        $this->assertEquals(
            $this->userConfig[0],
            $this->container->oct_get_config()['post']
        );
    }

    public function testConfigAddMethodsInSecondTestCase()
    {
        $this->container->call_any_method();

        $methods = array(
            'methods' => array(
                'add'    => 'addCategory',
                'get'    => 'getCategory',
                'delete' => 'deleteCategory',
                'list'   => 'getAllCategories',
            ),
        );

        $categoryConfig = $this->userConfig[1];
        $categoryConfig = array_merge($categoryConfig, $methods);

        $this->assertEquals(
            $categoryConfig,
            $this->container->oct_get_config()['category']
        );
    }

    public function testOctGetCallInfoResultForAddPost()
    {
        $methodInfo = $this->container->oct_get_call_info('addPost');

        $this->assertEquals('post', $methodInfo['type']);
        $this->assertEquals('add', $methodInfo['action']);
    }

    public function testOctGetCallInfoResultForGetPost()
    {
        $methodInfo = $this->container->oct_get_call_info('getPost');

        $this->assertEquals('post', $methodInfo['type']);
        $this->assertEquals('get', $methodInfo['action']);
    }

    public function testOctGetCallInfoResultForDeletePost()
    {
        $methodInfo = $this->container->oct_get_call_info('deletePost');

        $this->assertEquals('post', $methodInfo['type']);
        $this->assertEquals('delete', $methodInfo['action']);
    }

    public function testOctGetCallInfoResultForGetAllPosts()
    {
        $methodInfo = $this->container->oct_get_call_info('getAllPosts');

        $this->assertEquals('post', $methodInfo['type']);
        $this->assertEquals('list', $methodInfo['action']);
    }

    public function getCustomContainer()
    {
        $userConfig = array(
            array(
                'class'         => Post::class,
                'singular_name' => 'post',
                'plural_name'   => 'posts',
                'methods'       => array(
                    'add'    => 'insertPost',
                    'get'    => false,
                    'delete' => 'removePost',
                    'list'   => 'listAllPosts',
                ),
            ),
        );

        $container = $this->getMockBuilder(ObjectContainerTrait::class)
                ->setMethods(['oct_container_config'])
                ->getMockForTrait();
        $container->method('oct_container_config')
                ->willReturn($userConfig);

        return $container;
    }

    public function testOctGetCallInfoResultForInsertPostInCustomConfig()
    {
        $container = $this->getCustomContainer();

        $methodInfo = $container->oct_get_call_info('insertPost');

        $this->assertEquals('post', $methodInfo['type']);
        $this->assertEquals('add', $methodInfo['action']);
    }

    public function testOctGetCallInfoResultForGetPostInCustomConfig()
    {
        $container = $this->getCustomContainer();

        $methodInfo = $container->oct_get_call_info('getPost');

        $this->assertNull($methodInfo);
    }

    public function testOctGetDataReturnAnEmptyArrayByDefault()
    {
        $this->assertEmpty($this->container->oct_get_data());
    }

    public function testMagicCallInvokeToOctGetCallInfoWithSameCallData()
    {
        $container = $this->getMockBuilder(ObjectContainerTrait::class)
                ->setMethods(['oct_get_call_info'])
                ->getMockForTrait();
        $container->expects($this->once())
            ->method('oct_get_call_info')
            ->with($this->equalTo('addPost'));

        $container->addPost();
    }

    public function testInvocationOfOctAddAction_WhenCallInfoReturnAddAction()
    {
        $post = new Post();

        $container = $this->getMockBuilder(ObjectContainerTrait::class)
                ->setMethods(['oct_add_action', 'oct_get_call_info'])
                ->getMockForTrait();
        $container->method('oct_get_call_info')
            ->willReturn(array(
                'type'   => 'post',
                'action' => 'add',
            ));
        $container->expects($this->once())
            ->method('oct_add_action')
            ->with($this->equalTo('post'), $this->equalTo('post-id'), $this->equalTo($post));

        $container->addPost('post-id', $post);
    }

    public function testInvocationOfOctGetAction_WhenCallInfoReturnGetAction()
    {
        $container = $this->getMockBuilder(ObjectContainerTrait::class)
                ->setMethods(['oct_get_action', 'oct_get_call_info'])
                ->getMockForTrait();
        $container->method('oct_get_call_info')
            ->willReturn(array(
                'type'   => 'post',
                'action' => 'get',
            ));
        $container->expects($this->once())
            ->method('oct_get_action')
            ->with($this->equalTo('post'), $this->equalTo('post-id'));

        $container->getPost('post-id');
    }

    public function testInvocationOfOctDeleteAction_WhenCallInfoReturnDeleteAction()
    {
        $container = $this->getMockBuilder(ObjectContainerTrait::class)
                ->setMethods(['oct_delete_action', 'oct_get_call_info'])
                ->getMockForTrait();
        $container->method('oct_get_call_info')
            ->willReturn(array(
                'type'   => 'post',
                'action' => 'delete',
            ));
        $container->expects($this->once())
            ->method('oct_delete_action')
            ->with($this->equalTo('post'), $this->equalTo('post-id'));

        $container->deletePost('post-id');
    }

    public function testInvocationOfOctListAction_WhenCallInfoReturnListAction()
    {
        $container = $this->getMockBuilder(ObjectContainerTrait::class)
                ->setMethods(['oct_list_action', 'oct_get_call_info'])
                ->getMockForTrait();
        $container->method('oct_get_call_info')
            ->willReturn(array(
                'type'   => 'post',
                'action' => 'list',
            ));
        $container->expects($this->once())
            ->method('oct_list_action')
            ->with($this->equalTo('post'));

        $container->getAllPosts();
    }

    public function testInitializationOnCallToOctAddAction()
    {
        $this->container->oct_add_action('post', 'id', new Post());

        $this->assertTrue($this->container->oct_is_initialized());
    }

    public function testInitializationOnCallToOctGetAction()
    {
        $this->container->oct_get_action('post', 'id');

        $this->assertTrue($this->container->oct_is_initialized());
    }

    public function testInitializationOnCallToOctDeleteAction()
    {
        $this->container->oct_delete_action('post', 'id');

        $this->assertTrue($this->container->oct_is_initialized());
    }

    public function testInitializationOnCallToOctListAction()
    {
        $this->container->oct_list_action('post');

        $this->assertTrue($this->container->oct_is_initialized());
    }

    public function testOctDataHasArrayOfPosts()
    {
        $this->container->call_any_method();

        $data = $this->container->oct_get_data();

        $this->assertTrue(is_array($data['post']));
    }

    public function testOctDataHasArrayOfCategories()
    {
        $this->container->call_any_method();

        $data = $this->container->oct_get_data();

        $this->assertTrue(is_array($data['category']));
    }

    /**
     * @expectedException Andaniel05\ObjectContainerTrait\Exception\TypeNotConfiguredException
     */
    public function testOctAddActionThrowTypeNotConfiguredException()
    {
        $this->container->oct_add_action('car', 'id', null);
    }

    /**
     * @expectedException Andaniel05\ObjectContainerTrait\Exception\TypeNotConfiguredException
     */
    public function testOctGetActionThrowTypeNotConfiguredException()
    {
        $this->container->oct_get_action('car', 'id');
    }

    /**
     * @expectedException Andaniel05\ObjectContainerTrait\Exception\TypeNotConfiguredException
     */
    public function testOctDeleteActionThrowTypeNotConfiguredException()
    {
        $this->container->oct_delete_action('car', 'id');
    }

    /**
     * @expectedException Andaniel05\ObjectContainerTrait\Exception\TypeNotConfiguredException
     */
    public function testOctListActionThrowTypeNotConfiguredException()
    {
        $this->container->oct_list_action('car');
    }

    /**
     * @expectedException Andaniel05\ObjectContainerTrait\Exception\NotAllowedTypeException
     */
    public function testOctAddActionThrowNotAllowedTypeException()
    {
        $this->container->oct_add_action('post', 'category-id', new Category());
    }

    public function testOctGetActionReturnNullIfIdNotExists()
    {
        $this->assertNull($this->container->oct_get_action('post', 'post-id'));
    }

    public function testAddAndGetActions()
    {
        $post = new Post();

        $this->container->oct_add_action('post', 'post-id', $post);

        $this->assertSame($post, $this->container->oct_get_action('post', 'post-id'));
    }

    public function testOctDeleteAction()
    {
        $post = new Post();
        $this->container->oct_add_action('post', 'post-id', $post);

        $this->container->oct_delete_action('post', 'post-id');

        $this->assertNull($this->container->oct_get_action('post', 'post-id'));
    }

    public function testOctListAction()
    {
        $post1 = new Post();
        $post2 = new Post();
        $this->container->oct_add_action('post', 'post1', $post1);
        $this->container->oct_add_action('post', 'post2', $post2);

        $data = $this->container->oct_list_action('post');

        $this->assertSame($post1, $data['post1']);
        $this->assertSame($post2, $data['post2']);
    }

    public function testAddAndGetPost()
    {
        $post = new Post();

        $this->container->addPost('post-id', $post);

        $this->assertSame($post, $this->container->getPost('post-id'));
    }

    public function testDeletePost()
    {
        $post = new Post();
        $this->container->addPost('post-id', $post);

        $this->container->deletePost('post-id');

        $this->assertNull($this->container->getPost('post-id'));
    }

    public function testGetAllPosts()
    {
        $post1 = new Post();
        $post2 = new Post();
        $this->container->addPost('post1', $post1);
        $this->container->addPost('post2', $post2);

        $data = $this->container->getAllPosts();

        $this->assertSame($post1, $data['post1']);
        $this->assertSame($post2, $data['post2']);
    }

    public function testAutomaticInitializationOnOtcGetConfig()
    {
        $this->container->oct_get_config();

        $this->assertTrue($this->container->oct_is_initialized());
    }
}