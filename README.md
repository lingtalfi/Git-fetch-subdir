Git fetch subdir
===================
2016-12-29


a small php script to fetch a subdirectory of a github repository.




How to use
=============

At the top of the php script, you will find a configuration section, with the following variables:

 
- author: the author of the repository
- repository: the author of the repository
- relPath: the relative path (without a leading slash), from the root of the repository, to the sub-directory you want to fetch
- dstDir: the path to the directory on your machine that will contain the fetched sub-directory 
    - in the example below, the __DIR__ . "/code" subdirectory will be created


```php
$author = 'lingtalfi';
$repository = "bashmanager";
$relPath = 'code';
$dsDir = __DIR__;
```


Once it's configured, you can use it.
Just call the script, either from your browser, or from the php cli.










