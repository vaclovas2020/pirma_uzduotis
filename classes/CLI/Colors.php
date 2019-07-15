<?php


namespace CLI;


class Colors
{
    private $foreground_colors = array();

    public function __construct()
    {
        $this->foreground_colors['black'] = '0;30';
        $this->foreground_colors['dark_gray'] = '1;30';
        $this->foreground_colors['blue'] = '0;34';
        $this->foreground_colors['light_blue'] = '1;34';
        $this->foreground_colors['green'] = '0;32';
        $this->foreground_colors['light_green'] = '1;32';
        $this->foreground_colors['cyan'] = '0;36';
        $this->foreground_colors['light_cyan'] = '1;36';
        $this->foreground_colors['red'] = '0;31';
        $this->foreground_colors['light_red'] = '1;31';
        $this->foreground_colors['purple'] = '0;35';
        $this->foreground_colors['light_purple'] = '1;35';
        $this->foreground_colors['brown'] = '0;33';
        $this->foreground_colors['yellow'] = '1;33';
        $this->foreground_colors['light_gray'] = '0;37';
        $this->foreground_colors['white'] = '1;37';
    }

    public function getColoredString($string, $color_str = 'white')
    {
        $colored_string = "";
        if (isset($this->foreground_colors[$color_str])) {
            $colored_string .= "\033[" . $this->foreground_colors[$color_str] . "m";
        }
        $colored_string .= $string . "\033[0m";

        return $colored_string;
    }

}