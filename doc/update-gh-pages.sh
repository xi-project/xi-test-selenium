#!/bin/sh -e
cd `dirname "$0"`
cd ..

rm -Rf doc/api

echo "Running doxygen"
doxygen

echo "Re-cloning doc/gh-pages"
rm -Rf doc/gh-pages
git clone git@github.com:xi-project/xi-test-selenium.git -b gh-pages doc/gh-pages

echo "Copying API docs over to gh-pages"
cp -r doc/api/html/* doc/gh-pages
cd doc/gh-pages
git add -A
git status
git commit -m "Update API reference."

echo
echo
echo "Now please check that the latest commit in doc/gh-pages looks ok"
echo "and then git push it."
echo "You may remove doc/gh-pages afterwards"
echo

