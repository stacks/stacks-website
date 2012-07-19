<!doctype html>
<?php
  include('config.php');
  include('functions.php');
?>
<html>
  <head>
    <title>Stacks Project -- git howto</title>
    <link rel="stylesheet" type="text/css" href="<?php print(full_url('style.css')); ?>">
    <link rel="icon" type="image/vnd.microsoft.icon" href="<?php print(full_url('stacks.ico')); ?>"> 
    <link rel="shortcut icon" type="image/vnd.microsoft.icon" href="<?php print(full_url('stacks.ico')); ?>"> 
    <meta charset="utf-8">
  </head>

  <body>
    <h1><a href="<?php print(full_url('')); ?>">The Stacks Project</a></h1>
    <?php print_navigation(); ?>

    <h2>How to use git with the Stacks project</h2>
    <p>I assume you have cloned the official git repository at GitHub using the command
    <pre><code>git clone git://github.com/stacks/stacks-project.git</code></pre>
    Then you will have a directory called stacks-project. In the rest of this howto we assume that you have changed directory into stacks-project.</p>
    
    <h2>Warning</h2>
    <p>It is easy to make mistakes using git, so it is a good idea to back up your changes by simply copying them somewhere safe before trying something tricky.</p>
    
    <h2>Pulling updates</h2>
    <p>From time to time type
    <pre><code>git pull</code></pre>
    to automatically pull new updates from GitHub.</p>
    
    <h2>Working on the project (simplest version)</h2>
    <p>Create a new branch that you will use to make changes to the project.
    <pre><code>git branch newbranch</code></pre>
    The reason for doing this is that it is a bit confusing to deal with the consequences of pulling updates when you have edited the master branch. Switch to the new branch by using the command
    <pre><code>git checkout newbranch</code></pre>
    
    <p>Of course you can switch back to the master branch by executing
    <pre><code>git checkout master</code></pre>
    whenever you want. Making sure that you are on <var>newbranch</var> can be done by typing
    <pre><code>git branch</code></pre>
    The branch that has a * next to it is the one you are currently on. Assuming you are on <var>newbranch</var>, make edits, etc. Whenever you want to add your changes to newbranch you type
    <pre><code>git commit -a</code></pre>
    It brings up an editor where you can write a short commit message. Keep going like this until you are happy with the result and want to submit your changes. To do this, make sure you have committed your latest changes, and then type
    <pre><code>git format-patch -n master..newbranch --stdout > my.patch</code></pre>
    This creates the file <var>my.patch</var> which you can email to <a href="mailto:stacks.project@gmail.com">stacks.project@gmail.com</a> for inclusion in the project.</p>
    
    <h2>Keeping in sync with the repository at GitHub</h2>
    <p>To keep in sync with the repository at GitHub you can do the following steps
    <pre><code>git checkout master
git pull
git checkout newbranch
git rebase master</code></pre>
    
    <p>The result of these steps is that the <var>master</var> branch is synced with the <var>master</var> branch at GitHub and that your branch <var>newbranch</var> is rebased on this synced version of the <var>master</var> branch. Actually the last step may lead to <em>conflicts</em>. This means that you have edited some lines that have also been changed in the origin repository (but in a different way). Whenever this
    happens you have to <em>resolve</em> the conflicts. This is done by editing the affected files <var>A</var>, <var>B</var>, ... (you can find them by grepping for <code>&lt;&lt;&lt;&lt;</code> and <code>&gt;&gt;&gt;&gt;</code>), editing the troublesome spots in <var>A</var>, <var>B</var>, ..., marking the file done by
    <pre><code>git add A
git add B</code></pre>
    and then continuing with the rebase procedure typing
    <pre><code>git rebase --continue</code></pre>
    until done. Create and email patches using <code>git format-patch</code> as explained above. (In fact this is the reason for using <code>git rebase</code> instead of <code>git merge</code>.)</p>
    
    <h2>More information</h2>
    There is a lot more you can do with git. For example you can set it up so that the maintainer of the Stacks project can pull changes directly from your own repository. To learn more see <a href="http://git-scm.com">git-scm.com</a> and <a href="http://www.kernel.org/pub/software/scm/git/docs/">kernel.org/pub/software/scm/git/docs/</a>.

    <p id="backlink">Back to the <a href="<?php print(full_url('')); ?>">main page</a>.
  </body>
</html>
