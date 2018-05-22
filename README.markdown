Description
-----------
This is the old version of the website for the [Stacks project](http://stacks.math.columbia.edu). It enabled a comment system, an improved tag lookup, and a full-powered online view of its contents. This project is no longer maintained.

Please visit the [Gerby project](https://gerby-project.github.io/) to read about the current version.


Configuration
-------------

Below you will find rough instructions to create a local copy of the Stacks project website on your system. Requirements:

1. Apache with mod-rewrite and php enabled
2. php-sqlite
3. UNIX command line tools, in particular make, python, and git
4. a recent version of TeX Live (2012 or later is fine)
5. a directory `base`
6. the url `http://localhost:8080` points to `base/stacks-website`

Here are the instructions:

1. clone `stacks-website` using `git clone https://github.com/stacks/stacks-website`

2. change directories to `stacks-website/` and initialize the submodules using `git submodule init` and `git submodule update`

3. change directories to `stacks-website/` and clone the Stacks project into the (not yet existing) `tex/` subdirectory using `git clone git://github.com/stacks/stacks-project tex`

4. change one occurence of `http://stacks.math.columbia.edu/tag/` in `stacks-website/tex/scripts/tag_up.py` to `http://localhost:8080/tag/

5. run `make tags` in `stacks-website/tex/`

6. clone `stacks-tools` in the `base` directory using `git clone https://github.com/stacks/stacks-tools`

7. change directories to `stacks-tools` and create the database by calling `python create.py`

8. back in the `base` directory execute the following commands:

        mkdir stacks-website/database
        chmod 0777 stacks-website/database
        mv stacks-tools/stacks.sqlite stacks-website/database
        chmod 0777 stacks-website/database/stacks.sqlite
This will create a directory with the database in it with the correct permissions for the webserver. To set permissions for the cache correctly execute

        chmod 0777 stacks-website/php/cache
9. change directory into stacks-website and edit the file `conf.ini` setting database = "database/stacks.sqlite", directory = "", and project = "/path/to/base/stacks-website/tex"

10. sanity check: at this point if you point your browser to `http://localhost:8080` you should not get an error concerning the database

11. get the correct styling in EpicEditor by executing

        ln -s ../../../../../css/stacks-editor.css js/EpicEditor/epiceditor/themes/editor/stacks-editor.css
        ln -s ../../../../../css/stacks-preview.css js/EpicEditor/epiceditor/themes/preview/stacks-preview.css
from the `stacks-website` directory

12. make XyJax work by editing the last line of `stacks-website/js/XyJax/extensions/TeX/xypic.js` and replacing `MathJax.Ajax.loadComplete("[MathJax]/extensions/TeX/xypic.js");` by `MathJax.Ajax.loadComplete("/js/XyJax/extensions/TeX/xypic.js");`

13. change directories to `stacks-tools` and update the database by calling `python update.py` as well as `python macros.py`

14. create a directory `stacks-website/data` by executing `mkdir stacks-website/data`, change directories to `stacks-tools`, and create the graph files by calling `python graphs.py`

Please contact the maintainer or create an issue if you encounter problems.


Updating the website
--------------------

1. Update the `stacks-website/tex` folder using `git pull`
2. run `make tags` as in step 5 above
3. Run steps 13 and 14 above
