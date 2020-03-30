# TarBlog-Core
## 项目简介
该 Library 是 TarBlog 的核心组件，基于 Laravel 5.8 组件制作。

注意：不是直接魔改&精简 Laravel ，虽然借用了部分 Foundation 的代码，但是其他组件我还是通过 Composer 引用的，
主要是避免一些协议和版权上的问题（虽然 MIT 问题不大，但还是尽量避坑）。

其实当初就想直接拿 Laravel/Lumen 直接精简的，但是后来还是只用了 database 和 pagination ，现在因为把核心另作一个包，
因此想要更加全面的利用（毕竟单单是 database 一个库就引用了很多”无关“组件，感觉不划算）。
当然，考虑到这是一个博客，过于庞大的规模是没有必要的，因此一些组件没有引用。

## 自主模块
### 路由(Route)
与 Laravel、ThinkPHP、Slim Framework 等框架的路由不同，TarBlog-Core 的路由支持重用，即同一个形式的 Uri 可以根据 ID/Slug
等参数定位到不同类型的页面，从而实现文章、独立页面和 category （翻译为分类）复用同一表达式的地址。

例如：文章地址为`/{slug}.html`，页面地址也为`/{slug}.html`，但是文章和页面的 **slug** 是不一样的，可以根据 slug 定位到正确的页面，
而不会直接返回 404 Not Found。

### 视图(View)
与多数框架的视图组件不同，TarBlog-Core 的视图组件完全使用原生PHP作为视图，无需额外算力解析模板。
通过缓冲输出函数，也可以达到像 smarty 一样输出内容到变量的功能。

视图引用了宏指令（Macroable），可以动态添加方法，供模板调用。用法：视图（View）对象有公共方法 addMacro ，
传入一个参数调用 Engine::mixin ，传入两个参数调用 Engine::macro。理论上也可以直接调用这两个静态方法，但是以后有更换的可能，
为了标准，建议不要直接调用。

### 应用(Application)
实现了 Illuminate\Contracts\Foundation\Application，但是由于部分方法不会使用到，所以可能会先做一个alias或置空，以后用到再说。

## 引用类库
* illuminate/auth 鉴权
* illuminate/cookie Cookie
* illuminate/console 终端/命令行操作
* illuminate/database 数据库
* illuminate/encryption 加密
* illuminate/events 事件
* illuminate/hashing 哈希
* illuminate/http HTTP
* illuminate/pagination 分页
* illuminate/pipeline 管道
* illuminate/session Session
* illuminate/validation 表单验证

## 开源协议

采用 [Apache License 2.0](LICENSE)