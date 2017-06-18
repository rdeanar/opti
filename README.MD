# Opti - decrease your image size
PHP component for image optimization which uses various chains of tools and detects the best one for each image

[![Build Status](https://travis-ci.org/rdeanar/opti.svg?branch=master)](https://travis-ci.org/rdeanar/opti)


## Usage

`./bin/opti optimize data/1.jpg`

Or with glob
`./bin/opti optimize data/*.jpg`

Or using stdin\stdout
`cat original.svg | ./bin/opti optimize data/*.jpg > optimized.svg`

Or maybe just files modified within the hour
`find ./data/t/ -newermt $(date +%Y-%m-%d -d '1 hour ago') -type f | xargs php ./bin/opti optimize`
`find ./data/t/ -newerct '1 hour ago' -type f |  -type f | xargs php ./bin/opti optimize`

## Supported tools

### convert
https://www.imagemagick.org/script/convert.php

### Jpegoptim
https://github.com/glennr/jpegoptim

### guetzli (Google)
https://github.com/google/guetzli

Install on Mac OS
`brew install guetzli`

### pngquant
https://pngquant.org/

Install on Mac OS
`brew install pngquant`

### optipng 
http://optipng.sourceforge.net/

Install on Mac OS
`brew install optipng`

### svgo 
https://github.com/svg/svgo

Install on Mac OS
`npm install -g svgo`


### svgcleaner
https://github.com/RazrFalcon/svgcleaner

Install
Clone repo and build using cargo as described in repo readme

Install on Mac OS
`brew install svgcleaner`

## TODO
* More tests
* More tools
* ~~Move internal configurations to file~~ DONE
* ~~Command option for setting log level~~ DONE
* ~~Ability to read external config file~~ DONE
* ~~Allow to register new tools on the fly~~ DONE
* ~~Read from stdin, output to stdout~~ DONE
* Config lint command https://symfony.com/doc/current/components/yaml.html#syntax-validation
* Dummy config create command
* Tool options replacements, i.e. {quality}
* Build Phar file
* Output dir option
* Tinypng web service integration
* Dry-run mode
* Installed tool detection command
* Batch optimize statistics
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
* http://optipng.sourceforge.net/pngtech/optipng.html
