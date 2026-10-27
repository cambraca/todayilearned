codesnippetgeshi

This plugin allow the use of CodeSnippetGeshi plugin from CKeditor inside
Drupal. It is used to edit source code that will be processed by
Geshifilter module.

=== Installation ===
Go to https://ckeditor.com/cke4/addons/plugins/all and download the following
plugins:

* Ajax(https://ckeditor.com/cke4/addon/ajax)(dependency)
* XML(https://ckeditor.com/cke4/addon/xml)(dependency)
* CodeSnippet(https://ckeditor.com/cke4/addon/codesnippet)(dependency)
* CodeSnippetGeshi(https://ckeditor.com/cke4/addon/codesnippetgeshi)

Extract then into libraries(/libraries) folder. For example, you will have
a file tree like this:

...
core
...
libraries
    ajax
        plugin.js
    codesnippet
        plugin.js
    codesnippetgeshi
        plugin.js
    xml
        plugin.js
...

Go to example.com/admin/config/content/formats(replace example.com with
the real name of your site). Choose one or more texts formats to use the
plugin. In the text format you choose, add the codesnippetgeshi button
to the editor toolbar.

=== Use ===
Click on the button in the toolbar to add a new code block. A popup window
will show. In this new window choose the language you want and add the code
in the textbox. Click on "ok".
Now you are back to node edit. Your code will show in ckeditor, already with
sintax hightlight. If you need to edit, just double click on it.

=== Background Color ===
I set a background color so the code blocks are more visible. If this is not wanted,
I may remove or document how to change it.
