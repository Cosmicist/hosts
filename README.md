Hosts Manager
=============

Hosts Manager is a utility to quickly and easily manage hosts in your hosts file.

Requirements
------------

* [Composer](http://getcomposer.org/)
* php 5.3+

Installation
------------

As of now, the only way to install it is from source, but fear not, it's really
simple.

1. Run `git clone git@github.com:xFlatlinex/hosts.git`
2. Run `composer install`

At this point you should be able to run it like this `php bin/hosts`.  

### Global installation

To install it globally, you need to compile it to a single phar file and copy it
to `/usr/local/bin`.

1. Run `php bin/compile` to generage `hosts.phar`
2. Run `sudo cp hosts.phar /usr/local/bin/hosts`

Now you should be able to run it directly:

```
$ hosts add somehost
```

Usage
-----

To get a list of options and commands run `hosts` without any parameters.

Currently available commands:

* Run `hosts add [hostname] [options]` to add a host
* Run `hosts toggle [hostname] [options]` to enable/disable a host
* Run `hosts show [options]` to show a list of hosts

All of the commands have several options and filters, you can check them out by
running `hosts help [command]`.

Contributing
------------

This is a work in progress, I'll be adding more commands and modifying existing
ones.  
This project was built to suit my needs, but if it helps you as well, fantastic!
Feel free to contribute with new functionality and/or fixes!

License
-------

Copyright (c) 2013 Luciano Longo

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is furnished
to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.