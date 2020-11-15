<?php
/*
* @version 0.1 (wizard)
*/
 global $session;
  if ($this->owner->name=='panel') {
   $out['CONTROLPANEL']=1;
  }


  $qry="1";
  // search filters
  //searching 'TITLE' (varchar)
  global $title;
  if ($title!='') {
   $qry.=" AND TITLE LIKE '%".DBSafe($title)."%'";
   $out['TITLE']=$title;
  }
debmes('we are here!');
global $calendar_category_id;
if ($calendar_category_id!="") {
 $out['CALENDAR_CATEGORY_ID']=(int)$calendar_category_id;
 $qry.=" AND calendar_events.CALENDAR_CATEGORY_ID=".$out['CALENDAR_CATEGORY_ID'];
}

  // QUERY READY
  global $save_qry;
  if ($save_qry) {
   $qry=$session->data['calendar_events_qry'];
  } else {
   $session->data['calendar_events_qry']=$qry;
  }
  if (!$qry) $qry="1";
  // FIELDS ORDER
  global $sortby_calendar_events;
  if (!$sortby_calendar_events) {
   $sortby_calendar_events=$session->data['calendar_events_sort'];
  } else {
   if ($session->data['calendar_events_sort']==$sortby_calendar_events) {
    if (Is_Integer(strpos($sortby_calendar_events, ' DESC'))) {
     $sortby_calendar_events=str_replace(' DESC', '', $sortby_calendar_events);
    } else {
     $sortby_calendar_events=$sortby_calendar_events." DESC";
    }
   }
   $session->data['calendar_events_sort']=$sortby_calendar_events;
  }
  if (!$sortby_calendar_events) $sortby_calendar_events="IS_NODATE DESC,DUE DESC";
  $out['SORTBY']=$sortby_calendar_events;
  // SEARCH RESULTS
  $res = SQLSelect("SELECT calendar_events.*,calendar_categories.TITLE as CATEGORY FROM calendar_events left join calendar_categories ON calendar_events.calendar_category_id=calendar_categories.id WHERE $qry ORDER BY ".$sortby_calendar_events);
  $out['RESULT_MY']="test";
  if ($res[0]['ID']) {
   paging($res, 50, $out); // search result paging
   colorizeArray($res);
   $total=count($res);
   for($i=0;$i<$total;$i++) {
    // some action for every record if required
    if ($res[$i]['IS_NODATE']==1) {
     $res[$i]['DUE']='';
    } else {
     $res[$i]['DUE']=date('d.m.Y H:i',strtotime($res[$i]['DUE']));
     $res[$i]['DUE_TIME']=date('H:i',strtotime($res[$i]['DUE']));
    }

   }
   $out['RESULT']=$res;
  }
 $categories=SQLSelect("SELECT ID, TITLE FROM calendar_categories ORDER BY PRIORITY DESC,TITLE");
 $out['CATEGORIES']=$categories;
?>
