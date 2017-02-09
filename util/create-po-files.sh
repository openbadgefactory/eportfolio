#!/bin/bash

MAHARADIR=/home/olli/Projects/ekampus/htdocs
LANGS=( en sv fi )
MASTERLANG=fi
TMPDIR=$(mktemp -d)
SCRIPTDIR=$(cd $(dirname "${BASH_SOURCE[0]}" ) && pwd)
PODIR=$SCRIPTDIR

cd $MAHARADIR

echo "Creating directory structure."

# Create directory structure for the lang files.
for lang in ${LANGS[@]}
do
    mkdir "$TMPDIR/$lang.utf8"
    find . -type d -name "$lang.utf8" -exec cp -r --parents {} "$TMPDIR/$lang.utf8/" \;
done

echo "Creating po-files."

# Create po-files.
for lang in ${LANGS[@]}
do
    if [ $lang != $MASTERLANG ]; then
	php "$SCRIPTDIR/php-po.php" "$TMPDIR/$MASTERLANG.utf8" "$TMPDIR/$lang.utf8" $MASTERLANG "$PODIR/$lang.po"
	echo "Created po-file: $PODIR/$lang.po."
    fi
done

echo "Done!"
