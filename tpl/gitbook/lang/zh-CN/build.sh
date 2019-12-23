#!/usr/bin/sh

set -e

DOC_DIR="<?= $docDir ?>"
SITE_DIR="<?= $siteDir ?>"
GIT_DIR="$SITE_DIR/.git"
GIT_DIR_TMP="$DOC_DIR/__tmp_git_dist"

<?php if ($versions ?? false) : ?>
cd $DOC_DIR
<?php foreach($versions as $version) : ?>
cd <?= $version ?> && gitbook install
cd ..
<?php endforeach ?>

if [ -d "$GIT_DIR" ]; then
    cd $SITE_DIR
    git add -A
    git reset --hard HEAD 2>&1 > /dev/null
    git pull origin master 2>&1 > /dev/null
    cd ..
    mv $GIT_DIR $GIT_DIR_TMP 2>&1 > /dev/null
fi

rm -rf $SITE_DIR 2>&1 > /dev/null

<?php foreach($versions as $version) : ?>

gitbook build $DOC_DIR/<?= $version ?> $SITE_DIR/<?= $version ?>

if [ -d "$DOC_DIR/__assets" ]; then
    cp -r "$DOC_DIR/__assets" $SITE_DIR/<?= $version ?> 2>&1 > /dev/null
fi

<?php endforeach ?>

mv $DOC_DIR/index.html $SITE_DIR 
if [ -d $GIT_DIR_TMP ]; then
    mv $GIT_DIR_TMP $GIT_DIR 2>&1 > /dev/null
fi

<?php if ($deploy ?? true) : ?>
if [ -d "$GIT_DIR" ]; then
    cd $SIET_DIR 
    git add -A
    git commit -a -m 'auto updated'
    git push -f origin master
fi
<?php endif ?>
<?php endif ?>