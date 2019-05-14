1 字符串
1.1 少用正则表达式

能用PHP内部字符串操作函数的情况下，尽量用他们，不要用正则表达式， 因为其效率高于正则。

没得说，正则最耗性能。

str_replace函数要比preg_replace快得多，strtr函数又比str_replace来得快。

有没有你漏掉的好用的函数？

例如：strpbrk()、strncasecmp()、strpos()、strrpos()、stripos()、strripos()。

1.2 字符替换

如果需要转换的全是单个字符，用字符串作为 strtr() 函数完成替换，而不是数组：

$addr = strtr($addr, "abcd", "efgh");       // 建议
$addr = strtr($addr, array('a' => 'e', ));  // 不建议
效率提升：10 倍。

str_replace字符替换比正则替换preg_replace快，但strtr比str_replace又快1/4。

另外，不要做无谓的替换，即使没有替换，str_replace也会为其参数分配内存。很慢！

用 strpos 先查找（非常快），看是否需要替换，如果需要，再替换。

如果需要替换，效率几乎相等，差别在 0.1% 左右。

如果不需要替换：用 strpos 快 200%。

1.3 压缩大的字符串

使用 gzcompress() 和 gzuncompress() 对容量大的字符串进行压缩和解压，再存入和取出数据库。

这种内置的函数使用gzip算法，能压缩字符串90%。

1.4 echo 输出

echo 字符串用逗号代替点连接符更快些。

虽然，echo是一种语言结构，不是真正的函数。

但是，它可以把逗号隔开的多个字符串当作“函数”参数传入，所以速度会更快。

echo $str1, $str2;       // 速度快
echo $str1 . $str2;      // 速度稍慢
1.5 尽量用单引号

PHP 引擎允许使用单引号和双引号来封装字符串变量，但是它们的速度是有很大的差别的！

使用双引号的字符串会告诉 PHP 引擎，首先去读取字符串内容，查找其中的变量，并改为变量对应的值。

一般来说字符串是没有变量的，使用双引号会导致性能不佳。

最好使用字符串连接，而不是双引号字符串。

$output = "This is a plain string";  // 不好的实践
$output = 'This is a plain string';  // 好的实践

$type = "mixed";                     // 不好的实践
$output = "This is a $type string";

$type = 'mixed';                     // 好的实践
$output = 'This is a ' . $type . ' string';
1.6 使用isset代替strlen

在检验字符串长度时，我们第一想法会使用 strlen() 函数。

此函数执行起来相当快，因为它不做任何计算，只返回在zval结构（C的内置数据结构，用于存储PHP变量）中存储的已知字符串长度。

但是，由于strlen()是函数，多多少少会有些慢，因为函数调用会经过诸多步骤，如字母小写化、哈希查找，会跟随被调用的函数一起执行。

在某些情况下，你可以使用 isset() 技巧加速执行你的代码。例如：

if (strlen($foo) < 5) {
    echo "Foo is too short";
}

// 使用isset()
if (!isset($foo{5})) {
    echo "Foo is too short";
}
1.7 用split分割字符串

在分割字符串时，split()要比explode()快。

split()
0.001813 - 0.002271 seconds (avg 0.002042 seconds)
explode()
0.001678 - 0.003626 seconds (avg 0.002652 seconds)
1.8 echo效率高于print

因为echo没有返回值，print返回一个整型。

注意：echo输出大字符串的时候，如果没有调整就会严重影响性能。

打开Apache的mod_deflate进行压缩，或者打开ob_start将内容放进缓冲区，可以改善性能问题。

2 语句
2.1 最好不用@

用@掩盖错误会降低脚本运行速度，并且在后台有很多额外操作。

用@比起不用，效率差距 3 倍。特别不要在循环中使用@。

在 5 次循环的测试中，即使是先用error_reporting(0)关掉错误，循环完成后再打开，都比用@快。

2.2 避免使用魔术方法

对于__开头的函数就命名为魔术函数，它们都在特定的条件下触发。

这些魔术函数包括：__construct()、__get()、__call()、__autoload()等等。

以__autoload() 为例，如果不能将类名与实际的磁盘文件对应起来，将不得不做大量的文件存在判断。

而判断文件存在需要磁盘I/O操作，众所周知，磁盘I/O操作的效率很低，因此这才是使得autoload机制效率降低的原因。

因此，在系统设计时，需要定义一套清晰的、将类名与实际磁盘文件映射的机制。

这个规则越简单越明确，__autoload()机制的效率就越高。

autoload机制并不是天然的效率低下，只有滥用autoload、设计不好的自动装载函数，才会导致其效率的降低.

所以说，尽量避免使用__autoload等魔术方法，有待商榷。

2.3 别在循环里用函数

例如：

for($x=0; $x < count($array); $x++) {
}
这种写法在每次循环的时候都会调用 count() 函数，效率大大降低，建议这样：

$len = count($array);
for($x=0; $x < $len; $x++) {
}
让函数在循环外面一次获得循环次数。

2.4 使用三元运算符

在简单的判断语句中，三元运算符?:更简洁高效。

2.5 使用选择分支语句

switch、case好于使用多个if、else if语句，并且代码更加容易阅读和维护。

2.6 屏蔽敏感信息

使用 error_reporting() 函数来预防潜在的敏感信息显示给用户。

理想的错误报告应该被完全禁用在php.ini文件里。

如果用的是共享虚拟主机，php.ini不能修改，最好添加 error_reporting() 函数。

放在每个脚本文件的第一行，或者用require_once()来加载，能有效的保护敏感的SQL查询和路径，在出错时不被显示。

2.7 不实用段标签<?

不要使用开始标志的缩写形式，你正在使用这样的符号吗<?，应该用完整的<?php开始标签。

当然，如果是输出变量，用<?= $value ?>这种方式是鼓励的，可以是代码更加简洁。

2.8 纯PHP代码不加结束标记

如果文件内容是纯 PHP 代码，最好在文件末尾删除 PHP 结束标记?>。

这可以避免在 PHP 结束标记之后万一意外加入了空格或者换行符，会导致 PHP 开始输出这些空白，而脚本中此时并无输出的意图。

2.9 永远不要使用register_globals和magic quotes

这是两个很古老的功能，在当时（十年前）也许是一个好方法，但现在看来并非如此。

老版本的PHP在安装时会默认打开这两个功能，这会引起安全漏洞、编程错误及其他的问题。

如只有用户输入了数据时才会创建变量等。

PHP5.4.0开始这两个功能都被舍弃了，所以每个程序员都应该避免使用。

如果你过去的程序有使用这两项功能，那就尽快将其剔除吧。

3 函数
3.1 尽量使用PHP内部函数

内置函数使用C语言实现，并且经过PHP官方优化，效率更高。

3.2 使用绝对路径

在include和require中尽量使用绝对路径。

如果包含相对路径，PHP会在include_path里面遍历查找文件。

用绝对路径就会避免此类问题，解析路径所需的时间会更少。

3.3 包含文件

尽量不要用require_once和include_once包含文件，它们多一个判断文件是否被引用的过程，能不用尽量不用。

而使用require、include方法代替。

鸟哥在其博客中就多次声明，尽量不要用require_once和include_once。

3.4 函数快于类方法

调用只有一个参数、并且函数体为空的函数，花费的时间等于7-8次$localvar++运算。

而同一功能的类方法大约为15次$localvar++运算。

3.5 用子类方法

基类里面只放能重用的方法，其他功能尽量放在子类中实现，子类里方法的性能优于在基类中。

3.6 类的性能和其方法数量没有关系

新添加10个或多个方法到测试的类后，性能没什么差异。

3.7 读取文件内容

在可以用file_get_contents()替代file()、fopen()、feof()、fgets()等系列方法的情况下，尽量用file_get_contents()。

因为他的效率高得多！

3.8  引用传递参数

通过参数地址引用的方式，实现函数多个返回值，这比按值传递效率高。

方法是在参数变量前加个 &。

3.9 方法不要细分得过多

仔细想想你真正打算重用的是哪些代码？

3.10 尽量静态化

如果一个方法能被静态，那就声明它为静态的，速度可提高1/4，甚至我测试的时候，这个提高了近三倍。

当然了，这个测试方法需要在十万级以上次执行，效果才明显。

其实，静态方法和非静态方法的效率主要区别在内存。

静态方法在程序开始时生成内存，实例方法（非静态方法）在程序运行中生成内存。

所以，静态方法可以直接调用，实例方法要先成生实例再调用，静态速度很快，但是多了会占内存。

任何语言都是对内存和磁盘的操作，至于是否面向对象，只是软件层的问题，底层都是一样的，只是实现方法不同。

静态内存是连续的，因为是在程序开始时就生成了，而实例方法申请的是离散的空间，所以当然没有静态方法快。

静态方法始终调用同一块内存，其缺点就是不能自动进行销毁，而实例化可以销毁。

3.11 用C扩展方式实现

如果在代码中存在大量耗时的函数，可以考虑用C扩展的方式实现它们。

4 变量
4.1 及时销毁变量

数组、对象和GLOBAL变量在 PHP 中特别占内存的，这个由于 PHP 的底层的zend引擎引起的。

一般来说，PHP数组的内存利用率只有 1/10。

也就是说，一个在C语言里面100M 内存的数组，在PHP里面就要1G。

特别是，在PHP作为后台服务器的系统中，经常会出现内存耗费太大的问题。

4.2 使用$_SERVER变量

如果你需要得到脚本执行的时间，$_SERVER['REQUSET_TIME']优于time()。

一个是现成就可以直接用，一个还需要函数得出的结果。

4.3 方法里建立局部变量

在类的方法里建立局部变量速度最快，几乎和在方法里调用局部变量一样快。

4.4 局部变量比全局变量快

由于局部变量是存在栈中的。

当一个函数占用的栈空间不是很大的时候，这部分内存很有可能全部命中cache，CPU访问的效率是很高的。

相反，如果一个函数同时使用全局变量和局部变量，当这两段地址相差较大时，cpu cache需要来回切换，效率会下降。

4.5 局部变量而不是对象属性

建立一个对象属性（类里面的变量，例如：$this->prop++）比局部变量要慢3倍。

4.6 提前声明局部变量

建立一个未声明的局部变量，要比建立一个已经定义过的局部变量慢9-10倍。

4.7 谨慎声明全局变量

声明一个未被任何一个函数使用过的全局变量，也会使性能降低。

这和声明相同数量的局部变量一样，PHP可能去检查这个全局变量是否存在。

4.8 使用++$i递增

当执行变量$i的递增或递减时，$i++会比++$i慢一些。

这种差异是PHP特有的，并不适用于其他语言，所以请不要修改你的C或Java代码，并指望它们能立即变快，没用的。

++$i更快是因为它只需要3条指令(opcodes)，$i++则需要4条指令。

后置递增实际上会产生一个临时变量，这个临时变量随后被递增。

而前置递增直接在原值上递增。

这是最优化处理的一种，正如Zend的PHP优化器所作的那样。

牢记，这个优化处理不失为一个好主意，因为不是所有的指令优化器都会做同样的优化处理。

4.9 不要随便复制变量

有时候为了使 PHP 代码更加整洁，一些 PHP 新手（包括我）会把预定义好的变量，复制到一个名字更简短的变量中。

其实这样做的结果是增加了一倍的内存消耗，只会使程序更加慢。

试想一下，在下面的例子中，如果用户恶意插入 512KB 字节的文字，就会导致 1MB 的内存被消耗！

// 不好的实践
$description = $_POST['description'];
echo $description;

// 好的实践
 echo $_POST['description'];
4.10 循环内部不要声明变量

尤其是大变量，这好像不只是PHP里面要注意的问题吧？

4.11 一定要对变量进行初始化

这里的“初始化”指的是“声明”。

当需要没有初始化的变量，PHP解释器会自动创建一个变量，但依靠这个特性来编程并不是一个好主意。

这会造成程序的粗糙，或者使代码变得另人迷惑。

因为你需要探寻这个变量是从哪里开始被创建的。

另外，对一个没有初始化的变量进行递增操作要比初始化过的来得慢。

所以对变量进行初始化会是个不错的主意。

5 数组
5.1 用字符串而不是数组作为参数

如果一个函数既能接受数组，又能接受简单字符做为参数，那么尽量用字符作为参数。

例如，字符替换函数，参数列表并不是太长，就可以考虑额外写一段替换代码。

使得每次传递参数都是一个字符，而不是接受数组做为查找和替换参数。

5.2 数组元素加引号

$row['id']比$row[id]速度快7倍。

如果不带引号，例如$a[name]，那么PHP会首先检查有没有define定义的name常量。

如果有，就用这个常量值作为数组键值。如果没有，再查找键值为字符串'name'的数组元素。

多了一个查找判断的过程，所以建议养成数组键名加引号的习惯。

正如上面字符串部分所述，用'又比用"速度更快。

5.3 多维数组操作

多维数组尽量不要循环嵌套赋值。

5.4 循环用foreach

尽量用foreach代替while和for循环，效率更高。

6 架构
6.1 压缩输出

在php.ini中开启gzip压缩：

zlib.output_compression = On
zlib.output_compression_level = (level)
level可能是1-9之间的数字，你可以设置不同的数字。

几乎所有的浏览器都支持Gzip的压缩方式，gzip可以降低80%的输出.

付出的代价是，大概增加了10%的cpu计算量。

但是还是会赚到了，因为带宽减少了，页面加载会变得很快。

如果你使用apache，也可以激活mod_gzip模块。

6.2 静态化页面

Apache/Nginx解析一个PHP脚本的时间，要比解析一个静态HTML页面慢2至10倍。

所以尽量使页面静态化，或使用静态HTML页面。

6.3 将PHP升级到最新版

提高性能的最简单的方式是不断升级、更新PHP版本。

6.4 利用PHP的扩展

一直以来，大家都在抱怨PHP内容太过繁杂。

最近几年来，开发人员作出了相应的努力，移除了项目中的一些冗余特征。

即便如此，可用库以及其它扩展的数量还是很可观。

甚至一些开发人员开始考虑实施自己的扩展方案。

6.5 PHP缓存

一般情况下，PHP脚本被PHP引擎编译后执行，会被转换成机器语言，也称为操作码。

如果PHP脚本反复编译得到相同的结果，为什么不完全跳过编译过程呢？

PHP加速器缓存了编译后的机器码，允许代码根据要求立即执行，而不经过繁琐的编译过程。

对PHP开发人员而言，目前提供了两种可用的缓存方案。

一种是APC（Alternative PHP Cache，可选PHP缓存），它是一个可以通过PEAR安装的开源加速器。

另一种流行的方案是OPCode，也就是操作码缓存技术。

6.6 使用NoSQL缓存

Memchached或者Redis都可以。

这些是高性能的分布式内存对象缓存系统，能提高动态网络应用程序性能，减轻数据库的负担。

这对运算码 （OPcode）的缓存也很有用，使得脚本不必为每个请求重新编译。
