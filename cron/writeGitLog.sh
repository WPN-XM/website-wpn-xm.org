#!/bin/sh
cd ../registry
git log -n 60 --date=short --pretty=format:"%s#~|~#%ad" > ../gitlog.txt