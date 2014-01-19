/** Stacks Flavored Markdown
 * In order to accomodate some of the specifics of the Stacks project we preprocess text. This entails
 * 1) double backslashes are converted to quadruple backslashes to ensure proper LaTeX parsing
 * 2) interpreting \ref{}
 */
function sfm(text) {
  // all double backslashed should be doubled to quadruple backslashes to ensure proper LaTeX results
  text = text.replace(/\\\\/g, "\\\\\\\\");
  // \ref{0000} can point to the correct URL (all others have to be (ab)used by MathJax)
  var lines = text.split(/\r?\n/);
  for (var i = 0; i < lines.length; i++) {
    if (lines[i].substring(0, 4) != '    ')
      lines[i] = lines[i].replace(/\\ref\{(\w{4})\}/g, "[$1](http://stacks.math.columbia.edu/tag/$1)");
  }
  text = lines.join("\n");

  // fix underscores (all underscores in math mode will be escaped
  var result = '';
  var mathmode = false;
  for (c in text) {
    // match math mode (\begin{equation}\end{equation} goes fine mysteriously)
    if (text[c] == "$") {
      // handle $$ correctly
      if (window.parseInt(c) + 1 < text.length && text[window.parseInt(c) + 1] != "$")
        mathmode = !mathmode;
    }

    // replace unescaped underscores in math mode, the accessed position always exists because we had to enter math mode first
    if (mathmode && text[c] == "_" && text[window.parseInt(c) - 1] != "\\")
      result += "\\_";
    // replace * in math mode: we are not emphasizing things
    else if (mathmode && text[c] == "*" && text[window.parseInt(c) - 1] != "\\")
      result += "\\*";
    // escape \{ in math mode to \\{
    else if (mathmode && text[c] == "{" && text[window.parseInt(c) - 1] == "\\")
      result += "\\\\{";
    // escape \} in math mode to \\}
    else if (mathmode && text[c] == "}" && text[window.parseInt(c) - 1] == "\\")
      result += "\\\\}";
    else
      result += text[c];
  }
  

  return marked(result);
}
