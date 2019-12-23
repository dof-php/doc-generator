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


<?php if ($model) : ?>
## <?= $model->title ?> 

<?php if ($model->subtitle) : ?>
> <?= $model->subtitle ?>
<?php endif ?>



<?php if ($model->properties) : ?>
| 属性 | 名称 | 基本类型 | 兼容字段 | 备注 |
| :--- | :--- | :--- | :--- |
<?php foreach ($model->properties as $property) : ?>
<?php
$type = $property->type;
if ($property->typemodel) {
        $_type = \Str::start('entity', \strtolower($type)) ? '_entity' : '_model';
        $type .= <<<LINK
:<br/> <button class="link-button" onclick='selectDOFDoc("{$_type}/{$property->typemodel}.html")'>{$property->typemodel}</button>
LINK;
}
?>
| `<?= $property->name ?>` | <?= $property->title ?> | <?= $type ?> | <?= Str::wraps($property->compatibles, '`', '<br>', '-') ?> | <?= $property->notes ?: '-' ?> |
<?php endforeach ?>
<?php endif ?>
<?php endif ?>



<?php if ($model->arguments) : ?>
#### 可接受参数

<?php foreach ($model->arguments as $argument => $option) : ?>

- `<?= $argument ?>`


``` json
<?= \JSON::pretty($option) ?>

```
<?php endforeach ?>
<?php endif ?>


> <?= $model->key ?>

<script>
function selectDOFDoc(path) {
    let url = new URL(window.location.href)
    window.location.href = url.origin + '/' + path
}
</script>
