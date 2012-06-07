def parse_contentsline(contentsline):
  parts = contentsline.split('}{')

  # sanitize first element to determine type
  parts[0] = parts[0][15:]
  # remove clutter
  parts = map(lambda part: part.strip('\{\}'), parts)

  return [parts[0], parts[2], parts[3], parts[4]]


def parse_book_toc(filename):
  toc = open(filename, 'r')

  for line in toc:
    parts = parse_contentsline(line)

    print 'type:   {0}'.format(parts[0])
    print 'number: {0}'.format(parts[1])
    print 'title:  {0}'.format(parts[2])
    print 'page:   {0}\n'.format(parts[3])
  
  toc.close()


def parse_newlabel(newlabel):
  parts = newlabel.split('}{')

  # get the actual label
  parts[0] = parts[0][10:]
  # remove clutter
  parts = map(lambda part: part.strip('\{\}'), parts)

  return parts


def parse_aux(filename):
  aux = open(filename)

  for line in aux:
    # not interesting, go to next line
    if not line.startswith("\\newlabel{"):
      continue

    parts = parse_newlabel(line)

    # not an actual label, just LaTeX layout bookkeeping, go to next line
    if len(parts) == 2:
      continue

    print 'label:  {0}'.format(parts[0])
    print 'number: {0}'.format(parts[1])
    print 'page:   {0}'.format(parts[2])

parse_aux('tex/tags/tmp/adequate.aux')
parse_book_toc('tex/tags/tmp/book.toc')
