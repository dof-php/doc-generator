# 全局错误码

<?php if ($errors) :?>
> 本文档里面的错误描述均为默认描述，大部分情况下可以参考，但是当具体调用报错时，同一个错误码可能会根据实际情况改变更符合当时情境的错误描述。


| 错误码 | KEY | 描述（默认） | 所属领域 | 建议 |
| :--- | :--- | :--- | :--- | :--- |
<?php foreach ($errors as $code => $item) : ?>
<?php
$desc = \I18N::active('zh-CN') ? \I18N::get($item[0], $item[1], 'zh-CN') : ($item[2] ?? '-');
$domain = \DMN::name($item[0]);
$_domain = \DMN::meta($item[0], 'title', $domain);
?>
| `<?= $code ?>` | `<?= $item[1] ?>` | <?= $desc ?> | <?= $_domain ?> (`<?= $domain ?>`) | <?= $item[3] ?? '-' ?> |
<?php endforeach ?>
<?php endif ?>
