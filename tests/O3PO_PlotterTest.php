<?php

require_once dirname( __FILE__ ) . '/../o3po/includes/class-o3po-plotter.php';

class O3PO_PlotterTest extends O3PO_TestCase
{

    public function test_histogram() {

        $data = [1,2,3,4,5,5,7,45,12,12,12,12,12,12,12,12,12,12,23,22];

        $plotter = new O3PO_plotter();
        $output = $plotter->histogram($data, 1, 3, 5, "400pt", "300pt", "X axis", "Y axis", "#ffaa33", "Caption", "reference");
        $this->assertValidHTMLFragment($output);
    }

}
