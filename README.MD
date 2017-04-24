# Opti - decrease your image size
PHP component for image optimization which uses various chains of tools and detects the best one for each image

[![Build Status](https://travis-ci.org/rdeanar/opti.svg?branch=master)](https://travis-ci.org/rdeanar/opti)


## Usage

`./bin/opti optimize data/1.jpg`

Or with glob
`./bin/opti optimize data/*.jpg`

Or maybe just files modified within the hour (command valid for linux)
`find ./data/t/ -newermt $(date +%Y-%m-%d -d '1 hour ago') -type f | xargs php ./bin/opti optimize`

## Supported tools

### convert
https://www.imagemagick.org/script/convert.php

### Jpegoptim
https://github.com/glennr/jpegoptim


## TODO
* Tests
* Add more tools
* ~~Move internal configurations to file~~ DONE
* Command option for setting log level
* Ability to read external config file
* Allow to register new tools on the fly
* Config lint command https://symfony.com/doc/current/components/yaml.html#syntax-validation
* Dummy config create command
* Tool options replacements, i.e. {quality}
* Build Phar file
* ...


## Other info

### png
* https://pngmini.com/ > https://pngquant.org/


### jpeg
* https://github.com/mozilla/mozjpeg
* https://github.com/technopagan/adept-jpg-compressor
* jpegtran

### Articles
* https://addyosmani.com/blog/image-optimization-tools/
* http://www.tecmint.com/optimize-and-compress-jpeg-or-png-batch-images-linux-commandline/
