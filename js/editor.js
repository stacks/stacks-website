// Chromium (and Chrome too I presume) adds a bogus character when a space follows after a line break (or something like that)
// remove this by hand for now TODO fix EpicEditor
function sanitize(s) {
  var output = '';
  for (c in s) {
    if (s.charCodeAt(c) != 160) output += s[c]
    else output += " ";
  }
 
  return output;
}

var fullscreenNotice = false;

var editor = new EpicEditor(options).load(function() {
    // TODO find out why this must be a callback in the loader, editor.on('load', ...) doesn't seem to be working?!
    // hide textarea, EpicEditor will take over
    document.getElementById('comment-textarea').style.display = 'none';
    // when the form is submitted copy the contents from EpicEditor to textarea
    document.getElementById('comment-form').onsubmit = function() {
      document.getElementById('comment-textarea').value = sanitize(editor.exportFile());
    };

    // add a notice on how to get out the fullscreen mode
    var wrapper = this.getElement('wrapper');
    var button = wrapper.getElementsByClassName('epiceditor-fullscreen-btn')[0];
    button.onclick = function() {
      if (!fullscreenNotice) {
        alert('To get out the fullscreen mode, press Escape.');
        fullscreenNotice = true;
      }
    }

    // inform the user he is in preview mode
    document.getElementById('epiceditor-status').innerHTML = '(editing)';
});

function preview(iframe) {
  var mathjax = iframe.contentWindow.MathJax;

  mathjax.Hub.Config({
    tex2jax: {inlineMath: [['$','$'], ['\\(','\\)']]}
  });

  var previewer = iframe.contentDocument.getElementById('epiceditor-preview');
  mathjax.Hub.Queue(mathjax.Hub.Typeset(previewer));
}

editor.on('preview', function() {
    var iframe = editor.getElement('previewerIframe');

    if (iframe.contentDocument.getElementById('previewer-mathjax') == null) {
      var script = iframe.contentDocument.createElement('script');
      script.type = 'text/javascript';
      script.src = 'http://cdn.mathjax.org/mathjax/latest/MathJax.js?config=TeX-AMS_HTML';
      script.setAttribute('id', 'previewer-mathjax');
      iframe.contentDocument.head.appendChild(script);
    }

    // inform the user he is in preview mode
    document.getElementById('epiceditor-status').innerHTML = '(previewing)';

    // wait a little for MathJax to initialize
    // TODO might this be possible through a callback?
    if (iframe.contentWindow.MathJax == null) {
      setTimeout(function() { preview(iframe) }, 500);
    }
    else {
      preview(iframe);
    };
});

editor.on('edit', function() {
    // inform the user he is in preview mode
    document.getElementById('epiceditor-status').innerHTML = '(editing)';
});
