<?php

/**
 * Collection of functions for visualization of data by means of relatively simple html.
 *
 * @link       https://quantum-journal.org/o3po/
 * @since      0.3.0
 *
 * @package    O3PO
 * @subpackage O3PO/includes
 */

require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-o3po-settings.php';

/**
 * Collection of functions for visualization of data by means of relatively simple html.
 *
 * Provides in particular a function to plot histograms.
 *
 * @package    O3PO
 * @subpackage O3PO/includes
 * @author     Christian Gogolin <o3po@quantum-journal.org>
 */
class O3PO_Plotter {

        /**
         * Fetch meta-data from the abstract page of an eprint on the arXiv.
         *
         * extracts the abstract, number_authors, author_given_names,
         * author_surnames and title
         *
         * @since  0.3.0
         * @access public
         * @param  array $data       Array of data to be displayed as a histogram. Can be a dictionary, in which case the values will be interpreted as the data.
         * @param  float $x_delta    The width of the bins (in x direction) of the histogram.
         * @param  int   $max_x_labels    The maximum number of labels on the x axis.
         * @param  int   $max_y_labels    The maximum number of labels on the y axis.
         * @param  string $max_width HTML length for the maximum width, below that takes 100% of the available space
         * @param  string $height    HTML length for the height of the plot
         * @param  string $x_label   The label of the x axis
         * @param  string $y_label   The label of the y axis
         * @param  string $color     The color of of the bars in HTML notation (defaults to '#000000').
         * @param  string $caption   The caption of the plot (optional)
         * @param  string $ref       The reference key of this plot
         * @return string            The generated html code of the histogram
         */
    public function histogram($data, $x_delta, $max_x_labels, $max_y_labels, $max_width, $height, $x_label=null, $y_label=null, $color="#000000", $caption=null, $ref=null)
    {
        $frame_color = "gray";

        $max_val_in_data = 0;
        foreach($data as $title => $t)
        {
            if($t > $max_val_in_data)
                $max_val_in_data = $t;
        }
        $x_max = floor($max_val_in_data/$x_delta+1)*$x_delta;

        $hist_data = array_fill(0,floor($x_max/$x_delta)+1,0);
        foreach($data as $title => $t)
        {
            $hist_data[floor($t/$x_delta)] += 1;
        }
        $y_max = max($hist_data);

        $y_delta = 1;
        $y_delta_step = 0;
        while($y_max/$y_delta > $max_y_labels) {
            $y_delta = (($y_delta_step % 3)**2 + 1) * 10**floor($y_delta_step/3); #taken from http://oeis.org/A051109
            $y_delta_step += 1;
        }
        $x_label_step = 1;
        while($x_max/($x_delta*$x_label_step) > $max_x_labels) {
            $x_label_step += 1;
        }


        $output = "";
        $output .= '<div style="position:relative;display:inline-block;vertical-align:top;width:100%;max-width:' . $max_width . ';"><div style="display:block;margin-bottom:3em;margin-left:1em;"><div style="position:relative;height:' . $height . ';outline:0.2ch solid '.$frame_color.';margin-left:6ch;margin-bottom:2em;margin-right:2ch;margin-top:0.5em">';
        $output .=  '<span style="display:inline-block;width:0;height:100%;"></span>';//make following bars start at the bottom;

        foreach($hist_data as $x => $y)
        {
            $output .= '<span title="'.$y.' times ' . ( $x_delta == 1 ? $x : ($x*$x_delta) .' to ' . (($x+1)*$x_delta-1)) . '" style="display:inline-block;width:'.(100*$x_delta/$x_max).'%;height:'.($y/$y_max*100).'%;box-shadow:0px 0px 0px 1px '.$color.' inset;background-color:'.$color.';"   onMouseOver="this.style.opacity=\'0.7\'" onMouseOut="this.style.opacity=\'1\'"></span>';
        }

        #axes
        for($y=0; $y <= $y_max; $y+=$y_delta)
        {
            $output .=  '<div style="position:absolute;left:-1ch;width:1ch;height:0.2ch;top:'.(100-$y/$y_max*100).'%;background-color:'.$frame_color.';box-shadow:0px 0px 0px 1px '.$frame_color.' inset;"><div style="color:'.$frame_color.';position:absolute;left:-6ch;width:5ch;bottom:-0.5em;text-align:right">'.round($y,2).'</div></div>';
        }
        $num_x_label = 0;
        for($x=0; $x <= $x_max; $x+=$x_delta)
        {
            $output .=  '<div style="position:absolute;bottom:-1ch;width:0.2ch;height:1ch;right:'.(100-$x/$x_max*100).'%;background-color:'.$frame_color.';box-shadow:0px 0px 0px 1px '.$frame_color.' inset;"><div style="color:'.$frame_color.';position:absolute;bottom:-1.5em;width:6ch;left:-3ch;text-align:center">' . ($num_x_label % $x_label_step == 0 ? round($x,2) : '') . '</div></div>';
            $num_x_label += 1;
        }
        #axes labels
         if(!empty($x_label))
            $output .= '<div style="position:absolute;right:50%;bottom:-2em;"><div style="position:absolute;left:-13em;width:26em;text-align:center;">'.$x_label.'</div></div>';
         if(!empty($y_label))
             $output .= '<div style="position:absolute;top:50%;left:-7ch;"><div style="position:absolute;left:-8em;text-align:center;width:16em;-moz-transform: rotate(-90deg);-o-transform: rotate(-90deg);-webkit-transform: rotate(-90deg);transform: rotate(-90deg);">'.$y_label.'</div></div>';
        $output .=  '</div></div>' . "\n";
        if(!empty($caption))
            $output .= static::caption("figure", $caption, $ref);
        $output .= '</div>';

        return $output;
    }


        /**
         * Array of tags of referencable captions.
         *
         * @since 0.3.0
         * @access private
         * @var $caption_refs Array of tags of referencable captions.
         */
    private $caption_refs;


        /**
         * Array of the most recent referencable caption by caption type.
         *
         * @since 0.3.0
         * @access private
         * @var $last_num Array of the most recent referencable caption by caption type.
         */
    private $last_num;

        /**
         * Plot caption
         *
         * Returns HTML code suitable for captions of plots
         * produced with this class.
         *
         * @since 0.3.0
         * @access public
         * @param  string      The type of element/plot that is captioned
         * @param  string      The text of the caption
         * @param  string      The key of the caption through which it can be referenced
         * @return string      HTML code of the caption
         */
    public function caption( $type, $text, $ref=null ) {

        $output = "";
        if(!isset($this->caption_refs[$type]))
        {
            $this->caption_refs[$type] = array();
        }

        if(!isset($this->last_num[$type]))
            $this->last_num[$type] = 0;

        $id = $this->last_num[$type]+1;

        if(!empty($ref))
            $this->caption_refs[$type][$ref] = $id;

        $output .= "<div style=\"display: flex;\"><span id=\"" . $type . $id . "\" style=\"transform:translateY(-50vh);\"></span><div style=\"flex-grow: 1;width: 0;margin-bottom:1em;margin-top:0.5em;text-align:left\"><strong>".ucfirst($type)." ".($this->last_num[$type]+1).":</strong> ".$text."</div></div>";

        $output .= "\n";
        $this->last_num[$type] +=1;

        return $output;
    }

}
