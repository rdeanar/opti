#!/bin/bash

#find ./data/ -newermt $(date +%Y-%m-%d -d '1 hour ago') -type f | xargs php ./1.php
#find ./data/ -type f | xargs php ./1.php

cp data/fast/1.jpg data/fast/2.jpg
cp data/fast/3.png data/fast/4.png
cp data/fast/5.svg data/fast/6.svg

du -hs data/fast/2.jpg
du -hs data/fast/4.png
du -hs data/fast/6.svg

time ./bin/opti optimize -vvv ./data/fast/2.jpg ./data/fast/4.png ./data/fast/6.svg

du -hs data/fast/2.jpg
du -hs data/fast/4.png
du -hs data/fast/6.svg
