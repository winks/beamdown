BeamDown, a (semi-)Markdown to TeX (Beamer) converter
=====================================================

written by Florian Anderiasch, fa at art-core dot org

version 1.0, 2010-05-22

requirements for TeX generation
-------------------------------
* PHP 5.3

requirements for PDF generation
-------------------------------
* latex
* beamer, http://en.wikipedia.org/wiki/Beamer_%28LaTeX%29 (aptitude install latex-beamer)
* listings, http://en.wikibooks.org/wiki/LaTeX/Packages/Listings

howto
-----
    $ ./beamdown-demo.php input.txt > input.tex
    $ latex input.tex
    $ dvipdf input.dvi

done, there should be 'input.pdf'

included files
--------------
* BeamDown.php
* beamdown-demo.php
* README.md
* templates/default/beamer_header.tex
* templates/default/beamer_body.tex
* templates/default/beamer_footer.tex
* tests/example.txt
* tests/example.tex
* tests/example.pdf
* tests/demo.txt


syntax
------

    this is a headline
    =================
    here's any type of content
    it can be as long or as
    short as
    you
    like, just like all the == in the divider

    another slide
    ============[plain]
    plain means that \\
    this is plain \TeX

    the next slide (with a table)
    ============================[table]
    a & b & c \\
    d & e & f \\

    and another one
    ==============[lang=php]
    // so this is a \begin{lstlisting} in php
    $a = "foo";
    echo $a;
    $x = 4 + 3;
    // no escaping needed!

additonal notes
---------------
* beamdown-demo.php should explain all public methods you can use.
* BeamDown doesn't know about $argc/$argv
* developed on Debian lenny with only "latex-beamer" and dependencies installed
