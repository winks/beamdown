<?php
namespace beamdown;
/**
 * BeamDown - a (semi-)Markdown to TeX (Beamer) converter
 *      written by Florian Anderiasch <fa at art-core dot org>
 *      version 1.0, 2010-05-22
 */
class BeamDown {
    /**
     * The filename of the input file to be converted
     */
    private $input;
    /**
     * The path to the TeX templates
     */
    private $path;
    /**
     * The text after the input file has been read
     */
    private $text;
    /**
     * All errors
     */
    private $err = array();
    /**
     * The template file names, without a path
     */
    private $tpl = array(
        'head' => 'beamer_header.tex',
        'body' => 'beamer_body.tex',
        'foot' => 'beamer_footer.tex',
    );
    /**
     * Template fragments to be filled in
     */
    private $fragments = array(
        'pre'           => "\\lstset{language=%s}\n",
        'begin_plain'   => '',
        'end_plain'     => '',
        'begin_table'   => "\\begin{tabular}{ l c r }\n",
        'end_table'     => "\n\\end{tabular}",
        'begin_listing' => "\\begin{lstlisting}\n",
        'end_listing'   => "\n\\end{lstlisting}",
    );
    /**
     * Languages supported by 'listings'
     */
    private $languages = array('php','tex');

    /**
     * Constructor
     * @param string $path the path to the TeX templates
     */
    public function __construct($path = './templates/default') {
        $x = $this->checkTemplates($path);
        if (!$x) {
            echo $this->showErrors();
            exit(3);
        }
        $this->fragments = array_merge($this->fragments, $this->readTemplates());
    }

    /**
     * Show the text
     * @return string
     */
    public function getText() {
        return $this->text;
    }

    /**
     * Show all errors
     * @return string
     */
    public function showErrors() {
        return join("", $this->err);
    }

    /**
     * Read from a file
     * @param string $input the filename to read
     * @return bool
     */
    public function readfile($input) {
        // fix "unknown" path of a file in same directory without "./" prefix
        if (false === strpos($input, DIRECTORY_SEPARATOR)) {
            $input = '.' . DIRECTORY_SEPARATOR . $input;
        }
        $input = realpath($input);
        if (!is_readable($input)) {
            $this->err[] = "{$input} not readable." . PHP_EOL;
            return false;
        }
        $this->input = $input;
        $this->text = file_get_contents($this->input);
        if (strlen($this->text) == 0) {
            $this->err[] = "empty file read" . PHP_EOL;
            return false;
        }
        return true;
    }

    /**
     * Build TeX from plain text
     * @param string $text the text to transform
     * @return string
     */
    public function build($text = null) {
        if (is_null($text)) {
            $text = $this->text;
        }
        $tex = $this->fragments['head'];

        // get rid of windows line breaks
        $text = preg_replace("(\r\n)","\n", $text);

        // split by headers and a ==== line next, with optional [mode]
        preg_match_all('( (?P<title>[^\n]+)\n =+(?: \[(?P<mode>[^\n]+)\])?\n (?P<text> (?:(?:[^\n]+\n)|(?:\n(?!([^\n]+\n=+))))+) )x', $text, $m, PREG_SET_ORDER);

        $slides = array();

        foreach($m as $k => $v) {
            $parts['pre'] = '';
            $parts['title'] = trim($v['title']);
            $parts['text'] = trim($v['text']);
            $mode = trim($v['mode']);

            $parts['title'] = $this->texify($parts['title']);

            if (substr($mode, 0, 5) == 'lang=') {
                $lang = substr($mode, 5);
                $parts['pre'] = sprintf($this->fragments['pre'], $lang);
                $mode = "listing";
            } else if ($mode == "table"){
                // nothing special
            } else if ($mode == "plain") {
                $parts['text'] = $this->texify($parts['text']);
            } else {
                $mode = "listing";
            }

            $parts['begin'] = $this->fragments['begin_'.$mode];
            $parts['end'] = $this->fragments['end_'.$mode];

            $frame[$k] = $this->fragments['body'];
            foreach($parts as $key => $val) {
                $frame[$k] = str_replace('%%'.$key.'%%', $parts[$key], $frame[$k]);
            }
        }

        $tex .= join("", $frame);
        $tex .= $this->fragments['foot'];

        return $tex;
    }

    /**
     * Sets the template dir
     * @param string $path the template dir
     */
    public function setTemplateDir($path = null) {
        $success = false;
        if (is_null($path)) {
            $this->err[] = "no valid path given" . PHP_EOL;
            return false;
        }
        $success = $this->checkTemplates($path);
        return $success;
    }

    /**
     * Returns the template dir
     * @return string
     */
    public function getTemplateDir() {
        return $this->path;
    }

    /**
     * Escape special characters for TeX
     * @param string $arg the text to escape
     * @return string
     */
    private function texify($arg) {
        $arg = preg_replace("(([{}_^#&$%]))", "\\\\\\1", $arg);
        $arg = preg_replace("(([<>]))", "\$\\1\$", $arg);
        $arg = str_replace('|', '\\textbar', $arg);
        $arg = str_replace('^', '\\^{}', $arg);
        $arg = str_replace('~', '\sim', $arg);

        return $arg;
    }

    /**
     * Check if templates are readable
     * @return bool
     */
    private function checkTemplates($path) {
        $path = realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . $path);
        $success = true;
        foreach($this->tpl as $tpl) {
            $file = $path . DIRECTORY_SEPARATOR . $tpl;
            if (!is_readable($file)) {
                $this->err[] = "{$file} not readable." . PHP_EOL;
                $success = false;
            }
        }
        if ($success) {
            $this->path = $path;
        }
        return $success;
    }

    /**
     * Read templates
     * @return array
     */
    private function readTemplates() {
        $tpl = array();
        foreach($this->tpl as $key => $val) {
            $tpl[$key] = file_get_contents($this->path . DIRECTORY_SEPARATOR . $this->tpl[$key]);
        }
        return $tpl;
    }
}
