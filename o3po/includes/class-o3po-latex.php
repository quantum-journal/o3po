<?php

/**
 * A collection of various functions to transform, parse, and convert latex code to and from utf8 and other encodings
 *
 * @link       https://quantum-journal.org/o3po/
 * @since      0.1.0
 *
 * @package    O3PO
 * @subpackage O3PO/includes
 */

require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-o3po-utility.php';

/**
 * This class provides a collection of various functions to transform, parse, and convert latex code to and from utf8 and other encodings.
 *
 * @since      0.1.0
 * @package    O3PO
 * @subpackage O3PO/includes
 * @author     Christian Gogolin <o3po@quantum-journal.org>
 */
class O3PO_Latex extends O3PO_Latex_Dictionary_Provider
{
        /**
         * Convert LaTeX code to utf8, leaving along everything within math mode.
         *
         * This is the function that actually does all the replacements. It
         * leaves mathematicla formulas enclosed in $...$ intact so that they can be
         * displayed nicely using MathJax. This function turns various math modes
         * into standard linline mode $a+b$. At the moment this is a feature.
         *
         * @since    0.1.0
         * @access   public
         * @param    string     $latex_text    Latex code whose non-math part is to be converted to utf8
         * @param    boolean    $clean         Whether to perform some cleanup at the end.
         * */
    static public function latex_to_utf8_outside_math_mode( $latex_text, $clean=true  ) {

        $latex_lines = self::preg_split_at_latex_math_mode_delimters($latex_text);
        $latex_text_converted = '';
        foreach ($latex_lines as $x => $line) {
            if ($x % 2 === 1) //In math mode
                $latex_text_converted .= '$' . $line . '$';
                //$latex_text_converted .= '$'.preg_replace('/([a-zA-Z]{2,})/', '\\\\'."$1", $line).'$';
            else { //Outside math mode
                foreach (self::get_latex_special_chars_dictionary() as $target => $substitute) {
                    $line = preg_replace('#'.'(?<!\\\\)'.$target.'#', $substitute, $line);
                    if( strpos($line, '\\') === false ) break;
                }
                if($clean)
                {
                    foreach (self::get_latex_clean_up_dictionary() as $target => $substitute)
                    {
                        $line = str_replace($target, $substitute, $line);
                    }
                    $line = preg_replace('#  +#', ' ', $line);
                }
                $latex_text_converted .= $line;
            }
        }

        return $latex_text_converted;
    }


        /**
         * Preg split LaTeX code at math mode delimters.
         *
         * @since    0.1.0
         * @access   public
         * @param    string    $text    Text to be split at LaTeX math mode delimiters such as $$ \[\] \(\).
         */
    static public function preg_split_at_latex_math_mode_delimters( $text ) {

        return preg_split('#(?<!\\\\)(?:\$\$|\$|\\\\\[|\\\\\]|\\\\\(|\\\\\))#', $text);
    }


        /**
         * Strpos in latex code, but only taking into account the part of
         * code that is not in math mode.
         *
         * @since    0.1.0
         * @access   public
         * @param    string   $latex_text      Latex text in whose non-math parts the string is to be found.
         * @param    string   $string          String to be found.
         */
    static public function strpos_outside_math_mode( $latex_text, $string ) {

        $latex_lines = self::preg_split_at_latex_math_mode_delimters($latex_text);
        foreach ($latex_lines as $x => $line) {
            if ($x % 2 !== 1) //Outside math mode
                return strpos($line, $string);
        }

        return false;
    }

        /**
         * Parse bbl code of potentially multiple bibliographies.
         *
         * Parses bbl code produced by either bibtex or biblatex as well as
         * such written by authors by hand. $bbl may be a concatenation of
         * multiple bibliographies.
         *
         * @since    0.1.0
         * @access   public
         * @param    string    $bbl    Bibliography or concatenation of bibliographies in .bbl format as it is produced by BibTeX, Biber, and BibLaTeX.
         */
    static public function parse_bbl( $bbl ) {

        $citations = array();
        $bbls = preg_split('/(% \$ biblatex auxiliary file \$|\\\\begin{thebibliography}|\\\\begin{references})/', $bbl, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
        foreach($bbls as $individual_bbl)
            $citations = array_merge($citations, static::parse_single_bbl($individual_bbl));

        return $citations;
    }


        /**
         * Parse bbl code of an individual bibliography.
         *
         * Parses bbl code produced by either bibtex or biblatex as well as
         * such written by authors by hand.
         *
         * @since    0.1.0
         * @access   public
         * @param    string    $bbl    Bibliography in .bbl format as it is produced by BibTeX, Biber, and BibLaTeX.
         */
    static public function parse_single_bbl( $bbl ) {

        preg_match('/% \$ (biblatex bbl format|biblatex) version ([0-9.]*) \$/' , $bbl, $version);
        if( !empty($version[1]))
            $biblatex = $version[1];
        else
            $biblatex = false;

        if( $biblatex ) {
            $str_replacements = array(
                '\\bibrangedash ' => '-',
                '\\bibinitperiod' => '.',
                '\\bibinitdelim ' => ' ',
                                      );
            $preg_replacements = array(
                '\\\\\s+' => ' ',
                '(?<!\\\\)%.*' => '', //remove all comments
                                       );

            $entries = preg_split('/\\\\entry/', $bbl);
            foreach($entries as $n => $entry) {
                if(strpos($entry, '\\endentry' ) === false ) continue;

                preg_match('/^\s*({[^}]*})({[^}]*})/' , $entry, $args);
                if(!empty($args[1]))
                    $citations[$n]['key'] = substr($args[1], 1, -1);
                if(!empty($args[2]))
                    $citations[$n]['type'] = substr($args[2], 1, -1);
                $citations[$n]['ref'] = $n;

                preg_match('#\\\\name\{[^}]*\}\{[^}]*\}\{\}(?=\{((?:[^{}]++|\{(?1)\})*)\})#', $entry, $name);//matches balanced parenthesis (Note the use of (?1) here!) to test changes go here https://regex101.com/r/bVHadc/1
                foreach(preg_split('/{{hash=/', $name[1]) as $i => $author_bbl) {
                    if($i === 0) continue;
                    preg_match('#family={(.*?)},#', $author_bbl, $family );
                    preg_match('#familyi={(.*?)},#', $author_bbl, $familyi );
                    preg_match('#given={(.*?)},#', $author_bbl, $given );
                    preg_match('#giveni={(.*?)},#', $author_bbl, $giveni );

                    if(!empty($family[1]))
                        $citations[$n]['author'][$i]['family'] = $family[1];
                    if(!empty($familyi[1]))
                        $citations[$n]['author'][$i]['familyi'] = $familyi[1];
                    if(!empty($given[1]))
                        $citations[$n]['author'][$i]['given'] = $given[1];
                    if(!empty($giveni[1]))
                        $citations[$n]['author'][$i]['giveni'] = $giveni[1];
                }

                preg_match('#\\\\list{publisher}{[^}*]}(?=\{((?:[^{}]++|\{(?1)\})++)\})#', $entry, $publisher);
                if(!empty($publisher[1]))
                    $citations[$n]['publisher'] = str_replace('%', '', $publisher[1]);

                preg_match('#\\\\verb{doi}\s*?\\\\verb ([^\s]*)\s*\\\\endverb#', $entry, $doi);
                if(!empty($doi[1]))
                    $citations[$n]['doi'] = $doi[1];

                if(empty($citations[$n]['doi']))
                {
                    preg_match('#\\\\verb{url}\s*?\\\\verb ([^\s]*)\s*\\\\endverb#', $entry, $url);
                    if(!empty($url[1]))
                        $citations[$n]['url'] = $url[1];
                }

                preg_match('#\\\\verb{eprint}\s*?\\\\verb ([^\s]*)\s*\\\\endverb#', $entry, $eprint);
                if(!empty($eprint[1]))
                    $citations[$n]['eprint'] = $eprint[1];

                $fields = array(
                    'pages',
                    'title',
                    'volume',
    				'journaltitle',
                    'booktitle',
                    'issuetitle',
    				'month',
                    'year',
                    'chapter',
                    'note',
                    'institution',
                    'organization',
                    'howpublished',
                    'editor',
                                );
                foreach( $fields as $field) {
                    preg_match('#\\\\field{' . $field .'}{*([^}]*)}*#', $entry, $arg);
                    if(!empty($arg[1]))
                        $citations[$n][$field] = $arg[1];
                }

                $text = '';

                if(!empty($citations[$n]['author'])) {
                    $num_authors = count($citations[$n]['author']);
                    foreach( $citations[$n]['author'] as $i => $author) {
                        if(!empty($author['giveni']))
                            $text .= $author['giveni'];
                        if(!empty($author['giveni']) && !empty($author['family']))
                            $text .= " ";
                        if(!empty($author['family']))
                            $text .= $author['family'];
                        if($num_authors > 2) $text .= ", ";
                        if($i === $num_authors-1 ) $text .= "and ";
                    }
                    $text .= ' ';
                }
                switch ($citations[$n]['type']) {
                    case 'article':
                    case 'unpublished':
                    case 'periodical':
                    case 'suppperiodical':
                    case 'proceedings':
                    case 'mvproceedings':
                    case 'inproceedings':
                    case 'conference':
				        if(!empty($citations[$n]['title'])) $text .= "``" . $citations[$n]['title'] . "''";
				        if(!empty($citations[$n]['journaltitle'])) $text .= " " . $citations[$n]['journaltitle'];
                        if(!empty($citations[$n]['booktitle'])) $text .= " " . $citations[$n]['booktitle'];
                        if(!empty($citations[$n]['issuetitle'])) $text .= " " . $citations[$n]['issuetitle'];
				        if(!empty($citations[$n]['volume'])) $text .= " " . $citations[$n]['volume'];
                        if(!empty($citations[$n]['volume']) && !empty($citations[$n]['pages'])) $text .= ",";
				        if(!empty($citations[$n]['pages'])) $text .= " " . $citations[$n]['pages'];
				        if(!empty($citations[$n]['year'])) $text .= " (" . $citations[$n]['year'] . ")";
                        if(!empty($citations[$n]['note'])) $text .= " " . $citations[$n]['note'];
                        break;
                    case 'book':
                    case 'mvbook':
                    case 'inbook':
                    case 'bookinbook':
                    case 'suppbook':
                    case 'booklet':
                    case 'collection':
                    case 'mvcollection':
                    case 'incollection':
                    case 'suppcollection':
                    case 'reference':
                    case 'mvreference':
                    case 'inreference':
                        if(!empty($text) && !empty($citations[$n]['editor'])) $text .= ' ';
                        if(!empty($citations[$n]['editor'])) $text .= $citations[$n]['editor'] . " (eds.) ";
				        if(!empty($citations[$n]['title'])) $text .= "``" . $citations[$n]['title'] . "''";
				        if(!empty($citations[$n]['publisher'])) $text .= " " . $citations[$n]['publisher'];
                        if(!empty($citations[$n]['howpublished'])) $text .= " " . $citations[$n]['howpublished'];
                        if(!empty($citations[$n]['chapter'])) $text .= " chapter " . $citations[$n]['chapter'];
				        if(!empty($citations[$n]['year'])) $text .= " (" . $citations[$n]['year'] . ")";
                        break;
                    case 'thesis':
                    case 'mastersthesis':
                    case 'phdthesis':
                    case 'manual':
                    case 'patent':
                    case 'report':
                    case 'techreport':
                        if(!empty($citations[$n]['title'])) $text .= "``" . $citations[$n]['title'] . "''";
				        if(!empty($citations[$n]['type'])) $text .= " " . $citations[$n]['type'];
                        if(!empty($citations[$n]['number'])) $text .= " " . $citations[$n]['number'];
                        if(!empty($citations[$n]['institution'])) $text .= " " . $citations[$n]['institution'];
                        if(!empty($citations[$n]['organization'])) $text .= " " . $citations[$n]['organization'];
                        if(!empty($citations[$n]['location'])) $text .= " " . $citations[$n]['location'];
				        if(!empty($citations[$n]['year'])) $text .= " (" . $citations[$n]['year'] . ")";
                        break;
                    case 'misc':
                    case 'online':
                    case 'electronic':
                    case 'www':
                        if(!empty($text) && !empty($citations[$n]['editor'])) $text .= ' ';
                        if(!empty($citations[$n]['editor'])) $text .= $citations[$n]['editor'] . " (eds.) ";
				        if(!empty($citations[$n]['title'])) $text .= "``" . $citations[$n]['title'] . "''";
                        if(!empty($citations[$n]['howpublished'])) $text .= " " . $citations[$n]['howpublished'];
				        if(!empty($citations[$n]['year'])) $text .= " (" . $citations[$n]['year'] . ")";
                        break;
                    default:
                        if(!empty($citations[$n]['title'])) $text .= "``" . $citations[$n]['title'] . "''";
                        if(!empty($citations[$n]['year'])) $text .= " (" . $citations[$n]['year'] . ")";
                        break;
                }
                foreach ($str_replacements as $target => $substitute)
                    $text = str_replace($target, $substitute, $text);
                foreach ($preg_replacements as $target => $substitute) {
                    $text = preg_replace('#'.$target.'#', $substitute, $text);
                    if( strpos($text, '\\') === false ) break;
                }
                $text = self::latex_to_utf8_outside_math_mode($text);

                $text = preg_replace('!\s+!', ' ', $text);
                $text = trim($text, ". \t\n\r\0\x0B\s");

                    //$text = iconv(mb_detect_encoding($text, mb_detect_order(), true), "UTF-8", $text);

                $citations[$n]['text'] = $text . '.';
            }
        }
        else //no biblatex
        {
            $bbl = preg_replace('#(?<!\\\\)%.*#', '', $bbl); //remove all comments

            $contains_thebibliography_environment = preg_match('/\\\\begin{thebibliography}{([^}]*)}/' , $bbl, $longest_item);
            $contains_references_environment = preg_match('/\\\\begin{references}/' , $bbl);
            if( !$contains_thebibliography_environment || (!empty($longest_item[1]) && ctype_digit($longest_item[1])) )
                $style = 'numeric';
            else
                $style = 'author-year';

            $bbl = preg_replace('#\\\\(newcommand|providecommand|def)(?:\{| +|)(\\\\[@a-zA-Z]+)(?:\}| +|)(\[[0-9]\]|)(\[[^]]*\]|)(?=\{((?:[^{}]++|\{(?5)\})*)\})#', '', $bbl); //remove all \newcommand and similar (Note the use of (?5) here!) to test changes go here https://regex101.com/r/g7LCUO/1

//		$entries = preg_split('/\\\\bibitem(?=[^a-zA-Z])/', $bbl);
            $entries = preg_split('/\\\\bibitem\s*(?=[[{])/', $bbl);
//      $entries = preg_split('#\\\\bibitem\s*(?:(?=\[((?:[^\[\]]++|\[(?1)\])*)\])\s*\[(?1)\]|)\s*(?=\{((?:[^{}]++|\{(?2)\})*)\})\s*{(?2)}#', $bbl);

            $citations = array();

                /* $str_replacements_key = array( */
                /* '\\etalchar{+}' => '⁺', */
                /* '{' => '', */
                /* '}' => '', */
                /* '\\citenamefont' => '', */
                /* '\\natexlab' => '', */
                /* ); */
            $str_replacements = array(
                '\\ensuremath' => '',
                '\\eprintprefix' => '',
                '\\newblock' => '',
                '\\natexlab' => '',
                '\\citenamefont' => '',
                '\\bibnamefont' => '',
                '\\BibitemOpen' => '',
                '\\bibfnamefont' => '',
                '\\doibase' => '',
                '\\urlprefix' => '',
                '\\url' => '',
                '\\doi' => '',
                '\\cite' => '',
                '\\protect' => '',
                '\\path' => '',
                '\\penalty0' => '',
                '\\&' => '&',
                                      );
            $preg_replacements = array(
                '\\\\\s' => ' ',
                '(?<!\\\\)%.*' => '', //remove all comments
                '\\\\bib(info|field)\s*{(year|pages|journal|title|author|volume|editor|publisher|booktitle|address|series|series and number|howpublished|note|school|edition|eid|number)}\s*' => '',
                '\\\\BibitemShut\s*{(No|)Stop}' => '',
                '\\\\enquote\s*' => '', //this should be improved to actually add quotes
                '\\\\(href|Eprint|eprint)(@noop|)\s*{[^}]*}' => '',
                '\\\\(bf|tt|sf|sl)(series|family)[\s{]' => '',
                '\\\\text(em|it|bf|tt|rm|sl|sc)[\s{]' => '',
                '\\\\emph[\s{]' => '',
                '\\\\(em|it|tt|bf|sl)(?![a-zA-Z])' => '',
                '\\\\text(super|sub)script' => '',
                '\\\\spaceskip=' => '',
                '\\\\fontdimen[0-9]*' => '',
                '\\\\font (plus|minus)' => '',
                '\\\\font' => '',
                '\\\\hskip[^\\\\]*\\\\relax' => '',
                '\\\\relax' => '',
                                       );

            foreach($entries as $n => $entry) {
                $entry = preg_replace('#(\\\\end{thebibliography}|\\\\end{references}).*#s', '', $entry);
                if(strpos($entry, 'begin{thebibliography}' ) !== false || strpos($entry, 'begin{references}' ) !==false || empty($entry) || ($n === 0 && !$contains_thebibliography_environment && !$contains_references_environment) ) continue;

                $citations[$n] = array();
                preg_match('/^\s*(|\[[^\]]*\]){([^}]*)}/' , $entry, $args);
                if( $style !== 'numeric' && !empty($args[1]) && strlen($args[1]) >= 3) {
                    $key = substr($args[1], 1, -1);
                        /* foreach ($str_replacements_key as $target => $substitute) */
                        /* 	$key = str_replace($target, $substitute, $key); */
                    $key = trim(self::latex_to_utf8_outside_math_mode($key));
                    $citations[$n]['ref'] = $key;
                }
                else
                    $citations[$n]['ref'] = $n;

                if(isset($args[2]))
                    $citations[$n]['key'] = $args[2];

                if(isset($args[0]))
                    $entry = substr($entry, strlen($args[0]));

			preg_match('#\\\\doi\s*{([^}]*)}#' , $entry, $doi);
			if( empty($doi[1]) )
				preg_match('#\\\\doibase\s*([^} ]*)}#' , $entry, $doi);
			if( empty($doi[1]) )
				preg_match('#\\\\(?:href|url)\s*{.*doi\.org/([^} ]*)}#' , $entry, $doi);
			if( empty($doi[1]) )
				preg_match('#\\\\path\s*{doi:([^} ]*)}#' , $entry, $doi);
            if( !empty($doi[1]) )
                $citations[$n]['doi'] = str_replace('\\%', '_', str_replace('\\#', '_', str_replace('\\_', '_', $doi[1]))); //Undo escaping of special characters in LaTeX. TODO: make a method that does that propperly.
			preg_match('#\\\\(href\s*|Eprint)(@noop|)\s*{.*arxiv\.org/abs/([^}]*)}#' , $entry, $eprint);
			if( empty($eprint[3]) )
				preg_match('#(arxiv|arXiv)(:)(/*[a-z*-]*/*[0-9]+\.?[0-9]+v*[0-9]*)#' , $entry, $eprint);
			if( empty($eprint[3]) )
				preg_match('#()()(quant-ph/[0-9]+\.?[0-9]+v*[0-9]*)#' , $entry, $eprint);
            if( !empty($eprint[3]) )
                $citations[$n]['eprint'] = $eprint[3];

			preg_match('#\\\\(url)(@noop|)\s*{([^}]*)}#' , $entry, $url);
			if( empty($url[3]) && empty($citations[$n]['eprint']) && empty($citations[$n]['doi']) )
				preg_match('#\\\\(href)(@noop|)\s*{([^}]*)}#' , $entry, $url);
            if( !empty($url[3]) )
                $citations[$n]['url'] = $url[3];

            if( !empty($citations[$n]['url']) && ( !empty($citations[$n]['doi']) &&  strpos($citations[$n]['url'], $citations[$n]['doi']) !== false || !empty($citations[$n]['eprint']) &&  strpos($citations[$n]['url'], $citations[$n]['eprint']) !== false ) )
                unset($citations[$n]['url']);

                $text = $entry;


                foreach ($str_replacements as $target => $substitute)
                    $text = str_replace($target, $substitute, $text);

                foreach ($preg_replacements as $target => $substitute) {
                    $text = preg_replace('#'.$target.'#', $substitute, $text);
                    if( strpos($text, '\\') === false ) break;
                }
                $text = self::latex_to_utf8_outside_math_mode($text);

                $text = preg_replace('!\s+!', ' ', $text);
                $text = trim($text, ". \t\n\r\0\x0B\s");

                $citations[$n]['text'] = $text . '.';
            }
        }

        return $citations;
    }


    	/**
         * Convert utf8 text to LaTeX code.
         *
         * @since    0.1.0
         * @access   public
         * @param    string    $text    Text with special characters that are to be converted to LaTeX encoding.
         */
    static public function utf8_to_latex($text) {

        foreach (self::get_latex_special_chars_reverse_dictionary() as $target => $substitute) {
            if (mb_strlen($text) === strlen($text)) break;
            $text = preg_replace('#'.$target.'#', $substitute, $text);
        }

        return $text;
    }

    	/**
         * Convert utf8 text to LaTeX code suitable for BibTeX.
         *
         * @since    0.1.0
         * @access   public
         * @param    string     $text    Text with special characters that are to be converted to a LaTeX type encoding suitable for the use in BibTeX .bib files.
         */
    static public function utf8_to_bibtex( $text ) {

        $parts = self::preg_split_at_latex_math_mode_delimters($text);
        $bibtex = '';
        foreach ($parts as $x => $part) {
            if ($x % 2 === 1) {//In math mode
                $bibtex .= '{$' . $part . '$}';
            } else { //Outside math mode
                $part = preg_replace('/([A-Z]{2,}|(?<!^)[A-Z]+)/', '{'."$1".'}', $part);
                $bibtex .= self::utf8_to_latex($part);
            }
        }

        return $bibtex;
    }

        /**
         * Extracts LaTeX command definitions from latex code.
         *
         * Extracts LaTeX command definitions from latex code. The definitions are of the following form:
         *
         * \newcommand{\myvec}[1]{\vec{#1}}
         *
         * Full match	0-22	`\newcommand{\myvec}[1]`
         * Group 1.	1-11	`newcommand`
         * Group 2.	12-18	`\myvec`
         * Group 3.	19-22	`[1]`
         * Group 4.	22-22	``  (in case a default is set making an argument optional)
         * Group 5.	23-31	`\vec{#1}`
         *
         * @since    0.1.0
         * @access   public
         * @param    string    $latex_source     LaTeX source code from with the macro definitions are to be extracted.
         */
    static public function extract_latex_macros( $latex_source ) {

        $latex_source_without_comments = preg_replace('#(?<!\\\\)%.*#', '', $latex_source);
        preg_match_all('#\\\\(newcommand|providecommand|def)(?:\{| *)(\\\\[@a-zA-Z]+)(?:\}| *)(\[[0-9]\]|)(\[[^]]*\]|)(?=\{((?:[^{}]++|\{(?5)\})*)\})#', $latex_source_without_comments, $latex_macro_definitions, PREG_SET_ORDER);//matches \newcommand and friends and takes into account balanced parenthesis (Note the use of (?5) here!) to test changes go here https://regex101.com/r/g7LCUO/1

        return $latex_macro_definitions;
    }

        /**
         * Additional macros.
         *
         * Array of default macros that we always want to expand,
         * irrespective of whether they appeared in a LaTeX file.
         *
         * @since    0.1.0
         * @access   private
         * @var      array    $additional_default_macros   Array of additional macros that we often want to expand even if they were not explicitely defined by the user.
         */
    static private $additional_default_macros = array(
        array('', '\\newcommand', '\\\@firstoftwo', '[2]', '', '#1'),
        array('', '\\newcommand', '\\\@secondoftwo', '[2]', '', '#2'),
                                                      );
        /**
         * Get special macros we sometimes want to ignore in expansion.
         *
         * Some macros are better kept unexpended even if the authors have
         * manually re-defined them, because we need them to more efficiently
         * parse the latex code (i.e., to identify DOIs or URLs).
         *
         * @since    0.1.0
         * @access   public
         */
    static public function get_special_macros_to_ignore_in_bbl() {

        return array('\\href', '\\doi', '\\url');
    }

        /**
         * Remove special macros we sometimes want to ignore in expansion.
         *
         * @since    0.1.0
         * @access   public
         * @param    array    $latex_macro_definitions    Array of latex macro definitions.
         */
    static public function remove_special_macros_to_ignore_in_bbl( $latex_macro_definitions ) {
        $latex_macro_definitions_without_specials = array();
        $special_macros_to_ignore = O3PO_Latex::get_special_macros_to_ignore_in_bbl();
        foreach($latex_macro_definitions as $latex_macro_definition)
        {
            if(!in_array($latex_macro_definition[2], $special_macros_to_ignore))
            {
                $latex_macro_definitions_without_specials[] = $latex_macro_definition;
            }
        }

        return $latex_macro_definitions_without_specials;
    }

        /**
         * Expand LaTeX macros.
         *
         * Attempts to expands all macros in $text for which a definition is
         * given in $macro_definitions.
         *
         * Expects definitions like those produced by self::extract_latex_macros().
         *
         * @since    0.1.0
         * @access   public
         * @param    array    $macro_definitions   Array of macro definitions.
         * @param    string   $text                Text containing LaTeX macros that are to be expanded.
         */
    static public function expand_latex_macros( $macro_definitions, $text ) {

        if(strpos($text, '\\') === false || empty($macro_definitions))
            return $text;

        $macro_definitions = array_merge_recursive($macro_definitions, static::$additional_default_macros);

        $patterns_and_replacements = array();
        foreach($macro_definitions as $macro_definition)
        {
            if(empty($macro_definition[3]) || preg_match('#[0-9]+#',$macro_definition[3], $num_arguments) !== 1 )
                $num_arguments = 0;
            else
                $num_arguments = $num_arguments[0];

            if(empty($macro_definition[4]) || preg_match('#^\[(.*)\]$#',$macro_definition[4], $default_argument) !== 1 )
                $default_argument = false;
            else
                $default_argument = $default_argument[1];

            $macroname = '\\\\' . substr($macro_definition[2],1);//substr picks out the name of the macro without the leading \;
            $pattern = $macroname;
            $replacement = $macro_definition[5];
            if($num_arguments == 0)
            {
                if(preg_match('#[^a-zA-Z]#', substr($macro_definition[2],1))!==1)
                    $pattern .= '(?=[^a-zA-Z])\s*'; //If macro name is not a special character make sure it is not followed by letters to prevent expanding \e in things like \emph and eat following space characters
            }
            else
                for($i=1; $i <= $num_arguments; $i++)
                {
                    if($i==1 && $default_argument !== false)
                        $pattern .= '\s*(?=\[((?:[^\[\]]++|\[(?' . $i . ')\])*)\]|)\s*(?:\[(?' . $i . ')\]|)';
                    else
                        $pattern .= '\s*(?=\{((?:[^{}]++|\{(?' . $i . ')\})*)\})\s*{(?' . $i . ')}';

                    $replacement = str_replace ( '#' . $i , '\\'.$i ,  $replacement );
                }
            $pattern = '\\\\(?:newcommand|providecommand|def)[\s{]*' . $macroname . '(*SKIP)(*FAIL)|' . $pattern; //prevent expanion in the definition of the macro
            $pattern = '#' . $pattern . '#';
            $replacement = str_replace ('\$', '\\\\\$', $replacement);// espace $ in replacement as it has a special meaning

            $patterns_and_replacements[] = array($pattern,$replacement);

//        echo $macroname . "  :   ".  $pattern . ' => ' . $replacement . "\n\n";
        }

        $runs = 4;
        $new_text = $text;
        while($runs > 0)
        {
            $runs--;
            foreach($patterns_and_replacements as $patterns_and_replacement)
            {
                $new_text = preg_replace($patterns_and_replacement[0], $patterns_and_replacement[1], $new_text);
            }

            $new_text = preg_replace('#\\\\csname\s*(.*)\\\\endcsname#', '\\\\$1', $new_text);

            if($new_text === $text)
                break;
            else
                $text = $new_text;
        }

        return $text;
    }

        /**
         * Get the BibTeX string representation of a numeric month
         *
         * @since    0.1.0
         * @access   public
         * @param    string/int   $month   Number of the month in the range 1-12.
         */
    static public function get_month_string( $month ) {

        $month = intval($month);
        return array(
            1 => 'jan',
            2 => 'feb',
            3 => 'mar',
            4 => 'apr',
            5 => 'may',
            6 => 'jun',
            7 => 'jul',
            8 => 'aug',
            9 => 'sep',
            10 => 'oct',
            11 => 'nov',
            12 => 'dec',
                     )[$month];
    }

        /**
         * Convert utf8 strings to the closes latin letter string.
         *
         * @since    0.1.0
         * @access   public
         * @param    string     $text    Text with utf8 special characters.
         */
    static public function utf8_to_closest_latin_letter_string( $text ) {

        foreach (self::get_utf8_to_ascii_dictionary() as $target => $substitute) {
            if (mb_strlen($text) === strlen($text)) break;
            $text = preg_replace('#'.$target.'#', $substitute, $text);
        }

        return preg_replace('/[^a-zA-Z]/', '', $text);
    }

        /**
         * Compute a string suitable for taking the role of the BibTeX key
         * from a text such as a title.
         *
         * @since    0.1.0
         * @access   public
         * @param    string   $text    Title of an article from which a BibTeX entry key is to be generated.
         * */
    static public function title_to_key_suffix( $text ) {

        $text = trim(O3PO_Utility::remove_stopwords($text));
        $words = preg_split('/( |-|\\$)/', $text);
        $key = '';
        if(!empty($words[0]))
            $key .= $words[0];
        $i = 1;
        while($i < count($words) && strlen($key)+strlen($words[$i])<20)
        {
        if(!empty($words[$i]))
            $key .= $words[$i];
        $i += 1;
        }
        $key = self::utf8_to_closest_latin_letter_string($key);

        return strtolower($key);
    }


}



/**
 * This class provides various dictionaries that O3PO_Latex needs to do its job.
 *
 * @package    O3PO
 * @subpackage O3PO/includes
 * @author     Christian Gogolin <o3po@quantum-journal.org>
 */
class O3PO_Latex_Dictionary_Provider
{
        /**
         * Utility function to construct the $latex_special_chars_dictionary.
         *
         * @since    0.1.0
         * @access   protected
         * @param    string    $char    The non-character symbol characteristic to the respective latex macro.
         */
    static private function match_single_non_character_makro_regexp_fragment( $char ) {

        return '\s*(?(?=\{)\{\s*\\\\?'.$char.'\s*\}|\\\\?'.$char.')';
    }

        /**
         * Utility function to construct the $latex_special_chars_dictionary.
         *
         * @since    0.1.0
         * @access   protected
         * @param    string    $char    The character characteristic to the respective latex macro.
         */
    static private function match_single_character_makro_regexp_fragment( $char ) {

        return '\s*(?(?=\{)\{\s*\\\\?'.$char.'\s*\}|[\s\\\\]'.$char.'(?![a-zA-Z]))';
    }


        /**
         * An associative array of regular expressions that match latex code and utf8 representations of the respective symbol they represent
         *
         * As PHP does not allow the initialization of static variables
         * from static member methods we have to initialize this array to
         * null. You may thus never use this variable directly, instead
         * use the getter method below.
         *
         * @since    0.1.0
         * @access   private
         * @var      array    $latex_special_chars_dictionary    An associative array of regular expressions that match latex code and utf8 representations of the respective symbol they represent
         * */
    static private $latex_special_chars_dictionary = null;

        /**
         * Get the $latex_special_chars_dictionary.
         *
         * We are dealing with a lot of LaTeX code in wich special characters are
         * LaTeX encoded. On our website we want to display them in a pretty way.
         * The following is a (more or less complete) mapping of LaTeX encodigs into
         * utf-8 characters whenever such a character is available. This list must
         * only contain latex macros that contain a \ as for preformance reasons
         * replacing is stoped once all \ have been eliminated. Also there is a
         * negative look ahead added in the function below to prevent cases such as
         * \\v{a} to be replaced.
         *
         * @since    0.1.0
         * @access   public
         */
    static public function get_latex_special_chars_dictionary() {
        if(self::$latex_special_chars_dictionary===null)
            self::$latex_special_chars_dictionary = array(
                    /* '\\\\\\\\' => "\n", better ignore these as, depending on context, they should be replaced by a newline, a whitespace, or by nothing */
                    /* '\\\\linebreak(?![a-zA-Z])' => "\n", */
                '\\\\ifmmode.*?\\\\else[ {}\n\r]*(.*?)\\\\fi[ {}]*' => '$1',
                '\\\\spacefactor[0-9]*' => '',
                '\\\\relax(?![a-zA-Z])' => '',
                '\\\\space(?![a-zA-Z])' => ' ',
                '\\\\bibnamedelim[abcdi](?![a-zA-Z])' => ' ',
                '\\\\textemdash(?![a-zA-Z])' => '—',
                '\\\\textendash(?![a-zA-Z])' => '–',
                '\\\\&' => '&',
                '\\\\ss(\s*\{\s*\}|\s+|(?![a-zA-Z]))' => 'ß',
                '\\\\L(\s*\{\s*\}|\s+|(?![a-zA-Z]))' => 'Ł',
                '\\\\l(\s*\{\s*\}|\s+|(?![a-zA-Z]))' => 'ł',
                '\\\\o(\s*\{\s*\}|\s+|(?![a-zA-Z]))' => 'ø',
                '\\\\O(\s*\{\s*\}|\s+|(?![a-zA-Z]))' => 'Ø',
                '\\\\aa(\s*\{\s*\}|\s+|(?![a-zA-Z]))' => 'å',
                '\\\\AA(\s*\{\s*\}|\s+|(?![a-zA-Z]))' => 'Å',
                '\\\\"'.self::match_single_non_character_makro_regexp_fragment('A') => 'Ä',
                '\\\\"'.self::match_single_non_character_makro_regexp_fragment('E') => 'Ë',
                '\\\\"'.self::match_single_non_character_makro_regexp_fragment('I') => 'Ï',
                '\\\\"'.self::match_single_non_character_makro_regexp_fragment('O') => 'Ö',
                '\\\\"'.self::match_single_non_character_makro_regexp_fragment('U') => 'Ü',
                '\\\\"'.self::match_single_non_character_makro_regexp_fragment('Y') => 'Ÿ',
                '\\\\"'.self::match_single_non_character_makro_regexp_fragment('a') => 'ä',
                '\\\\"'.self::match_single_non_character_makro_regexp_fragment('e') => 'ë',
                '\\\\"'.self::match_single_non_character_makro_regexp_fragment('i') => 'ï',
                '\\\\"'.self::match_single_non_character_makro_regexp_fragment('o') => 'ö',
                '\\\\"'.self::match_single_non_character_makro_regexp_fragment('u') => 'ü',
                '\\\\"'.self::match_single_non_character_makro_regexp_fragment('y') => 'ÿ',
                '\\\\='.self::match_single_non_character_makro_regexp_fragment('A') => 'Ā',
                '\\\\='.self::match_single_non_character_makro_regexp_fragment('E') => 'Ē',
                '\\\\='.self::match_single_non_character_makro_regexp_fragment('I') => 'Ī',
                '\\\\='.self::match_single_non_character_makro_regexp_fragment('O') => 'Ō',
                '\\\\='.self::match_single_non_character_makro_regexp_fragment('U') => 'Ū',
                '\\\\='.self::match_single_non_character_makro_regexp_fragment('a') => 'ā',
                '\\\\='.self::match_single_non_character_makro_regexp_fragment('e') => 'ē',
                '\\\\='.self::match_single_non_character_makro_regexp_fragment('o') => 'ō',
                '\\\\='.self::match_single_non_character_makro_regexp_fragment('u') => 'ū',
                '\\\\\''.self::match_single_non_character_makro_regexp_fragment('A') => 'Á',
                '\\\\\''.self::match_single_non_character_makro_regexp_fragment('C') => 'Ć ',
                '\\\\\''.self::match_single_non_character_makro_regexp_fragment('E') => 'É',
                '\\\\\''.self::match_single_non_character_makro_regexp_fragment('I') => 'Í',
                '\\\\\''.self::match_single_non_character_makro_regexp_fragment('L') => 'Ĺ',
                '\\\\\''.self::match_single_non_character_makro_regexp_fragment('N') => 'Ń',
                '\\\\\''.self::match_single_non_character_makro_regexp_fragment('O') => 'Ó',
                '\\\\\''.self::match_single_non_character_makro_regexp_fragment('R') => 'Ŕ',
                '\\\\\''.self::match_single_non_character_makro_regexp_fragment('S') => 'Ś',
                '\\\\\''.self::match_single_non_character_makro_regexp_fragment('U') => 'Ú',
                '\\\\\''.self::match_single_non_character_makro_regexp_fragment('Y') => 'Ý',
                '\\\\\''.self::match_single_non_character_makro_regexp_fragment('Z') => 'Ź',
                '\\\\\''.self::match_single_non_character_makro_regexp_fragment('a') => 'á',
                '\\\\\''.self::match_single_non_character_makro_regexp_fragment('c') => 'ć',
                '\\\\\''.self::match_single_non_character_makro_regexp_fragment('e') => 'é',
                '\\\\\''.self::match_single_non_character_makro_regexp_fragment('g') => 'ǵ',
                '\\\\\''.self::match_single_non_character_makro_regexp_fragment('i') => 'í',
                '\\\\\''.self::match_single_non_character_makro_regexp_fragment('l') => 'ĺ',
                '\\\\\''.self::match_single_non_character_makro_regexp_fragment('n') => 'ń',
                '\\\\\''.self::match_single_non_character_makro_regexp_fragment('o') => 'ó',
                '\\\\\''.self::match_single_non_character_makro_regexp_fragment('r') => 'ŕ',
                '\\\\\''.self::match_single_non_character_makro_regexp_fragment('s') => 'ś',
                '\\\\\''.self::match_single_non_character_makro_regexp_fragment('u') => 'ú',
                '\\\\\''.self::match_single_non_character_makro_regexp_fragment('y') => 'ý',
                '\\\\\''.self::match_single_non_character_makro_regexp_fragment('z') => 'ź',
                '\\\\\.'.self::match_single_non_character_makro_regexp_fragment('C') => 'Ċ',
                '\\\\\.'.self::match_single_non_character_makro_regexp_fragment('E') => 'Ė',
                '\\\\\.'.self::match_single_non_character_makro_regexp_fragment('G') => 'Ġ',
                '\\\\\.'.self::match_single_non_character_makro_regexp_fragment('I') => 'İ',
                '\\\\\.'.self::match_single_non_character_makro_regexp_fragment('Z') => 'Ż',
                '\\\\\.'.self::match_single_non_character_makro_regexp_fragment('c') => 'ċ',
                '\\\\\.'.self::match_single_non_character_makro_regexp_fragment('e') => 'ė',
                '\\\\\.'.self::match_single_non_character_makro_regexp_fragment('g') => 'ġ',
                '\\\\\.'.self::match_single_non_character_makro_regexp_fragment('z') => 'ż',
                '\\\\\^'.self::match_single_non_character_makro_regexp_fragment('A') => 'Â',
                '\\\\\^'.self::match_single_non_character_makro_regexp_fragment('C') => 'Ĉ',
                '\\\\\^'.self::match_single_non_character_makro_regexp_fragment('E') => 'Ê',
                '\\\\\^'.self::match_single_non_character_makro_regexp_fragment('G') => 'Ĝ',
                '\\\\\^'.self::match_single_non_character_makro_regexp_fragment('H') => 'Ĥ',
                '\\\\\^'.self::match_single_non_character_makro_regexp_fragment('I') => 'Î',
                '\\\\\^'.self::match_single_non_character_makro_regexp_fragment('J') => 'Ĵ',
                '\\\\\^'.self::match_single_non_character_makro_regexp_fragment('O') => 'Ô',
                '\\\\\^'.self::match_single_non_character_makro_regexp_fragment('S') => 'Ŝ',
                '\\\\\^'.self::match_single_non_character_makro_regexp_fragment('U') => 'Û',
                '\\\\\^'.self::match_single_non_character_makro_regexp_fragment('W') => 'Ŵ',
                '\\\\\^'.self::match_single_non_character_makro_regexp_fragment('Y') => 'Ŷ',
                '\\\\\^'.self::match_single_non_character_makro_regexp_fragment('a') => 'â',
                '\\\\\^'.self::match_single_non_character_makro_regexp_fragment('c') => 'ĉ',
                '\\\\\^'.self::match_single_non_character_makro_regexp_fragment('e') => 'ê',
                '\\\\\^'.self::match_single_non_character_makro_regexp_fragment('g') => 'ĝ',
                '\\\\\^'.self::match_single_non_character_makro_regexp_fragment('h') => 'ĥ',
                '\\\\\^'.self::match_single_non_character_makro_regexp_fragment('i') => 'ı̂',
                '\\\\\^'.self::match_single_non_character_makro_regexp_fragment('o') => 'ô',
                '\\\\\^'.self::match_single_non_character_makro_regexp_fragment('s') => 'ŝ',
                '\\\\\^'.self::match_single_non_character_makro_regexp_fragment('u') => 'û',
                '\\\\\^'.self::match_single_non_character_makro_regexp_fragment('w') => 'ŵ',
                '\\\\\^'.self::match_single_non_character_makro_regexp_fragment('y') => 'ŷ',
                '\\\\`'.self::match_single_non_character_makro_regexp_fragment('A') => 'À',
                '\\\\`'.self::match_single_non_character_makro_regexp_fragment('E') => 'È',
                '\\\\`'.self::match_single_non_character_makro_regexp_fragment('I') => 'Ì',
                '\\\\`'.self::match_single_non_character_makro_regexp_fragment('O') => 'Ò',
                '\\\\`'.self::match_single_non_character_makro_regexp_fragment('U') => 'Ù',
                '\\\\`'.self::match_single_non_character_makro_regexp_fragment('a') => 'à',
                '\\\\`'.self::match_single_non_character_makro_regexp_fragment('e') => 'è',
                '\\\\`'.self::match_single_non_character_makro_regexp_fragment('i') => 'ì',
                '\\\\`'.self::match_single_non_character_makro_regexp_fragment('o') => 'ò',
                '\\\\`'.self::match_single_non_character_makro_regexp_fragment('u') => 'ù',
                '\\\\~'.self::match_single_non_character_makro_regexp_fragment('A') => 'Ã',
                '\\\\~'.self::match_single_non_character_makro_regexp_fragment('I') => 'Ĩ',
                '\\\\~'.self::match_single_non_character_makro_regexp_fragment('N') => 'Ñ',
                '\\\\~'.self::match_single_non_character_makro_regexp_fragment('O') => 'Õ',
                '\\\\~'.self::match_single_non_character_makro_regexp_fragment('U') => 'Ũ',
                '\\\\~'.self::match_single_non_character_makro_regexp_fragment('a') => 'ã',
                '\\\\~'.self::match_single_non_character_makro_regexp_fragment('n') => 'ñ',
                '\\\\~'.self::match_single_non_character_makro_regexp_fragment('o') => 'õ',
                '\\\\~'.self::match_single_non_character_makro_regexp_fragment('u') => 'ũ',

                '\\\\c'.self::match_single_character_makro_regexp_fragment('C') => 'Ç',
                '\\\\c'.self::match_single_character_makro_regexp_fragment('G') => 'Ģ',
                '\\\\c'.self::match_single_character_makro_regexp_fragment('K') => 'Ķ',
                '\\\\c'.self::match_single_character_makro_regexp_fragment('L') => 'Ļ',
                '\\\\c'.self::match_single_character_makro_regexp_fragment('N') => 'Ņ',
                '\\\\c'.self::match_single_character_makro_regexp_fragment('R') => 'Ŗ',
                '\\\\c'.self::match_single_character_makro_regexp_fragment('S') => 'Ş',
                '\\\\c'.self::match_single_character_makro_regexp_fragment('T') => 'Ţ',
                '\\\\c'.self::match_single_character_makro_regexp_fragment('c') => 'ç',
                '\\\\c'.self::match_single_character_makro_regexp_fragment('e') => 'ȩ',
                '\\\\c'.self::match_single_character_makro_regexp_fragment('g') => 'ģ',
                '\\\\c'.self::match_single_character_makro_regexp_fragment('k') => 'ķ',
                '\\\\c'.self::match_single_character_makro_regexp_fragment('l') => 'ļ',
                '\\\\c'.self::match_single_character_makro_regexp_fragment('n') => 'ņ',
                '\\\\c'.self::match_single_character_makro_regexp_fragment('r') => 'ŗ',
                '\\\\c'.self::match_single_character_makro_regexp_fragment('s') => 'ş',
                '\\\\c'.self::match_single_character_makro_regexp_fragment('t') => 'ţ',
                '\\\\k'.self::match_single_character_makro_regexp_fragment('A') => 'Ą',
                '\\\\k'.self::match_single_character_makro_regexp_fragment('E') => 'Ę',
                '\\\\k'.self::match_single_character_makro_regexp_fragment('I') => 'Į',
                '\\\\k'.self::match_single_character_makro_regexp_fragment('U') => 'Ų',
                '\\\\k'.self::match_single_character_makro_regexp_fragment('a') => 'ą',
                '\\\\k'.self::match_single_character_makro_regexp_fragment('e') => 'ę',
                '\\\\k'.self::match_single_character_makro_regexp_fragment('i') => 'į',
                '\\\\k'.self::match_single_character_makro_regexp_fragment('u') => 'ų',
                '\\\\r'.self::match_single_character_makro_regexp_fragment('A') => 'Å',
                '\\\\r'.self::match_single_character_makro_regexp_fragment('a') => 'å',
                '\\\\u'.self::match_single_character_makro_regexp_fragment('A') => 'Ă',
                '\\\\u'.self::match_single_character_makro_regexp_fragment('E') => 'Ĕ',
                '\\\\u'.self::match_single_character_makro_regexp_fragment('G') => 'Ğ',
                '\\\\u'.self::match_single_character_makro_regexp_fragment('I') => 'Ĭ',
                '\\\\u'.self::match_single_character_makro_regexp_fragment('O') => 'Ŏ',
                '\\\\u'.self::match_single_character_makro_regexp_fragment('U') => 'Ŭ',
                '\\\\u'.self::match_single_character_makro_regexp_fragment('a') => 'ă',
                '\\\\u'.self::match_single_character_makro_regexp_fragment('c') => 'c̆',
                '\\\\u'.self::match_single_character_makro_regexp_fragment('e') => 'ĕ',
                '\\\\u'.self::match_single_character_makro_regexp_fragment('g') => 'ğ',
                '\\\\u'.self::match_single_character_makro_regexp_fragment('o') => 'ŏ',
                '\\\\u'.self::match_single_character_makro_regexp_fragment('u') => 'ŭ',
                '\\\\v'.self::match_single_character_makro_regexp_fragment('c') => 'č',
                '\\\\v'.self::match_single_character_makro_regexp_fragment('C') => 'Č',
                '\\\\v'.self::match_single_character_makro_regexp_fragment('D') => 'Ď',
                '\\\\v'.self::match_single_character_makro_regexp_fragment('E') => 'Ě',
                '\\\\v'.self::match_single_character_makro_regexp_fragment('L') => 'Ľ',
                '\\\\v'.self::match_single_character_makro_regexp_fragment('N') => 'Ň',
                '\\\\v'.self::match_single_character_makro_regexp_fragment('R') => 'Ř',
                '\\\\v'.self::match_single_character_makro_regexp_fragment('S') => 'Š',
                '\\\\v'.self::match_single_character_makro_regexp_fragment('T') => 'Ť',
                '\\\\v'.self::match_single_character_makro_regexp_fragment('Z') => 'Ž',
                '\\\\v'.self::match_single_character_makro_regexp_fragment('d') => 'ď',
                '\\\\v'.self::match_single_character_makro_regexp_fragment('e') => 'ě',
                '\\\\v'.self::match_single_character_makro_regexp_fragment('l') => 'ľ',
                '\\\\v'.self::match_single_character_makro_regexp_fragment('n') => 'ň',
                '\\\\v'.self::match_single_character_makro_regexp_fragment('r') => 'ř',
                '\\\\v'.self::match_single_character_makro_regexp_fragment('s') => 'š',
                '\\\\v'.self::match_single_character_makro_regexp_fragment('t') => 'ť',
                '\\\\v'.self::match_single_character_makro_regexp_fragment('z') => 'ž',
                '\\\\etalchar{?\+}?' => '⁺',
                                                    );
        return self::$latex_special_chars_dictionary;
    }

        /**
         * LaTeX cleanup dictionary
         *
         * After we have performed the above replacements in the following function
         * we clean up remaining special characters accoding to the following list.
         *
         * @since    0.1.0
         * @access   private
         * @vat      $latex_clean_up_dictionary    Dictionary of LaTeX control sequences and characters and their replacements when converting to utf8 or similar representations.
         * */
    static private $latex_clean_up_dictionary = array(
            //'>' => '\gt ', leave these alone outside of math mode so that they can
            //'<' => '\lt ', later be caught by esc_html() and finalyl printed by MathJax
        '~' => ' ',
        '{' => '',
        '}' => '',
        '---' => '-',
        '--' => '-',
        '\\%' => '%',
        '\\#' => '#',
        '\\_' => '_',
        '\\ ' => ' ',
        "\\\n" => "\n",
        "\\\r" => "\r",
                                                      );

        /**
         * Get the $latex_clean_up_dictionary.
         *
         * @since    0.1.0
         * @access   public
         */
    static public function get_latex_clean_up_dictionary() {

        return self::$latex_clean_up_dictionary;
    }

        /**
         * Associative array of utf8 representations and their respective LaTeX representations.
         *
         * In some cases we need to generate LaTeX code from utf-8 encoded
         * strings containing special characters. This array helps in doing
         * this.
         *
         * @since    0.1.0
         * @access   private
         * @var      $latex_special_chars_reverse_dictionary   Dictionary of special characters and their LaTeX representations.
         */
    static private $latex_special_chars_reverse_dictionary = array(
        'á' => 	'{\\\\\'{a}}',
        'é' => 	'{\\\\\'{e}}',
        'í' => 	'{\\\\\'{i}}',
        'ó' => 	'{\\\\\'{o}}',
        'ú' => 	'{\\\\\'{u}}',
        'ć' => 	'{\\\\\'{c}}',
        'Á' => 	'{\\\\\'{A}}',
        'Ć' => 	'{\\\\\'{C}}',
        'É' => 	'{\\\\\'{E}}',
        'Í' => 	'{\\\\\'{I}}',
        'Ó' => 	'{\\\\\'{O}}',
        'Ú' => 	'{\\\\\'{U}}',
        'ä' => 	'{\\\\"{a}}',
        'ï' => 	'{\\\\"{i}}',
        'ë' => 	'{\\\\"{e}}',
        'ö' => 	'{\\\\"{o}}',
        'ü' => 	'{\\\\"{u}}',
        'â' => 	'{\\\\^{a}}',
        'ĉ' => 	'{\\\\^{c}}',
        'ê' => 	'{\\\\^{e}}',
        'ô' =>  '{\\\\^{o}}',
        'à' => 	'{\\\\`{a}}',
        'è' => 	'{\\\\`{e}}',
        'ò' => 	'{\\\\`{o}}',
        'ù' => 	'{\\\\`{u}}',
        'ì' => 	'{\\\\`{i}}',
        'č' => 	'{\\\\v{c}}',
        'ß' => 	'{\\\\ss{}}',
        'Ł' => 	'{\\\\L{}}',
        'ł' => 	'{\\\\l{}}',
        'ø' => 	'{\\\\o{}}',
        'Ø' => 	'{\\\\O{}}',
        'å' => 	'{\\\\aa{}}',
        'Å' => 	'{\\\\AA{}}',
        'Š' => 	'{\\\\v{S}}',
        'š' => 	'{\\\\v{s}}',
        'Ä' => 	'{\\\\"{A}}',
        'Ë' => 	'{\\\\"{E}}',
        'Ï' => 	'{\\\\"{I}}',
        'Ö' => 	'{\\\\"{O}}',
        'Ü' => 	'{\\\\"{U}}',
        'ç' => 	'{\\\\c{c}}',
        'Ç' => 	'{\\\\c{C}}',
        'ǵ' => 	'{\\\\\'{g}}',
        'ĺ' => 	'{\\\\\'{l}}',
        'ń' => 	'{\\\\\'{n}}',
        'ŕ' => 	'{\\\\\'{r}}',
        'ś' => 	'{\\\\\'{s}}',
        'ý' => 	'{\\\\\'{y}}',
        'ź' => 	'{\\\\\'{z}}',
        'Ĺ' => 	'{\\\\\'{L}}',
        'Ń' => 	'{\\\\\'{N}}',
        'Ŕ' => 	'{\\\\\'{R}}',
        'Ś' => 	'{\\\\\'{S}}',
        'Ý' => 	'{\\\\\'{Y}}',
        'Ź' => 	'{\\\\\'{Z}}',
        'Ċ' => 	'{\\\\.{C}}',
        'Ė' => 	'{\\\\.{E}}',
        'Ġ' => 	'{\\\\.{G}}',
        'İ' => 	'{\\\\.{I}}',
        'Ż' => 	'{\\\\.{Z}}',
        'ċ' => 	'{\\\\.{c}}',
        'ė' => 	'{\\\\.{e}}',
        'ġ' => 	'{\\\\.{g}}',
        'ż' => 	'{\\\\.{z}}',
        'Ā' => 	'{\\\\={A}}',
        'Ē' => 	'{\\\\={E}}',
        'Ī' => 	'{\\\\={I}}',
        'Ō' => 	'{\\\\={O}}',
        'Ū' => 	'{\\\\={U}}',
        'ā' => 	'{\\\\={a}}',
        'ē' => 	'{\\\\={e}}',
        'ō' => 	'{\\\\={o}}',
        'ū' => 	'{\\\\={u}}',
        'Ÿ' => 	'{\\\\"{Y}}',
        'ÿ' => 	'{\\\\"{y}}',
        'Â' => 	'{\\\\^{A}}',
        'Ĉ' => 	'{\\\\^{C}}',
        'Ê' => 	'{\\\\^{E}}',
        'Ĝ' => 	'{\\\\^{G}}',
        'Ĥ' => 	'{\\\\^{H}}',
        'Î' => 	'{\\\\^{I}}',
        'Ĵ' => 	'{\\\\^{J}}',
        'Ô' => 	'{\\\\^{O}}',
        'Ŝ' => 	'{\\\\^{S}}',
        'Û' => 	'{\\\\^{U}}',
        'Ŵ' => 	'{\\\\^{W}}',
        'Ŷ' => 	'{\\\\^{Y}}',
        'ĝ' => 	'{\\\\^{g}}',
        'ĥ' => 	'{\\\\^{h}}',
        'ô' => 	'{\\\\^{o}}',
        'ŝ' => 	'{\\\\^{s}}',
        'û' => 	'{\\\\^{u}}',
        'ŵ' => 	'{\\\\^{w}}',
        'ŷ' => 	'{\\\\^{y}}',
        'À' => 	'{\\\\`{A}}',
        'È' => 	'{\\\\`{E}}',
        'Ì' => 	'{\\\\`{I}}',
        'Ò' => 	'{\\\\`{O}}',
        'Ù' => 	'{\\\\`{U}}',
        'Ą' => 	'{\\\\k{A}}',
        'Ę' => 	'{\\\\k{E}}',
        'Į' => 	'{\\\\k{I}}',
        'Ų' => 	'{\\\\k{U}}',
        'ą' => 	'{\\\\k{a}}',
        'ę' => 	'{\\\\k{e}}',
        'į' => 	'{\\\\k{i}}',
        'ų' => 	'{\\\\k{u}}',
        'Ă' => 	'{\\\\u{A}}',
        'Ĕ' => 	'{\\\\u{E}}',
        'Ğ' => 	'{\\\\u{G}}',
        'Ĭ' => 	'{\\\\u{I}}',
        'Ŏ' => 	'{\\\\u{O}}',
        'Ŭ' => 	'{\\\\u{U}}',
        'ă' => 	'{\\\\u{a}}',
        'ĕ' => 	'{\\\\u{e}}',
        'ğ' => 	'{\\\\u{g}}',
        'ŏ' => 	'{\\\\u{o}}',
        'ŭ' => 	'{\\\\u{u}}',
        'Č' => 	'{\\\\v{C}}',
        'Ď' => 	'{\\\\v{D}}',
        'Ě' => 	'{\\\\v{E}}',
        'Ľ' => 	'{\\\\v{L}}',
        'Ň' => 	'{\\\\v{N}}',
        'Ř' => 	'{\\\\v{R}}',
        'Ť' => 	'{\\\\v{T}}',
        'Ž' => 	'{\\\\v{Z}}',
        'ď' => 	'{\\\\v{d}}',
        'ě' => 	'{\\\\v{e}}',
        'ľ' => 	'{\\\\v{l}}',
        'ň' => 	'{\\\\v{n}}',
        'ř' => 	'{\\\\v{r}}',
        'ť' => 	'{\\\\v{t}}',
        'ž' => 	'{\\\\v{z}}',
        'Ã' => 	'{\\\\~{A}}',
        'Ĩ' => 	'{\\\\~{I}}',
        'Ñ' => 	'{\\\\~{N}}',
        'Õ' => 	'{\\\\~{O}}',
        'Ũ' => 	'{\\\\~{U}}',
        'ã' => 	'{\\\\~{a}}',
        'ñ' => 	'{\\\\~{n}}',
        'õ' => 	'{\\\\~{o}}',
        'ũ' => 	'{\\\\~{u}}',
        'ţ' => 	'{\\\\c{t}}',
        'ş' => 	'{\\\\c{s}}',
        'ŗ' => 	'{\\\\c{r}}',
        'ņ' => 	'{\\\\c{n}}',
        'ļ' => 	'{\\\\c{l}}',
        'ķ' => 	'{\\\\c{k}}',
        'ģ' => 	'{\\\\c{g}}',
        'Ţ' => 	'{\\\\c{T}}',
        'Ş' => 	'{\\\\c{S}}',
        'Ŗ' => 	'{\\\\c{R}}',
        'Ņ' => 	'{\\\\c{N}}',
        'Ļ' => 	'{\\\\c{L}}',
        'Ķ' => 	'{\\\\c{K}}',
        'Ģ' => 	'{\\\\c{G}}',
                                                                   );

        /**
         * Get the $latex_special_chars_reverse_dictionary.
         *
         * @since     0.1.0
         * @access    public
         */
    static public function get_latex_special_chars_reverse_dictionary() {

        return self::$latex_special_chars_reverse_dictionary;
    }


        /**
         * Associative array of utf8 representations and their closest ascii characters.
         *
         * In some cases we need to generate ascii strings from utf-8 encoded
         * strings containing special characters. This array helps in doing
         * this.
         *
         * @since    0.1.0
         * @access   private
         * @var      $utf8_to_ascii_dictionary    Dictionary of special character to closes ascii versions.
         */
    static private $utf8_to_ascii_dictionary = array(
        'á' => 	'a',
        'é' => 	'e',
        'í' => 	'i',
        'ó' => 	'o',
        'ú' => 	'u',
        'ć' => 	'c',
        'Á' => 	'A',
        'Ć' => 	'C',
        'É' => 	'E',
        'Í' => 	'I',
        'Ó' => 	'O',
        'Ú' => 	'U',
        'ä' => 	'a',
        'ï' => 	'i',
        'ë' => 	'e',
        'ö' => 	'o',
        'ü' => 	'u',
        'â' => 	'a',
        'ĉ' => 	'c',
        'ê' => 	'e',
        'à' => 	'a',
        'è' => 	'e',
        'ò' => 	'o',
        'ù' => 	'u',
        'ì' => 	'i',
        'č' => 	'c',
        'ß' => 	'ss',
        'Ł' => 	'L',
        'ł' => 	'l',
        'ø' => 	'o',
        'Ø' => 	'O',
        'å' => 	'a',
        'Å' => 	'A',
        'Š' => 	'S',
        'š' => 	's',
        'Ä' => 	'A',
        'Ë' => 	'E',
        'Ï' => 	'I',
        'Ö' => 	'O',
        'Ü' => 	'U',
        'ç' => 	'c',
        'Ç' => 	'C',
        'ǵ' => 	'g',
        'ĺ' => 	'l',
        'ń' => 	'n',
        'ŕ' => 	'r',
        'ś' => 	's',
        'ý' => 	'y',
        'ź' => 	'z',
        'Ĺ' => 	'L',
        'Ń' => 	'N',
        'Ŕ' => 	'R',
        'Ś' => 	'S',
        'Ý' => 	'Y',
        'Ź' => 	'Z',
        'Ċ' => 	'C',
        'Ė' => 	'E',
        'Ġ' => 	'G',
        'İ' => 	'I',
        'Ż' => 	'Z',
        'ċ' => 	'c',
        'ė' => 	'e',
        'ġ' => 	'g',
        'ż' => 	'z',
        'Ā' => 	'A',
        'Ē' => 	'E',
        'Ī' => 	'I',
        'Ō' => 	'O',
        'Ū' => 	'U',
        'ā' => 	'a',
        'ē' => 	'e',
        'ō' => 	'o',
        'ū' => 	'u',
        'Ÿ' => 	'Y',
        'ÿ' => 	'y',
        'Â' => 	'A',
        'Ĉ' => 	'C',
        'Ê' => 	'E',
        'Ĝ' => 	'G',
        'Ĥ' => 	'H',
        'Î' => 	'I',
        'Ĵ' => 	'J',
        'Ô' => 	'O',
        'Ŝ' => 	'S',
        'Û' => 	'U',
        'Ŵ' => 	'W',
        'Ŷ' => 	'Y',
        'ĝ' => 	'g',
        'ĥ' => 	'h',
        'ô' => 	'o',
        'ŝ' => 	's',
        'û' => 	'u',
        'ŵ' => 	'w',
        'ŷ' => 	'y',
        'À' => 	'A',
        'È' => 	'E',
        'Ì' => 	'I',
        'Ò' => 	'O',
        'Ù' => 	'U',
        'Ą' => 	'A',
        'Ę' => 	'E',
        'Į' => 	'I',
        'Ų' => 	'U',
        'ą' => 	'a',
        'ę' => 	'e',
        'į' => 	'i',
        'ų' => 	'u',
        'Ă' => 	'A',
        'Ĕ' => 	'E',
        'Ğ' => 	'G',
        'Ĭ' => 	'I',
        'Ŏ' => 	'O',
        'Ŭ' => 	'U',
        'ă' => 	'a',
        'ĕ' => 	'e',
        'ğ' => 	'g',
        'ŏ' => 	'o',
        'ŭ' => 	'u',
        'Č' => 	'C',
        'Ď' => 	'D',
        'Ě' => 	'E',
        'Ľ' => 	'L',
        'Ň' => 	'N',
        'Ř' => 	'R',
        'Ť' => 	'T',
        'Ž' => 	'Z',
        'ď' => 	'd',
        'ě' => 	'e',
        'ľ' => 	'l',
        'ň' => 	'n',
        'ř' => 	'r',
        'ť' => 	't',
        'ž' => 	'z',
        'Ã' => 	'A',
        'Ĩ' => 	'I',
        'Ñ' => 	'N',
        'Õ' => 	'O',
        'Ũ' => 	'U',
        'ã' => 	'a',
        'ñ' => 	'n',
        'õ' => 	'o',
        'ũ' => 	'u',
        'ţ' => 	't',
        'ş' => 	's',
        'ŗ' => 	'r',
        'ņ' => 	'n',
        'ļ' => 	'l',
        'ķ' => 	'k',
        'ģ' => 	'g',
        'Ţ' => 	'T',
        'Ş' => 	'S',
        'Ŗ' => 	'R',
        'Ņ' => 	'N',
        'Ļ' => 	'L',
        'Ķ' => 	'K',
        'Ģ' => 	'G',
                                                     );

        /**
         * Get the $utf8_to_ascii_dictionary.
         *
         * @sine     0.1.0
         * @access   public
         */
    static public function get_utf8_to_ascii_dictionary() {

        return self::$utf8_to_ascii_dictionary;
    }

        /**
         * Expand \cite{} commands to html code.
         *
         * The resulting html code makes every \cite a clickable hyperlink to the
         * correspnding entry in the bibliograhy.
         *
         * @since    0.1.0
         * @access   public
         * @param    string    $text    The text in which \cite commands are to be expanded
         * @param    string    $bbl     The bbl code of the bibliography that contains the corresponding bibliography entries.
         */
    public static function expand_cite_to_html( $text, $bbl ) {

        preg_match_all('#\\\\cite\{\s*([^}]*)\s*\}#', $text, $refs, PREG_PATTERN_ORDER);
        if(empty($refs) or empty($refs[1]))
            return $text;

        $parsed_bbl = static::parse_bbl($bbl);
        $bibtex_key_dict = array();
        foreach($parsed_bbl as $n => $entry) {
            $bibtex_key_dict[$entry['key']] = $n;
        }

        foreach($refs[1] as $x => $ref)
        {
            $replacement = '[';
            foreach(preg_split('#\s*,\s*#', $ref) as $bibtex_key) {
                $replacement .= '<a onclick="document.getElementById(\'references\').style.display=\'block\';" href="#' . $bibtex_key . '">' . ( isset($bibtex_key_dict[$bibtex_key]) ? $bibtex_key_dict[$bibtex_key] : '?' )  . '</a>,';
            }
            $replacement = rtrim($replacement,',');
            $replacement .= ']';

            $text = preg_replace('#\\' . $refs[0][$x] . '#', $replacement, $text);
        }

        return $text;
    }

        /**
         * Normalized white space and line break characters.
         *
         * @since    0.3.0
         * @access   public
         * @param    string   $text          LaTeX text to normalize.
         * @param    boolean  $single_line   Whether to output a single-line text.
         * @param    boolean  $remove_extra_newlines If true, single newlines are replaced by space and any number of more than two successive newlines are replaced by exactly two newlines.
         */
    static public function normalize_whitespace_and_linebreak_characters( $text, $single_line=true, $remove_extra_newlines=false) {
        if($remove_extra_newlines)
            $text = preg_replace('#(?<!\n)\n(?!\n)#', ' ', $text);

        foreach(array(
                    '\\\\\\\\(\s*|\s*\[.*?\])' => "\n",
                    '\\\\linebreak(?![a-zA-Z])\h*' => "\n",
                    '\\\\(newline|hfill|break)(?![a-zA-Z])\h*'  => "\n",
                    '\\\\(hspace|vspace)\s*{[^}]*?}\h*' => " ",
                    '\\\\(smallskip|medskip|bigskip)(?![a-zA-Z])\h*' => " ",
                      ) as $target => $replacement )
            $text = preg_replace('#' . $target . '#', $replacement, $text);

        if($single_line)
            $text = str_replace("\n", ' ', $text);

        $text = str_replace("\t", ' ', $text);
        $text = join("\n", array_map("trim", explode("\n", $text)));
        $text = trim(preg_replace('#\h\h+#', ' ', $text));

        if($remove_extra_newlines)
            $text = preg_replace('#\n\n\n+#', "\n\n", $text);


        return $text;
    }


        /**
         * Extract all bibliographies from latex code.
         *
         * @since   0.3.0
         * @access  public
         * @param   string    $latex   Latex code to search for bibliographies.
         *
         */
    static public function extract_bibliographies( $latex ) {

        $bbl = '';

        preg_match_all('/(\\\\begin{thebibliography}.*?\\\\end{thebibliography}|\\\\begin{references}.*?\\\\end{references})/s', $latex, $matches, PREG_PATTERN_ORDER);
        if(!empty($matches[0])) {
            $i = 0;
            while(isset($matches[0][$i]))
            {
                $bbl .= $matches[0][$i] . "\n";
                $i++;
            }
            return $bbl;
        }
        else
            return '';
    }

        /**
         * Extract all abstracts from latex code.
         *
         * @since   0.3.0
         * @access  public
         * @param   string    $latex   Latex code to search for abstracts.
         *
         */
    static public function extract_abstracts( $latex ) {

        $abstract = '';

        preg_match_all('/\\\\begin{abstract}(.*?)\\\\end{abstract}/s', $latex, $matches, PREG_PATTERN_ORDER);
        if(!empty($matches[1])) {
            $i = 0;
            while(isset($matches[1][$i]))
            {
                $abstract .= $matches[1][$i] . "\n";
                $i++;
            }
            return $abstract;
        }
        else
            return '';
    }

}
