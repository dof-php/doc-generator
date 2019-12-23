<!-- toc -->

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

## <?= $api->title ?>

<?php if ($api->subtitle) : ?>
> <?= $api->subtitle ?>
<?php endif ?>



### 接口定义

<?php $api->route ?>
``` http
<?= \join('|', $api->verbs) ?> <?= $api->route ?>

```

> `<?= "{$api->class}@{$api->method}" ?>`

### 基础信息

| 认证授权类型 | 关联数据模型 | 版本 | 资源后缀支持 | 状态 | 负责人 | 最后更新时间 |
| :--- | :--- | :--- | :--- |
<?php
    $model = '-';
    if ($api->model->key ?? null) {
        $type = \DOF\DDD\Util\IS::entity($api->model->namespace) ? 'entity' : 'model';
        $model = <<<LINK
<button class="link-button" onclick='selectDOFDoc("_{$type}/{$api->model->key}.html")'>{$api->model->key}</button>
LINK;
    }
?>
| <?= $api->auth ?> | <?= $model ?> | <?= $api->version ?> | <?= Str::wraps($api->suffixes, '`', '<br>') ?> | <?= $api->status ?> | <?= $api->author ?> | <?= $api->updatedAt ?> |

<?php if ($api->request->headers) : ?>
### 请求头

<?php foreach($api->request->headers as $key => $val) : ?>
- `<?= $key ?>`: `<?= $val ?>`
<?php endforeach ?>
<?php endif ?>

<?php if ($api->arguments) : ?>
### 请求参数

| 名称 | 描述 | 基本类型 | 参数位置 | 校验规则 | 默认值 | 兼容字段 | 备注 |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
<?php foreach ($api->arguments as $argument) : ?>
<?php
    $rules = '';
    foreach ($argument->rules as $key => $val) {
        $rules .= join(' : ', [\Str::wrap($key, '`'), \Str::wrap($val, '`')]);
        $rules .= '<br>';
    }
?>
| `<?= $argument->name ?>` | <?= $argument->title ?> | <?= $argument->type ?> | <?= $argument->location ?> | <?= $rules ?> | <?= Str::wrap($argument->default, '`') ?> | <?= Str::wraps($argument->compatibles, '`', '<br>', '-') ?> | <?= $argument->notes ?: '-' ?> |
<?php endforeach ?>
<?php endif ?>

<?php if ($api->sorting) : ?>
### 可排序参数
| 字段 | 说明 | 兼容字段 |
| :--- |
<?php foreach($api->sorting as list($field, $title, $compatibles)) : ?>
| `<?= $field ?>` | <?= $title ?> | <?= Str::wraps($compatibles, '`', '<br>')?> |
<?php endforeach ?>
<?php endif ?>

<?php if ($api->response->headers) : ?>
### 响应头

<?php foreach($api->response->headers as $key => $val) : ?>
- `<?= $key ?>`: `<?= $val ?>`
<?php endforeach ?>
<?php endif ?>

<?php if ($api->response->statuses) : ?>
### 响应状态码
<?php foreach($api->response->statuses as $code => $info) : ?>
<?php if ($info) : ?>
- `<?= $code ?>` : <?= $info ?: '-' ?>
<?php endif ?>
<?php endforeach ?>
<?php endif ?>


<?php if ($api->wrapout || $api->wraperr) : ?>
### 响应结构示例

<?php if ($api->wrapout) : ?>
#### 成功情况

``` php
<?= $api->wrapout ?>

```
<?php endif ?>

<?php if ($api->wraperr) : ?>
#### 失败情况

``` php
<?= $api->wraperr ?>

```
<?php endif ?>
<?php endif ?>

<?php if ($api->model) : ?>
### 关联数据模型

#### `<?= $api->model->key ?>` | <?= $api->model->title ?>

<?php if ($api->model->properties) : ?>
| 属性 | 名称 | 基本类型 | 兼容字段 | 备注 |
| :--- | :--- | :--- | :--- |
<?php foreach ($api->model->properties as $property) : ?>
<?php
$type = $property->type;
if ($property->typemodel) {
        $_type = \Str::start('entity', \strtolower($type)) ? '_entity' : '_model';
        $type .= <<<LINK
:<br/> <button class="link-button" onclick='selectDOFDoc("{$_type}/{$property->typemodel}.html")'>{$property->typemodel}</button>
LINK;
}
?>
| `<?= $property->name ?>` | <?= $property->title ?> | <?= $type ?> | <?= \Str::wraps($property->compatibles, '`', '<br>', '-') ?> | <?= $property->notes ?: '-' ?> |
<?php endforeach ?>
<?php endif ?>

<?php if ($api->model->arguments) : ?>
#### 可接受参数

<?php foreach ($api->model->arguments as $argument => $option) : ?>

- `<?= $argument ?>`


``` json
<?= JSON::pretty($option) ?>

```
<?php endforeach ?>
<?php endif ?>
<?php endif ?>

<?php if ($api->remarks) : ?>
### 备注
<?php foreach($api->remarks as $remark) : ?>

> <?= $remark ?>

<?php endforeach ?>
<?php endif ?>

<script>
function selectDOFDoc(path) {
    let url = new URL(window.location.href)
    window.location.href = url.origin + '/' + path
}
</script>
