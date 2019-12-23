<style>
.page-inner {max-width: 900px;}
.markdown-section table {display: inline-block;}
.link-button {
  background: none!important;
  border: none;
  padding: 0!important;
  color: #069;
  text-decoration: underline;
  cursor: pointer;
}
</style>

<!-- toc -->

# 接口文档: `<?= $version ?>`

> 本手册由 DOF PHP Doc Generator 于 <?= Format::microtime('T Y/m/d H:i:s') ?> 自动生成。

# 文档公约

下列公约在本手册所有接口文档中通用。

## 路由参数

所有接口文档的「接口定义」中，包含 `{}` 的均表示路由参数，是请求参数的一部分。

示例：获取用户详情的 REST 接口为 `GET /api/users/{id}`，其中 `{id}` 就是路由参数，其实际要表示的是一个用户 ID，因此如果我们想要获取 ID 为 1 的用户详情，则我们最终请求的 URL 路径是 `GET /api/users/1`。

## 「认证授权」的四种类型

- `0`: 无需认证无需授权。
- `1`: 只需认证无需授权。
- `2`: 只需授权无需认证。
- `3`: 同时需要认证和授权。

## 接口状态

接口文档中的「基础信息」表格中，列「状态」表示该接口当前所处于的状态。

这个状态可选值的范围没有强制要求，但在没有特殊说明的情况下，默认的状态列表如下：

- `0`: 未实现。
- `1`: 已上线。
- `2`: 已实现，未部署。
- `3`: 已部署，可测试。
- `-1`: 已作废。

## 关于接口数据返回

除特殊情况，本手册所有接口文档均不包含具体的接口返回值说明，因为所有 HTTP 接口实行了 GraphQL-like 调用风格——即接口要返回什么，是由客户端指定的。只不过客户端能指定返回哪些字段结构，是由具体的接口文档中的「关联数据模型」决定的。

说明：接口可关联的数据模型列表由专门的数据模型文档维护，详见：<button class="link-button" onclick="selectDOFDoc('_model')">接口数据模型</button>。

## 关于接口响应数据格式

如果接口响应的数据没有指定格式，则对应的文档中不会出现「响应结构示例」，表示该请求返回的只有数据本身或者没有数据返回（比如响应的状态码为 204 时）。

如果接口响应的数据指定了格式，则对应的文档中会出现「响应结构示例」，该示例结构中会出现如下占位符。

### `__DATA__` - 单项数据占位符

如果包含 `__DATA__` 占位符，则表示接口返回的业务数据将被放到这个字段。

### `__PAGINATOR__` - 列表/分页元数据占位符

如果包含了 `__PAGINATOR__` 占位符，则表示该接口是一个列表/分页接口，其中分页相关的信息将被放到这个字段，该字段结构固定如下：

``` php
total =>
count =>
page => 1
size => 10
```

注意此时该 WrapOut 同级的 `__DATA__` 占位符（如果有）返回的则是一个同构子项的列表。

可以总结为：

- 当一个 WrapOut 只有 `__DATA__` 占位符时，`__DATA__` 里面是一个单项数据 A。
- 当一个 WrapOut 既有 `__PAGINATOR__` 又有 `__DATA__` 时，`__DATA__` 里面相当于包含了若干个结构相同的单项数据 A。

### 接口返回字段的查询参数

如果接口文档没有特殊说明，均为 `__fields`。`__fields` 指明的字段列表将从接口关联的数据模型中取。

举例，假如我们只需要获取 ID 为 1 的用户姓名、手机号和注册时间这三个个字段，则请求返回字段的查询参数格式如下：

``` http
GET /users/1?__fields=name,mobile,createdAt{format:1}
```

其中，`createdAt{format:1}` 表示字段 `createdAt` 接受附加的字段参数 `format`，并会根据该参数的值改变该字段的最终返回值。

字段可以接受的参数详情参考具体的数据模型文档中的「可接受参数」说明。

### 列表查询接口的分页参数

- `__paginate` （默认；优先级最高）

``` http
GET /v1/users?__paginate=size{10},page{1}
```

- `__paginate_size` 和 `__paginate_page`

``` http
GET /v1/users?__paginate_size=20&__paginate_page=2
```

### 列表查询接口的排序参数

- `__sort` （默认；优先级最高）

``` http
GET /v1/users?__sort=field{id},order{desc}
```

- `__sort_field` 和 `__sort_order`

``` http
GET /v1/users?__sort_field=id&__sort_order=desc
```

### 时间戳格式化参数

前面的例子中有个 `createdAt{format:1}` 这样的字段参数表达式，其中这个 `format` 属于字段参数，用途是指定要返回的时间戳字段的格式。

框架内部定义了几种常用格式，字段参数值及其格式对应如下：

- `1`：`Y-m-d H:i:s`
- `2`：`y/m/d H:i:s`
- `3`：`d/m/y H:i:s`
- `0`：不传时默认，即返回时间戳本身。 

## 接口参数基本类型

在查看接口文档的时候，所有关于请求参数类型标识（包括请求参数和响应参数）的含义，均在下面的列表中说明：

| KEY | 含义 | 示例 |
| :--- | :--- | :--- |
| `Boolean` | 布尔值 | `true`, `false` |
| `Int` | 整数 | `0`, `-1`, `1` |
| `Bint` | 二进制整数 | 只能是 `0` 和 `1` |
| `Pint` | 正整数 | `1` |
| `Uint` | 非负整数 | `0` |
| `Nint` | 负整数 | `-1` |
| `String` | 字符串 | `foo` |
| `IdList` | ID 列表字符串，ID 之间使用英文逗号隔开且所有 ID 不重复 | `1,2,3,4` |
| `IdArray` | ID 列表数组，数组内所有 ID 不重复 | `[1, 2, 3, 4]` |
| `Array` | 广义数组 | `[1, 2, 'a' => 3, 'b' => 4]` |
| `ValueArray`/`ScalarArray` | 值数组/标量数组 | `[1, 2, 3, 4]` |
| `IndexArray` | 下标数组 | `[1, 2, 'a', 'b']` |
| `ListArray` | 列表数组（子项同构） | `[['a' => 1, 'b' => 2], ['a' => 3, 'b' => 4]]` |
| `ListArrayOfReference` | 列表数组（子项同构且每项关联到某个具体的数据模型） | `[['attr1' => 1, 'attr2' => 2], ['attr1' => 3, 'attr2' => 4]]` |
| `AssocArray`/`ObjectArray` | 关联数组/对象数组 | `['a' => 1, 'b' => 2]` |

### 说明

- 接口参数基本类型的 KEY 不区分大小写。
- `Array`/`IndexArray`/`IdArray`/`ValueArray`/`ScalarArray`/`ListArray`/`ListArrayOfReference`/`ObjectArray`/`AssocArray` 本质都是数组，只是在某些场景下可能需要对数组本身进行区分，因此进一步分为这几类。

## 接口参数校验规则

接口参数校验规则指的是接口参数满足前面的基本类型之后，还需要进一步对其格式和值进行校验的规则。

目前可以使用的校验规则有：

| KEY | 含义 | 可接受参数名 | 示例 |
| :--- | :--- | :--- | :--- |
| `EMAIL` | 电子邮箱 | - | `me@dof.php` |
| `MOBILE` | 手机号码 | `cn` => 中国大陆手机号格式 | `13344445555` |
| `IN` | 指定的参数必须在给定的枚举列表中 | - | 规则：`status` => `in:1,2,3`<br>含义：表示名为 `status` 的参数必须是 `1`/`2`/`3` 的某一个|
| `CIIN` | 指定的单个参数必须在给定的枚举列表中，不区分大小写 | - | 规则：`type` => `ciin:a,b,C`<br>含义：表示名为 `type` 的参数必须是 `a`,`A`, `b`,`B`, `c`,`C` 的某一个|
| `CIINS` | 指定的多个参数必须在给定的枚举列表中，不区分大小写 | - | 规则：`type` => `ciins:a,b,C`<br>含义：表示名为 `type` 的参数必须是集合 [`a`,`A`,`b`,`B`, `c`,`C`] 的某一个子集 |
| `INROUTE` | 指定的参数必须在接口路由定义的路由参数中 | - | - |
| `DateFormat` | 该参数的值是否为指定的日志格式 | 默认为检查该参数是否为字符串 | `Y-m-d H:i:s` => `1970-01-01 00:00:01` |
| `Timestamp` | 该参数的值是否为 UNIX 时间戳 | 默认为检查该参数是否为正整数 | `1546300800` |
| `Microtime` | 该参数的值是否为带微妙的 UNIX 时间戳 | 默认为检查该参数是否为正整数 | `1568798737782` |
| `HOST` | 主机域名或者IP | - | `demo.dofphp.org`, `12.34.56.78` |
| `URL` | URL 地址 | - | `http://demo.dofphp.org`<br>`http://12.34.56.78:10101/path/to/sth` |
| `IP` | IP 地址（v4或v6） | - | `1.1.1.1`, `fe80::ea9f:cb7e:822d:711f` |
| `IPV4` | IPv4 地址 | - | `1.1.1.1` |
| `IPV6` | IPv6 地址 | - | `fe80::ea9f:cb7e:822d:711f` |
| `MIN` | 数值最小/字符串最短 | 隐式会判断基本数据类型 | `4` |
| `MAX` | 数值最大/字符串最长 | 隐式会判断基本数据类型 | `8` |
| `LENGTH` | 字符串的长度为多少 | - | `4` |
| `NEED` | 指定的参数必须 | - | - |
| `NEEDIFNO` | 如果原数据中不存在某个参数则需要该参数 | 假如不存在的参数名 | 原数据：`['a' => 1]`<br>规则：`c` => `NeedIfNo(b)`<br>结果：则此时会检查原数据中 `c` 是否存在 |
| `NEEDIFHAS` | 如果原数据中存在某个参数则需要该参数 | 假如存在的参数名 | 原数据：`['a' => 1]`<br>规则：`c => NeedIfHas(a)`<br>结果：则此时会检查 `c` 是否存在 |
| `WRAPIN` | 该参数值引用了另外一个已定义的数据格式集合 | 隐式会判断基本数据类型是否是数组 | `User.Http.Wrapper.In.ABC` |

### 说明

- 接口参数校验规则的 KEY 也不区分大小写。
- `WrapIn` 实现了无限嵌套格式校验，关于本手册中所有文档可以使用的 `WrapIn` 列表详见：<button class="link-button" onclick="selectDOFDoc('_wrapin')">WrapIn 校验集合</button>。

<script>
function selectDOFDoc(path) {
    let url = new URL(window.location.href)
    window.location.href = url.origin + '/' + path
}
</script>
