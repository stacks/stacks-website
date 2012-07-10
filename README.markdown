Description
-----------
This is a new version of the website for the [Stacks project](http://math.columbia.edu/algebraic_geometry/stacks-git/), enabling a comment system and improved tag lookup.


Configuration
-------------
The following configuration is to remind me of what I have to do to get an instance running. It is by no means complete, let alone finished. In due time this will be extended.


1. clone the `stacks-website` project using
`git clone https://github.com/pbelmans/stacks-website.git`

2. change directories to `stacks-website/`and initialize the submodules using `git submodule init` and `git submodule update`

3. create all tags in the actual project by running `make tags` in `stacks-website/tex/`

4. create all necessary pdf's and dvi's using `make pdfs` and `make dvis` in the same `stacks-website/tex/` directory

5. create the database by calling `python scripts/config.py` in `stacks-website/`

6. put the file `stacks.sqlite` (the newly created database) in a directory that is not accessible from outside, apply `chmod 777 stacks.sqlite` and `chmod 777 ../` from the location of the database (PHP requires that both the database and the directory containing it have these...)

7. make the required modifications to `stacks-website/config.php` and `stacks-website/scripts/config.py`, mostly concerning the location of the website and the database on the server

8. run `python scripts/import_tags.py`, `python scripts/import_titles.py` and `python scripts/import_tex.py`, preferably (but not necessarily I guess) in that order

9. combine all of the above in one single install script
