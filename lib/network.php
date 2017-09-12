<?php

namespace lib;

class Network {

    public static function connections() {
        global $ssh;
        $connections = $ssh->shell_exec_noauth("netstat -nta --inet | wc -l");
        $connections--;

        return array(
            'connections' => substr($connections, 0, -1),
            'alert' => ($connections >= 50 ? 'warning' : 'success')
        );
    }

    public static function ethernet() {
        global $ssh;
        $data = $ssh->shell_exec_noauth("/sbin/ifconfig eth0 | grep bytes");
        #echo "<pre>$data</pre>";
        $data = explode("\n", $data);
        #echo "<pre>$data</pre>";
        $rx = $data[0];
        $tx = $data[1];
        $lrx = preg_split("/\\s+/", $rx);
        $ltx = preg_split("/\\s+/", $tx);

	#echo "<pre>$rx</pre>";
	#echo "<pre>$tx</pre>";
        #echo "<pre>$lrx</pre>";
        #$lrx4 = $lrx[5];
        #echo "<pre>$lrx4</pre>";
       
        #$data = str_ireplace("RX bytes:", "", $data);
        #$data = str_ireplace("TX bytes:", "", $data);
        #$data = trim($data);
        #$data = explode(" ", $data);

        $rxRaw = $lrx[5] / 1024 / 1024;
        $txRaw = $ltx[5] / 1024 / 1024;
        $rx = round($rxRaw, 2);
        $tx = round($txRaw, 2);

        return array(
            'up' => $tx,
            'down' => $rx,
            'total' => $rx + $tx
        );
    }

}
