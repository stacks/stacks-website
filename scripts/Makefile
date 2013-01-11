# Default target doesn't do anything
.PHONY: default
default:
	@echo "%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%"
	@echo "% This target doesn't do anything.                       %"
	@echo "% Available targets: update, create                      %"
	@echo "% The create target creates the database                 %"
	@echo "% The update target updates the databases and archives   %"
	@echo "%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%"

.PHONY: create
create:
	if [ -f ../db/stacks.sqlite ]; then \
		echo "Error: file ../db/stacks.sqlite already exists"; \
	elif [ -f stacks.sqlite ]; then \
		echo "Please remove or rename ./stacks.sqlite"; \
	else \
		python create_database.py; \
		mkdir -p ../db; \
		mv -n stacks.sqlite ../db/stacks.sqlite; \
		echo "Make sure to run make update to update the database."; \
	fi

.PHONY: update
update:
	python import_tags.py
	python import_titles.py
	python import_tex.py
	python import_bibliography.py
	# create archives
	mkdir -p ../archives
	tar -c -f ../archives/stacks-pdfs.tar --exclude book.pdf --transform=s@tex/tags/tmp@stacks-pdfs@ ../tex/tags/tmp/*.pdf
	tar -c -f ../archives/stacks-dvis.tar --exclude book.dvi --transform=s@tex/tags/tmp@stacks-dvis@ ../tex/tags/tmp/*.dvi
	git archive HEAD --prefix=stacks-project/ --remote=../tex/ | bzip2 > ../archives/stacks-project.tar.bz2
