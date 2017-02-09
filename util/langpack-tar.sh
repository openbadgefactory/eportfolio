#!/bin/sh
#
# langpack-tar.sh
#
# - Syncs selected language with en.utf8. If a file is missing, it will be created (empty).
#   Files are searched in current directory
#
# - Creates a tar file containing Mahara translation files
#   in selected language.
#
# SYNOPSIS
# 
#   cd /var/www/mahara
#   sh ~/devel/php/mahara.git/util/langpack-tar.sh fi.utf8 > /tmp/fi.utf8.tar
# 

lang=$1

if [ "$lang" != "fi.utf8" -a "$lang" != "sv.utf8" ]; then
    echo "unsupported language: $lang" >&2;
    exit 1;
fi

# Sync with en.utf8
find . -type d -wholename \*en.utf8\*     -print0 |sed s/en\.utf8/$lang/g |xargs -0 mkdir -p
find . -type f -wholename \*en.utf8\*.php -print0 |sed s/en\.utf8/$lang/g |xargs -0 touch

# print tar to stdout
find . -type f -wholename \*$lang\* -print0 |xargs -0 tar --create -O

