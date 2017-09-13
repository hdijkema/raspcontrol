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
        $dir=$_SERVER['DOCUMENT_ROOT'];
        if (file_exists("$dir/tmp/raspcontrol_net")) {
           $rx = file_get_contents("$dir/tmp/raspcontrol_rx");
           $tx = file_get_contents("$dir/tmp/raspcontrol_tx");
           $rx = $rx / 1024 / 1024;
           $tx = $tx / 1024 / 1024;
           $rx = round($rx, 2);
           $tx = round($tx, 2);
           $net = file_get_contents("$dir/tmp/raspcontrol_net");
        } else {
          $net = "eth0";
          $data = $ssh->shell_exec_noauth("/sbin/ifconfig eth0 | grep bytes");
          $data = explode("\n", $data);
          $rx = $data[0];
          $tx = $data[1];
          $lrx = preg_split("/\\s+/", $rx);
          $ltx = preg_split("/\\s+/", $tx);

          $rxRaw = $lrx[5] / 1024 / 1024;
          $txRaw = $ltx[5] / 1024 / 1024;
          $rx = round($rxRaw, 2);
          $tx = round($txRaw, 2);
        }

        return array(
            'up' => $tx,
            'down' => $rx,
            'total' => $rx + $tx,
            'net' => $net
        );
    }

}
