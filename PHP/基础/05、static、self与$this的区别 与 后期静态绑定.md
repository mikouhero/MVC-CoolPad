# static、self与$this的区别 与 后期静态绑定

##### static
     static 可以用于静态或非静态方法中，也可以访问类的静态属性、静态方法、常量和非静态方法，但不能访问非静态属性

##### self
    可以用于访问类的静态属性、静态方法和常量，但 self 指向的是当前定义所在的类，这是 self 的限制
##### $this  
    指向的是实际调用时的对象，也就是说，实际运行过程中，谁调用了类的属性或方法，$this 指向的就是哪个对象。但 $this 不能访问类的静态属性和常量，且 $this 不能存在于静态方法中


##### 范围解析操作符  （::）

- 可以用于访问静态成员，类常量，还可以用于覆盖类中的属性和方法。  
- self，parent 和 static 这三个特殊的关键字是用于- 在类定义的内部对其属性或方法进行访问的。  
parent用于调用父类中被覆盖的属性或方法（出现在哪里，就将解析为相应类的父类）。  
- self用于调用本类中的方法或属性（出现在哪里，就将解析为相应的类；注意与$this区别，$this指向当前实例化的对象）。  
- 当一个子类覆盖其父类中的方法时，PHP 不会调用父类中已被覆盖的方法。是否调用父类的方法取决于子类。  


#####  PHP内核将类的继承实现放在了"编译阶段"
- self::和parent::出现在某个类X的定义中，则将被解析为相应的类X，除非在子类中覆盖父类的方法。
```php
class A{
    const H = 'A';
    const J = 'A';
    static function testSelf(){
        echo self::H; //在编译阶段就确定了 self解析为 A
    }
}
class B extends A
{
    const H = "B";
    const J = 'B';
    static function testParent()
    {
        echo parent::J; //在编译阶段就确定了 parent解析为A
    }
    /* 若重写testSelf则能输出“B”, 且C::testSelf()也是输出“B”
    static function testSelf()
    {
        echo self::H;
    }
    */
}
class C extends B{
    const H = "C";
    const J = 'C';
}
B::testParent(); // A
B::testSelf();  // A
C::testParent(); // A 
C::testSelf();  // A
```

##### 后期静态绑定
>自 PHP 5.3.0 起，PHP 增加了一个叫做后期静态绑定的功能，用于在继承范围内引用静态调用的类。   
准确说，后期静态绑定工作原理是存储了在上一个"非转发调用"（non-forwardingcall）的类名。当进行静态方法调用时，该类名即为明确指定的那个（通常在 :: 运算符左侧部分）；当进行非静态方法调用时，即为该对象所属的类。所谓的"转发调用"（forwardingcall）指的是通过以下几种方式进行的静态调用：self::，parent::，static:: 以及 forward_static_call()。可用 get_called_class() 函数来得到被调用的方法所在的类名，static:: 则指出了其范围。   
该功能从语言内部角度考虑被命名为"后期静态绑定"。"后期绑定"的意思是说，static:: 不再被解析为定义当前方法所在的类，而是在实际运行时计算的。也可以称之为"静态绑定"，因为它可以用于（但不限于）静态方法的调用。

- 转发调用 ：
>  指的是通过以下几种方式进行的静态调用：self::，parent::，static:: 以及 forward_static_call()。

- 非转发调用
> 明确指定类名的静态调用（例如Foo::foo()）  
非静态调用（例如$foo->foo()）  

```php
class A {
 public static function foo() {
  echo __CLASS__."\n";
  static::who();
 }
 
 public static function who() {
  echo __CLASS__."\n";
 }
}
 
class B extends A {
 public static function test() {
  echo "A::foo()\n";
  A::foo();
  echo "parent::foo()\n";
  parent::foo();
  echo "self::foo()\n";
  self::foo();
 }
 
 public static function who() {
  echo __CLASS__."\n";
 }
}
class C extends B {
 public static function who() {
  echo __CLASS__."\n";
 }
}
 
C::test();
 
/*
 * C::test(); //非转发调用 ，进入test()调用后，“上一次非转发调用”存储的类名为C
 *
 * //当前的“上一次非转发调用”存储的类名为C
 * public static function test() {
 *  A::foo(); //非转发调用， 进入foo()调用后，“上一次非转发调用”存储的类名为A，然后实际执行代码A::foo(), 转 0-0
 *  parent::foo(); //转发调用， 进入foo()调用后，“上一次非转发调用”存储的类名为C， 此处的parent解析为A ,转1-0
 *  self::foo(); //转发调用， 进入foo()调用后，“上一次非转发调用”存储的类名为C， 此处self解析为B, 转2-0
 * }
 *
 *
 * 0-0
 * //当前的“上一次非转发调用”存储的类名为A
 * public static function foo() {
 *  static::who(); //转发调用， 因为当前的“上一次非转发调用”存储的类名为A， 故实际执行代码A::who(),即static代表A，进入who()调用后，“上一次非转发调用”存储的类名依然为A，因此打印 “A”
 * }
 *
 * 1-0
 * //当前的“上一次非转发调用”存储的类名为C
 * public static function foo() {
 *  static::who(); //转发调用， 因为当前的“上一次非转发调用”存储的类名为C， 故实际执行代码C::who(),即static代表C，进入who()调用后，“上一次非转发调用”存储的类名依然为C，因此打印 “C”
 
 * }
 *
 * 2-0
 * //当前的“上一次非转发调用”存储的类名为C
 * public static function foo() {
 *  static::who(); //转发调用， 因为当前的“上一次非转发调用”存储的类名为C， 故实际执行代码C::who(),即static代表C，进入who()调用后，“上一次非转发调用”存储的类名依然为C，因此打印 “C”
 * }
 */
```


