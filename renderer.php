<?php
// This file is part of the studentgrades grade report
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * report_studentgrades renderer
 *
 * @package    report_studentgrades 
 * @copyright  2017 Sonsbeekmedia, bas@sonsbeekmedia.nl
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Bas Brands
 */

defined('MOODLE_INTERNAL') || die();

class report_studentgrades_renderer extends plugin_renderer_base {

    public function zscore($zscore) {

        $colorarray = $this->z_color();

        // Real z-scores can range from -3 to +3. For this
        // Report we will only be showing -1.5 to + 1.5

        // Scalemax = the range from -1.5 to 1.5 ( =3 )
        $scalemax = 3;
        $zscoremax = 1.5; // position = 100%
        $zscoremin = -1.5; // posistion = 0%

        // If the Z-score is over or under the min max we simply show the line at it's max / min
        if ($zscore < $zscoremin) {
            $zposition = 0;
            $zcolor = $colorarray[0];
        } else if ($zscore > $zscoremax) {
            $zposition = 100;
            $zcolor = end($colorarray);
        } else {
            
            // First we calculate the position of the line in the graph.
            // zscore = 0 should position it in the middle .
            // First add ($zscoremax) to get a range from 0 to $scalemax.
            // Then we multiply it with 1/$scalemax * 100 to get a percentage.
            $zposition = ($zscore + $zscoremax) * (( 1 / $scalemax) * 100);

            // Same as the above. Now not calculating the percentage but the position in the colour array.
            $zcolor = ($zscore + $zscoremax) * (( 1 / $scalemax) * count($colorarray));
            $zcolor = $colorarray[round($zcolor)];
        }

        // The zbar is the container class for the graph.
        // In it's center the calculated zscore is shown.
        // The baseline contains the blue x-axis for this graph.
        $chart = '<div class="zbar">
                  <div class="zscorenr">
                  '. round($zscore, 2) .'
                  </div>
                  <div class="baseline">
                  </div>';
        
        // The gridlines are a visual aid to see where the gauge is pointing at.
        // We display a line starting at -1.5 (or zscoremin) until 1.5 (or zscoremax). That means we will have 7 lines
        $chart .= '<div class="gridlines">';
        $style = 'style = "width :' . 100 / (($scalemax * 2) + 1 ) . '%;"';
        foreach (range(0, ($scalemax * 2 )) as $number) {
            $chart .=  '<div class="scale gridline" '.$style.'>|</div>';
        }
        $chart .= '</div>';

        // The gridscale shows the actual score numbers. It only shows round numbers, halves are replaced with
        // a non-breaking-space.
        $chart .= '<div class="gridscale">';
        foreach (range(0, ($scalemax * 2 )) as $number) {
            if ($number - 3 == 0) {
                $number = 0;
            } else {
                $number = ($number - 3) / 2;
                // Do not display decimal numbers
                if (floor( $number ) != $number) {
                    $number = '&nbsp;';
                }
            }
            $chart .=  '<div class="scale number" '.$style.'>' . $number . '</div>';
        }
        $chart .= '</div>';


        // Next we need to position the Gauge needle.
        // It will only show when the score is between -1.5 (or zscoremin) and 1.5 (or zscoremax)
        // The gridzscore is the container for this element.
        // The zscore position has already been calculated.
        $scorestyle = 'style = "width: ' . (100 - (100 / ($scalemax * 2 ) + 1)) . '%; left: '. ((100 / ($scalemax *2 ) + 1) / 2) .'%"';
        $chart .= '<div class="gridzscore" '.$scorestyle.'>';
        $chart .= '<div class="zscore" style="left: '.$zposition.'%; color: '.$zcolor.' ; background-color: '.$zcolor.';">|</div>';
        $chart .= '</div>';

        $chart .= '</div>';
        return $chart;
    }

    public function z_color() {
        $zcolor = array(
            'FF0000', //<-- red
            '#FF1100',
            '#FF2200',
            '#FF3300',
            '#FF4400',
            '#FF5500',
            '#FF6600',
            '#FF7700',
            '#FF8800',
            '#FF9900',
            '#FFAA00',
            '#FFBB00',
            '#FFCC00',
            '#FFDD00',
            '#FFEE00',
            '#FFFF00', //<-- yellow
            '#EEFF00',
            '#DDFF00',
            '#CCFF00',
            '#BBFF00',
            '#AAFF00',
            '#99FF00',
            '#88FF00',
            '#77FF00',
            '#66FF00',
            '#55FF00',
            '#44FF00',
            '#33FF00',
            '#22FF00',
            '#11FF00',
            '#00FF00', //<-- green
        );
        return $zcolor;
    }

    public function heading($headingtext) {
        $blockheader = '<div class="block block_fake">
                            <div class="header">
                                <div class="title">
                                    <h2>' . $headingtext . '</h2>
                                </div>
                            </div>';
        return $blockheader;
    }

    public function content($contenttext) {
        $content = '<div class="content">' . $contenttext . '</div>
                </div>';
        return $content;
    }
}
