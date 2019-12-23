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

## `<?= $key ?>` - <?= $wrapin ?>

| 字段 | 名称 | 基本类型 | 校验规则 | 默认值 | 兼容字段 | 备注 |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
<?php if ($params ?? false) : ?>
<?php foreach ($params as $arg) : ?>
<?php
    \extract($arg, EXTR_OVERWRITE);
    $compatibles = \array_keys($compatibles);
    $compatibles = \array_map(function ($val) {return "`{$val}`";}, $compatibles);
    $compatibles = \join('<br>', $compatibles);
    $default = $default ? "`{$default}`" : '-';
    $rules = [];
    foreach ($validators as $rule => $err) {
        if (false === \mb_strpos($rule, ':')) {
            if (($rule == 'WRAPIN') && $err) {
                $_rule = $rule; 
                $_rule .= <<<LINK
:<button class="link-button" onclick='selectDOFDoc("_wrapin/{$err}.html")'>{$err}</button>
LINK;
            }
        }
        $_rule = \join(':', Str::arr("{$rule}:{$err}", ':'));
        $rules[] = $_rule;
    }
    $rules = $rules ? \join('<br>', $rules) : '-';
?>
| `<?= $name ?>` | <?= $title ?> | <?= \ucfirst($type) ?> | <?= $rules ?> | <?= $default ?> | <?= $compatibles ?> | <?= $notes ?: '-' ?> |
<?php endforeach ?>
<?php endif ?>

<script>
function selectDOFDoc(path) {
    let url = new URL(window.location.href)
    window.location.href = url.origin + '/' + path
}
</script>
