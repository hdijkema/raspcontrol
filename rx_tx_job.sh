#!/bin/bash
#
# Use this script to monitor your network trafic
# Start it every 5 minutes with cron.
# It will fix the 4GB reset of the network trafic
# and put decent values in $TMPDIR/raspcontrol_tx, $TMPDIR/raspcontrol_rx
# Note: this assumes that in 5 minutes a PI cannot transmit more than 4GB over a network
#
# Run this script as www-data!
#
INTERFACE=eth0


INIT=1

echo $0 | sed -e 's%/[^/]*$%%'
TMPDIR=`echo $0 | sed -e 's%[/][^/]*$%%'`
TMPDIR="$TMPDIR/tmp";
if [ ! -d $TMPDIR ]; then
  mkdir -p $TMPDDIR
fi

BC=bc
AWK=awk
GREP=grep
IFCONFIG=/sbin/ifconfig
CAT=cat
DATE=date

RX_CURRENT=`$IFCONFIG $INTERFACE | $GREP -i rx | $GREP -i bytes | $AWK '{print $5}'`
RX_PREV=$RX_CURRENT
if [ -r $TMPDIR/raspcontrol_rx_count ]; then
  RX_PREV=`$CAT $TMPDIR/raspcontrol_rx_count`
  INIT=0
fi
  
TX_CURRENT=`$IFCONFIG $INTERFACE | $GREP -i tx | $GREP -i bytes | $AWK '{print $5}'`
TX_PREV=$TX_CURRENT
if [ -r $TMPDIR/raspcontrol_tx_count ]; then
  TX_PREV=`$CAT $TMPDIR/raspcontrol_tx_count`
  INIT=0
fi

if [ $INIT = 1 ]; then
  echo $RX_CURRENT >$TMPDIR/raspcontrol_rx_count
  echo $RX_CURRENT >$TMPDIR/raspcontrol_rx
  echo $TX_CURRENT >$TMPDIR/raspcontrol_tx_count
  echo $TX_CURRENT >$TMPDIR/raspcontrol_tx
else
  RX_DIFF=`echo "$RX_CURRENT-$RX_PREV" | $BC`
  if (( $RX_PREV > $RX_CURRENT )); then
     RX_DIFF=`echo "((4*1024*1024*1024)+$RX_CURRENT)-$RX_PREV" | $BC`
  fi
  RX=`$CAT $TMPDIR/raspcontrol_rx`
  RX=`echo "$RX+$RX_DIFF" | $BC`
  echo $RX >$TMPDIR/raspcontrol_rx
  echo $RX_CURRENT >$TMPDIR/raspcontrol_rx_count

  TX_DIFF=`echo "$TX_CURRENT-$TX_PREV" | $BC`
  if (( $TX_PREV > $TX_CURRENT )); then
     TX_DIFF=`echo "((4*1024*1024*1024)+$TX_CURRENT)-$TX_PREV" | $BC`
  fi
  TX=`$CAT $TMPDIR/raspcontrol_tx`
  TX=`echo "$TX+$TX_DIFF" | $BC`
  echo $TX >$TMPDIR/raspcontrol_tx
  echo $TX_CURRENT >$TMPDIR/raspcontrol_tx_count
fi

echo $INTERFACE >$TMPDIR/raspcontrol_net
$DATE >$TMPDIR/raspcontrol_last

