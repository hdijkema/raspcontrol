<?php

namespace lib;

class Temp {

    /**
     * The number of line which will be shown in the popover
     */
    public static $DETAIL_LINE_COUNT = 5;

    public static function temp() {
        $result = array();

        #$fh = popen('vcgencmd measure_temp', 'r');
        #$out = fread($fh, 1024);
        #pclose($fh);
        #echo "<pre>".$out."</pre>";

        #$temp_file = "/sys/bus/w1/devices/28-000004e8a0f3/w1_slave";
        $temp_file = "/sys/class/thermal/thermal_zone0/temp";
        if (file_exists($temp_file)) {
            $lines = file($temp_file);
            #$pos = strpos($lines[1], "t=");
            $temp = round($lines[0] / 1000, 1);
            $currenttemp = $temp . "°C";
        } else {
            $temp = -1;
            $currenttemp = "N/A";
        }
        if ($temp < 0) { $result['alert'] = 'error'; }
        else {
           $MAXTEMP = 85.0;
           if ($temp > 80) { $result['alert'] = 'danger'; }
           else if ($temp > 60) { $result['alert'] = 'warning'; }
           else { $result['alert'] = 'success'; }
        }
        #echo "<pre>".$result['alert']."</pre>";
        $result['fahrenheit'] = round($temp * 1.8 + 32) . "°F";;
        $result['degrees'] = $currenttemp;
        $result['percentage'] = round(($temp / $MAXTEMP) * 100.0);

        return $result;
    }

}

?>
