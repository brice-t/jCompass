What is jCompass ?
==============================

This project is a plugin for [Jelix](http://jelix.org) PHP framework. It allows you to use easily [Compass](http://compass-style.org/) dynamic stylesheet language in Jelix.

This is a plugin of CSSprepro which is itself an htmlresponse plugin for Jelix.



Installation
============

Under Jelix default configuration, create (if missing) a "CSSprepro" directory in your project's "plugins" directory.
Clone this repository in that directory with :

    git clone --recursive git@github.com:brice-t/jCompass.git


Note that you should have your app plugin directory in your modulesPath (defaultconfig.ini.php or entry point's config.ini.php) to get it working.
The value should be at least :

    modulesPath="app:modules/"

You need an up and running installation of Compass on command line to get it working (e.g. on a Debian-based distro, ''apt-get install ruby-compass'' will do the trick).



Usage
=====

When including a CSS file (e.g. with addCSSLink()) you should set 'sass'=>true or 'scss'=>true as a param.

Another way of having a file compiled with Compass is including as file with .scss or .sass extension. You can set expected extensions in the comma-separated value of _CSSprepro\_jCompass\_extensions_ under the _jResponseHtml_ section.

E.g. in your response :

`$this->addCSSLink($gJConfig->urlengine['basePath'].'themes/'.$gJConfig->theme.'/Css/style.sass');`

or

`$this->addCSSLink($gJConfig->urlengine['basePath'].'themes/'.$gJConfig->theme.'/Css/style.sassFile', array( 'sass' => true ));`


Your config file must activate jCompass plugin :

    [jResponseHtml]
    plugins=jCompass

N.B. : the directories containing Compass files should be writable by your web server ! Indeed, compiled files will be written in that very same directory so that relative urls go on working ...




Config
======

You can configure jCompass's behviour regarding compilation:

    [jResponseHtml]
    ;...
    ; always|onchange|once
    CSSprepro_jCompass_compile=always

If CSSprepro\_jPhpsass\_compile's value is not valid or empty, its default value is onchange.

* always : compile Compass file on all requests
* onchange : compile Compass file only if it has changed
* once : compile Compass file once and never compile it again (until compiled file is removed)

