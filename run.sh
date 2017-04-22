#!/bin/bash

#find ./data/ -newermt $(date +%Y-%m-%d -d '1 hour ago') -type f | xargs php ./1.php
#find ./data/ -type f | xargs php ./1.php

cp data/fast/1.jpg data/fast/2.jpg
du -hs data/fast/2.jpg
#php ./1.php data/fast/2.jpg
./bin/opti optimize ./data/fast/2.jpg
du -hs data/fast/2.jpg