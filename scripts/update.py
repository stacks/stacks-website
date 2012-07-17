import os

# this file is a sorry excuse for a Makefile

# create tags, titles and tex for the website
os.system("python import_tags.py")
os.system("python import_titles.py")
os.system("python import_tex.py")
# create archives
if not os.path.exists("../archives"):
  os.system("mkdir ../archives")
os.system("tar -c -f ../archives/stacks-pdfs.tar --exclude book.pdf --transform=s@tex/tags/tmp@stacks-pdfs@ ../tex/tags/tmp/*.pdf")
os.system("tar -c -f ../archives/stacks-dvis.tar --exclude book.dvi --transform=s@tex/tags/tmp@stacks-dvis@ ../tex/tags/tmp/*.dvi")
os.system("git archive HEAD --prefix=stacks-project/ --remote=../tex/ | bzip2 > ../archives/stacks-project.tar.bz2")
