<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * Behavior类 是所有行为类的基类（言外之意就是所有的行为类，都需要继承此类）。
 * Behavior is the base class for all behavior classes.
 *
 * A behavior can be used to enhance the functionality of an existing component without modifying its code.
 * In particular, it can "inject" its own methods and properties into the component
 * and make them directly accessible via the component. It can also respond to the events triggered in the component
 * and thus intercept the normal code execution.
 *
 * For more details and usage information on Behavior, see the [guide article on behaviors](guide:concept-behaviors).
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Behavior extends BaseObject
{
    /**
     * @var Component|null the owner of this behavior
     * owner 表示hehavior的所有者，即behavior所绑定到的Component.
     * 
     * 我们已经知道， behavior的作用是扩展Component的功能，而不需要修改组件的代码。
     * 所以当一个behavior使用的时候，需要绑定到一个具体的Component上面，目的是指明behavior到底是扩展哪个Component的功能。
     * 在绑定的时候, 使用[[attach($owner)]]， 就会给owner赋值为 behavior所绑定到的Component。
     */
    public $owner;

    /**
     * @var array Attached events handlers
     */
    private $_attachedEvents = [];


    /**
     * Declares event handlers for the [[owner]]'s events.
     * 为owner的事件，声明事件处理器。 
     * 特别注意， 这些事件必须是owner所声明的，如果不是，则无意义。 因为只有owner自己声明的事件，才会在owner中被触发（trigger）
     * Behavior是基类，所以我们并不知道它的各个子类要绑定到哪个Component上，自然也就不知道owner Component有哪些事件，所以默认值就是空数组。
     *
     * Child classes may override this method to declare what PHP callbacks should
     * be attached to the events of the [[owner]] component.
     * 子类可以通过override这个方法，指明 [[owner]] component 的事件应该绑定哪个回调方法
     *
     * The callbacks will be attached to the [[owner]]'s events when the behavior is
     * attached to the owner; and they will be detached from the events when
     * the behavior is detached from the component.
     * 当behavior绑定到owner Component的时候，回调函数将会被绑定到owner的事件上，当behavior从owner component解绑的时候，回调函数也将从事件中解绑。
     *
     * The callbacks can be any of the following:
     *
     * - method in this behavior: `'handleClick'`, equivalent to `[$this, 'handleClick']`
     * - object method: `[$object, 'handleClick']`
     * - static method: `['Page', 'handleClick']`
     * - anonymous function: `function ($event) { ... }`
     *
     * The following is an example:
     *
     * ```php
     * [
     *     Model::EVENT_BEFORE_VALIDATE => 'myBeforeValidate',
     *     Model::EVENT_AFTER_VALIDATE => 'myAfterValidate',
     * ]
     * ```
     *
     * @return array events (array keys) and the corresponding event handler methods (array values).
     */
    public function events()
    {
        return [];
    }

    /**
     * 将behavior对象，绑定到组件上。
     * 默认的实现会设置 [[owner]] 属性，并且会把在[[events]]中声明的事件，也绑定到owner上。
     * 如果你想要override这个方法，确保你要调用父类的实现。
     * Attaches the behavior object to the component.
     * The default implementation will set the [[owner]] property
     * and attach event handlers as declared in [[events]].
     * Make sure you call the parent implementation if you override this method.
     * @param Component $owner the component that this behavior is to be attached to.
     */
    public function attach($owner)
    {
        $this->owner = $owner;
        foreach ($this->events() as $event => $handler) {
            $this->_attachedEvents[$event] = $handler;
            // 将behavior中设置的事件，绑定到hehavior的owner上。
            $owner->on($event, is_string($handler) ? [$this, $handler] : $handler);
        }
    }

    /**
     * Detaches the behavior object from the component.
     * The default implementation will unset the [[owner]] property
     * and detach event handlers declared in [[events]].
     * Make sure you call the parent implementation if you override this method.
     */
    public function detach()
    {
        if ($this->owner) {
            foreach ($this->_attachedEvents as $event => $handler) {
                $this->owner->off($event, is_string($handler) ? [$this, $handler] : $handler);
            }
            $this->_attachedEvents = [];
            $this->owner = null;
        }
    }
}
