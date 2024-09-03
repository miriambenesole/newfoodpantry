<?php

function getFilledGroupPrinted($grpElement, $groupdisplay)
{
  print "<tr>";
  print "<td class=\"result\" id=\"result\"  align=center>";
  print $grpElement->myitem;
  print "</td>";
  print "<td class=\"result\" align=center>";
  print $grpElement->mystartdate;
  print "</td>";
  if (str_contains($groupdisplay, 'Volunteers')) {
    print "<td class=\"result\" align=center>";
    print $grpElement->mystarttime . " - " . $grpElement->myendtime;
    print "</td>";
    if ($grpElement->myopenslots > 1) {
      print "<b>";
      print "<td class=\"bigresult\" align=center>";
      print $grpElement->myopenslots;
      print "</td>";
      print "</b>";
    } else {
      print "<td class=\"result\" align=center>";
      print $grpElement->myopenslots;
      print "</td>";
    }

  }
  print "</tr>";
}