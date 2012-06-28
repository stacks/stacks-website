Description
-----------
This is a new version of the website for the [Stacks project](http://math.columbia.edu/algebraic_geometry/stacks-git/), enabling a comment system and improved tag lookup.


Configuration
-------------
The following configuration is to remind me of what I have to do to get an instance running. It is by no means complete, let alone finished. In due time this will be extended.


1. clone the `stacks-website` project using
`git clone https://github.com/pbelmans/stacks-website.git`

2. change directories to `stacks-website/`and initialize the submodules using `git submodule init` and `git submodule update`

3. create all tags in the actual project by running `make tags` in `stacks-website/tex`

4. write decent code to initialize the website which at the moment is not yet easy to accomplish
