<?php
/**
* Calendar 
*
* App_calendar
*
* @package project
* @author Serge J. <jey@tut.by>
* @copyright http://www.atmatic.eu/ (c)
* @version 0.1 (wizard, 17:05:45 [May 07, 2012])
*/
Define('DEF_REPEAT_TYPE_OPTIONS', '1=Yearly|2=Monthly|3=Weekly|4=Daily'); // options for 'REPEAT_TYPE'
//
//
class app_calendar extends module {
/**
* app_calendar
*
* Module class constructor
*
* @access private
*/
function app_calendar() {
  $this->name="app_calendar";
  $this->title="<#LANG_APP_CALENDAR#>";
  $this->module_category="<#LANG_SECTION_APPLICATIONS#>";
  $this->checkInstalled();
}
/**
* saveParams
*
* Saving module parameters
*
* @access public
*/
function saveParams($data=0) {
 $p=array();
 if (IsSet($this->id)) {
  $p["id"]=$this->id;
 }
 if (IsSet($this->view_mode)) {
  $p["view_mode"]=$this->view_mode;
 }
 if (IsSet($this->edit_mode)) {
  $p["edit_mode"]=$this->edit_mode;
 }
 if (IsSet($this->data_source)) {
  $p["data_source"]=$this->data_source;
 }
 if (IsSet($this->tab)) {
  $p["tab"]=$this->tab;
 }
 return parent::saveParams($p);
}
/**
* getParams
*
* Getting module parameters from query string
*
* @access public
*/
function getParams() {
  global $id;
  global $mode;
  global $view_mode;
  global $edit_mode;
  global $data_source;
  global $tab;
  if (isset($id)) {
   $this->id=$id;
  }
  if (isset($mode)) {
   $this->mode=$mode;
  }
  if (isset($view_mode)) {
   $this->view_mode=$view_mode;
  }
  if (isset($edit_mode)) {
   $this->edit_mode=$edit_mode;
  }
  if (isset($data_source)) {
   $this->data_source=$data_source;
  }
  if (isset($tab)) {
   $this->tab=$tab;
  }
}
/**
* Run
*
* Description
*
* @access public
*/
function run() {
 global $session;
 
   
  $out=array();
  if ($this->action=='admin') {
   $this->admin($out);
  } else {
   $this->usual($out);
  }

  $this->checkSettings();

  if (IsSet($this->owner->action)) {
   $out['PARENT_ACTION']=$this->owner->action;
  }
  if (IsSet($this->owner->name)) {
   $out['PARENT_NAME']=$this->owner->name;
  }
  $out['VIEW_MODE']=$this->view_mode;
  $out['EDIT_MODE']=$this->edit_mode;
  $out['MODE']=$this->mode;
  $out['ACTION']=$this->action;
  $out['DATA_SOURCE']=$this->data_source;
  $out['TAB']=$this->tab;
  if ($this->single_rec) {
   $out['SINGLE_REC']=1;
  }
  $this->data=$out;
  $p=new parser(DIR_TEMPLATES.$this->name."/".$this->name.".html", $this->data, $this);
  $this->result=$p->result;
}

/**
* Title
*
* Description
*
* @access public
*/
 function checkSettings() {
  
  $settings=array(
   array(
    'NAME'=>'APP_CALENDAR_SOONLIMIT', 
    'TITLE'=>'Сколько дней показывать в "Скоро"', 
    'TYPE'=>'text',
    'DEFAULT'=>'14'
    ),
   array(
    'NAME'=>'APP_CALENDAR_SHOWDONE', 
    'TITLE'=>'Показывать недавно выполненные дела',
    'TYPE'=>'yesno',
    'DEFAULT'=>'1'
    ),
   array(
    'NAME'=>'APP_CALENDAR_SHOWCALENDAR', 
    'TITLE'=>'Показывать календарь в Делах и Событиях',
    'TYPE'=>'yesno',
    'DEFAULT'=>'1'
    ),
   array(
    'NAME'=>'APP_CALENDAR_REMINDERTIME', 
    'TITLE'=>'Стандартное время напоминания', 
    'TYPE'=>'text',
    'DEFAULT'=>'10:15'
    )

   );


   foreach($settings as $k=>$v) {
    $rec=SQLSelectOne("SELECT ID FROM settings WHERE NAME='".$v['NAME']."'");
    if (!$rec['ID']) {
     $rec['NAME']=$v['NAME'];
     $rec['VALUE']=$v['DEFAULT'];
     $rec['DEFAULTVALUE']=$v['DEFAULT'];
     $rec['TITLE']=$v['TITLE'];
     $rec['TYPE']=$v['TYPE'];
     $rec['DATA']=$v['DATA'];
     $rec['ID']=SQLInsert('settings', $rec);
     Define('SETTINGS_'.$rec['NAME'], $v['DEFAULT']);
    }
   }

 }

/**
* BackEnd
*
* Module backend
*
* @access public
*/
function admin(&$out) {

 if (isset($this->data_source) && !$_GET['data_source'] && !$_POST['data_source']) {
  $out['SET_DATASOURCE']=1;
 }
 if ($this->data_source=='calendar_events' || $this->data_source=='') {
  if ($this->view_mode=='' || $this->view_mode=='search_calendar_events') {
   $this->search_calendar_events($out);
  }
  if ($this->view_mode=='edit_calendar_events') {
//   $this->edit_calendar_events($out, $this->id);
     $this->usual_edit($out, $this->id);
   }
  if ($this->view_mode=='delete_calendar_events') {
   $this->delete_calendar_events($this->id);
   $this->redirect("?data_source=calendar_events");
  }
  if ($this->view_mode=='delete_all_task') {
   $this->delete_all_task();
   $this->redirect("?data_source=calendar_events");
  }
 if ($this->view_mode=='delete_all_past_events') {
   $this->delete_all_past_events();
   $this->redirect("?data_source=calendar_events");
  }


 }
 if ($this->data_source=='calendar_full') 
   $this->calendar_full($out);
 if (isset($this->data_source) && !$_GET['data_source'] && !$_POST['data_source']) {
  $out['SET_DATASOURCE']=1;
 }
 if ($this->data_source=='calendar_categories') {
  if ($this->view_mode=='' || $this->view_mode=='search_calendar_categories') {
   $this->search_calendar_categories($out);
  }
  if ($this->view_mode=='edit_calendar_categories') {
   $this->edit_calendar_categories($out, $this->id);
  }
  if ($this->view_mode=='delete_calendar_categories') {
   $this->delete_calendar_categories($this->id);
   $this->redirect("?data_source=calendar_categories");
  }
 }
}
/**
* FrontEnd
*
* Module frontend
*
* @access public
*/
function usual(&$out) {
 if ($this->view_mode=='edit') {
  $this->usual_edit($out,$id);
 }

 if ($this->view_mode=='') {
  

  if ($this->mode=='is_done') {
   global $id;
   $this->task_done($id);
   $this->redirect("?");
  }

  if ($this->mode=='reset_done') {
   global $id;

   $rec=SQLSelectOne("SELECT * FROM calendar_events WHERE ID='".(int)$id."'");
   $rec['IS_DONE']=0;
   SQLUpdate('calendar_events', $rec);

   $this->redirect("?");
  }


  if (defined('TEMP_APP_CALENDAR_SHOW_CALENDAR')==false and SETTINGS_APP_CALENDAR_SHOWCALENDAR==1) { 
   $m=date('m');
   $m1=$m+1;

   if ($_GET['year_calendar']==1) {
    $out['YEAR_CALENDAR']=1;
    $m=1; 
    $m1=12; 
   } else {
    if (IsSet($this->currentmonth)) {
     $m1=(int)date('m',time());
     $m2=$m1;
    } else { 
     if (IsSet($this->mon1)) {
      $m=(int)$this->mon1;
     } 
    
     if (IsSet($this->mon2)) {
      $m1=(int)$this->mon2;
     }
    }  
   }
   $this->calendar_full($out,$m,$m1);
   $out['SHOW_CALENDAR']=1;
  }

  if (IsSet($this->calendar) or $_GET['calendar']==1)
   $out['ONLY_CALENDAR']=1;
  else
 {

  $events_today_temp=SQLSelect("SELECT  DATE_FORMAT( calendar_events.due, '%H:%i' ) due_time,calendar_events.*,calendar_categories.ICON FROM calendar_events left join calendar_categories on calendar_events.calendar_category_id=calendar_categories.id WHERE (TO_DAYS(DUE)=TO_DAYS(NOW()) OR IS_NODATE=1) AND IS_REPEATING!=1 AND IS_TASK=0  ORDER by DUE");
  if ($events_today_temp) {
   foreach($events_today_temp as $k=>$v) {
    $events_today[]=$v;
    //$calendar_categories[$k1]['EVENTS_TODAY'][]=$v;
   }
  }

  $tasks_today=SQLSelect("SELECT  DATE_FORMAT( calendar_events.due, '%H:%i' ) due_time,calendar_events.*,calendar_categories.ICON FROM calendar_events left join calendar_categories on calendar_events.calendar_category_id=calendar_categories.id  WHERE (TO_DAYS(DUE)=TO_DAYS(NOW()) OR IS_NODATE=1) AND IS_DONE=0 AND IS_TASK=1  ORDER BY DUE");
  if ($tasks_today) {
   foreach($tasks_today as $k=>$v) {
    $events_today[]=$v;
    //$calendar_categories[$k1]['EVENTS_TODAY'][]=$v;
   }
  }


  $events_early_today=SQLSelect("SELECT  DATE_FORMAT( calendar_events.due, '%H:%i' ) due_time,calendar_events.*,calendar_categories.ICON FROM calendar_events left join calendar_categories on calendar_events.calendar_category_id=calendar_categories.id  WHERE TO_DAYS(DATE(CONCAT_WS('-', DATE_FORMAT(NOW(), '%Y'), DATE_FORMAT(DUE, '%m'), DATE_FORMAT(DUE, '%d'))))=TO_DAYS(NOW()) AND IS_REPEATING=1 AND REPEAT_TYPE=1 AND IS_TASK=0 ORDER BY DUE");
  if ($events_early_today) {
   foreach($events_early_today as $k=>$v) {
    $events_today[]=$v;
    //$calendar_categories[$k1]['EVENTS_TODAY'][]=$v;
   }
  }
  $events_monthly_today=SQLSelect("SELECT  DATE_FORMAT( calendar_events.due, '%H:%i' ) due_time,calendar_events.*,calendar_categories.ICON FROM calendar_events left join calendar_categories on calendar_events.calendar_category_id=calendar_categories.id  WHERE TO_DAYS(DATE(CONCAT_WS('-', DATE_FORMAT(NOW(), '%Y'), DATE_FORMAT(NOW(), '%m'), DATE_FORMAT(DUE, '%d'))))=TO_DAYS(NOW()) AND IS_REPEATING=1 AND REPEAT_TYPE=2 AND IS_TASK=0 ORDER BY DUE");
  if ($events_monthly_today) {
   foreach($events_monthly_today as $k=>$v) {
    $events_today[]=$v;
    //$calendar_categories[$k1]['EVENTS_TODAY'][]=$v;
   }
  }
  $events_weekly_today=SQLSelect("SELECT  DATE_FORMAT( calendar_events.due, '%H:%i' ) due_time,calendar_events.*,calendar_categories.ICON FROM calendar_events left join calendar_categories on calendar_events.calendar_category_id=calendar_categories.id  WHERE DATE_FORMAT(DUE, '%w')=DATE_FORMAT(NOW(), '%w') AND IS_REPEATING=1 AND REPEAT_TYPE=3 AND IS_TASK=0 ORDER BY DUE");
  if ($events_weekly_today) {
   foreach($events_weekly_today as $k=>$v) {
    $events_today[]=$v;
    //$calendar_categories[$k1]['EVENTS_TODAY'][]=$v;
   }
  }

  if ($events_today) {
   $out['EVENTS_TODAY']=$events_today;
  }

  

  $calendar_categories=SQLSelect("SELECT ID,TITLE,ICON FROM calendar_categories ORDER BY PRIORITY DESC");
  $calendar_categories[]=array('ID'=>0,'TITLE'=>'Без категории');
  foreach($calendar_categories as $k1=>$v1) {

  $events_past=SQLSelect("SELECT  DATE_FORMAT( calendar_events.due, '%H:%i' ) as due_time,calendar_events.*, (TO_DAYS(DUE)-TO_DAYS(NOW())) as AGE FROM calendar_events WHERE TO_DAYS(DUE)<TO_DAYS(NOW()) AND IS_NODATE=0 AND IS_TASK=1 AND IS_DONE=0 and CALENDAR_CATEGORY_ID=" . $v1['ID'] . " ORDER BY AGE");
  if ($events_past) {
   foreach($events_past as $k=>$v) {
    $days=abs($v['AGE']);
    $days=GetNumberWord($days,array('день','дня','дней'));
    $v['AGE']=abs($v['AGE']);
    $v['DAYS']=$days;
    $calendar_categories[$k1]['EVENTS_PAST'][]=$v;
   }
   $out['EVENTS_PAST']=$events_past;
  }

  if (defined('TEMP_APP_CALENDAR_SOONLIMIT')) {
    $how_soon=TEMP_APP_CALENDAR_SOONLIMIT;
  } else {
    $how_soon=SETTINGS_APP_CALENDAR_SOONLIMIT;
  }
  $events_soon=SQLSelect("SELECT  DATE_FORMAT( calendar_events.due, '%H:%i' ) as due_time,calendar_events.*, (TO_DAYS(DUE)-TO_DAYS(NOW())) as AGE FROM calendar_events WHERE IS_NODATE=0 AND IS_TASK=0 AND (TO_DAYS(DUE)>TO_DAYS(NOW()) AND (TO_DAYS(DUE)-TO_DAYS(NOW())<=".(int)$how_soon.")) and CALENDAR_CATEGORY_ID=" . $v1['ID'] . " ORDER BY AGE");
 
  $tasks_soon=SQLSelect("SELECT  DATE_FORMAT( calendar_events.due, '%H:%i' ) as due_time,calendar_events.*, (TO_DAYS(DUE)-TO_DAYS(NOW())) as AGE FROM calendar_events WHERE IS_NODATE=0 AND IS_TASK=1 AND IS_DONE=0 AND ((TO_DAYS(DUE)>TO_DAYS(NOW()) AND (TO_DAYS(DUE)-TO_DAYS(NOW())<=".(int)$how_soon.")) OR (IS_NODATE=1)) and CALENDAR_CATEGORY_ID=" . $v1['ID'] . " ORDER BY AGE");
  if ($tasks_soon) {
   foreach($tasks_soon as $k=>$v) {
    $events_soon[]=$v;
   }
  }

  $events_early_soon=SQLSelect("SELECT  DATE_FORMAT( calendar_events.due, '%H:%i' ) as due_time,calendar_events.*, TO_DAYS(DATE(CONCAT_WS('-', DATE_FORMAT(NOW(), '%Y'), DATE_FORMAT(DUE, '%m'), DATE_FORMAT(DUE, '%d'))))-TO_DAYS(NOW()) as AGE FROM calendar_events WHERE IS_NODATE=0 AND (TO_DAYS(DATE(CONCAT_WS('-', DATE_FORMAT(NOW(), '%Y'), DATE_FORMAT(DUE, '%m'), DATE_FORMAT(DUE, '%d'))))>TO_DAYS(NOW())) AND (TO_DAYS(DATE(CONCAT_WS('-', DATE_FORMAT(NOW(), '%Y'), DATE_FORMAT(DUE, '%m'), DATE_FORMAT(DUE, '%d'))))-TO_DAYS(NOW())<=".(int)$how_soon.") AND IS_REPEATING=1 AND REPEAT_TYPE=1 AND IS_TASK=0 and CALENDAR_CATEGORY_ID=" . $v1['ID'] . " ORDER BY DUE");
  if ($events_early_soon) {
   foreach($events_early_soon as $k=>$v) {
    $events_soon[]=$v;
   }
  }

  $events_monthly_soon=SQLSelect("SELECT  DATE_FORMAT( calendar_events.due, '%H:%i' ) as due_time,calendar_events.* FROM calendar_events WHERE IS_NODATE=0 AND (TO_DAYS(DATE(CONCAT_WS('-', DATE_FORMAT(NOW(), '%Y'), DATE_FORMAT(NOW(), '%m'), DATE_FORMAT(DUE, '%d'))))>TO_DAYS(NOW())) AND (TO_DAYS(DATE(CONCAT_WS('-', DATE_FORMAT(NOW(), '%Y'), DATE_FORMAT(DUE, '%m'), DATE_FORMAT(DUE, '%d'))))-TO_DAYS(NOW())<=".(int)$how_soon.") AND IS_REPEATING=1 AND REPEAT_TYPE=2 AND IS_TASK=0 and CALENDAR_CATEGORY_ID=" . $v1['ID'] . " ORDER BY DUE");
  if ($events_monthly_soon) {
   foreach($events_monthly_soon as $k=>$v) {
    $events_soon[]=$v;
   }
  }

  if ($events_soon) {
   //$new_events=array();
   foreach($events_soon as $ev) {
    if (!$seen[$ev['ID']]) {
     //$new_events[]=$ev;
     if ($ev['AGE']) {
      $days=abs($ev['AGE']);
      if ($days==1) {
       $ev['DAYS']='завтра';
      //} else if ($days==2) {
      // $ev['DAYS']='послезавтра';
      } else {
       $ev['DAYS']='через ' . $days . ' ' . GetNumberWord($days,array('день','дня','дней'));
      }   
     } 
     $calendar_categories[$k1]['EVENTS_SOON'][]=$ev;
    }
    $seen[$ev['ID']]=1;
   }
  // $out['EVENTS_SOON']=$new_events;
  }

  if (empty($calendar_categories[$k1]['EVENTS_PAST']) and empty($calendar_categories[$k1]['EVENTS_SOON']) and empty ($calendar_categories[$k1]['RECENTLY_DONE'])) {
   $calendar_categories[$k1]['REC_COUNT']=0;
  } else {
   $calendar_categories[$k1]['REC_COUNT']=1;
  }
 }
  $out['CALENDAR_CATEGORIES']=$calendar_categories;


  if (SETTINGS_APP_CALENDAR_SHOWDONE=='1') {
   $recently_done=SQLSelect("SELECT  DATE_FORMAT( calendar_events.due, '%H:%i' ) as due_time, calendar_events.* FROM calendar_events WHERE IS_TASK=1 AND (IS_DONE=1 OR IS_REPEATING=1) AND TO_DAYS(NOW())-TO_DAYS(DONE_WHEN)<=1")  ;
   if ($recently_done) {
    $out['RECENTLY_DONE']=$recently_done;
   }
  }

 }
 }

}

/**
* Title
*
* Description
*
* @access public
*/
 function usual_edit(&$out, $id) {
  echo('usual_edit id='.$id.' title='.$title);
  global $title;
  global $id;

  if ($id) {
   $rec=SQLSelectOne("SELECT * FROM calendar_events WHERE ID='".(int)$id."'");

   if ($this->mode=='delete') {
    SQLExec("DELETE FROM calendar_events WHERE ID='".(int)$rec['ID']."'");
    $this->redirect("?");
   }

  } else {
   $out['TITLE']=$title;
   $out['DUE']=date('Y-m-d H:i:00');
   if ($out['TITLE']) {
    $others=SQLSelect("SELECT ID, TITLE, IS_DONE FROM calendar_events WHERE TITLE LIKE '%".DBSafe($out['TITLE'])."%' ORDER BY ID DESC");
    if ($others) {
     $out['OTHERS']=$others;
    }
   }
  }

  if ($this->mode=='update') {
   $ok=1;

   global $is_task;
   global $notes;

   $rec['TITLE']=$title;

   if (!$rec['TITLE']) {
    $ok=0;
    $out['ERR_TITLE']=1;
   }

   $rec['IS_TASK']=(int)$is_task;
   $rec['NOTES']=$notes;

   global $due;
   $rec['DUE']=$due;
   if (!$rec['DUE'] && $is_nodate==0) {
    $rec['DUE']=date('Y-m-d H:i'.':00');
   }
 
   global $is_repeating;
   $rec['IS_REPEATING']=(int)$is_repeating;

   global $is_repeating_after;
   $rec['IS_REPEATING_AFTER']=(int)$is_repeating_after;

   global $repeat_in;
   $rec['REPEAT_IN']=(int)$repeat_in;

   global $repeat_type;
   $rec['REPEAT_TYPE']=(int)$repeat_type;
   

   global $is_done;
   if ($is_done && !$rec['IS_DONE']) {
    $marked_done=1;
   }
   $rec['IS_DONE']=(int)$is_done;


   global $is_nodate;
   $rec['IS_NODATE']=(int)$is_nodate;
   if ($is_nodate) {
    $rec['DUE']= null; //no due
    $rec['IS_REPEATING']=0; //no repeat
   }


   global $user_id;
   $rec['USER_ID']=(int)$user_id;

   //global $location_id;
   //$rec['LOCATION_ID']=(int)$location_id;

   global $calendar_category_id;
   $rec['CALENDAR_CATEGORY_ID']=(int)$calendar_category_id;
	
   global $done_script_id;
   $rec['DONE_SCRIPT_ID']=(int)$done_script_id;
   ////////////////////////////////add by lsv 20201002
   global $done_code;
   $rec['DONE_CODE'] = $done_code;
   global $is_remind;
   $rec['IS_REMIND'] = (int)$is_remind;
   global $remind_time;
   $rec['REMIND_TIME'] = $remind_time;
   global $remind_code;
   $rec['REMIND_CODE'] = $remind_code;
   global $all_day;
   $rec['ALL_DAY'] = (int)$all_day;
   global $week_days;
   $rec['WEEK_DAYS'] = @implode(',', $week_days);
   if (is_null($rec['WEEK_DAYS'])) {
        $rec['WEEK_DAYS'] = '';
    }
   global $autodone;
   $rec['AUTODONE'] = $autodone;
   ////////////////////////////////

   if ($ok) {
    if ($rec['ID']) {
     SQLUpdate('calendar_events', $rec);
    } else {
     $rec['ADDED']=date('Y-m-d H:i:s');
     $rec['ID']=SQLInsert('calendar_events', $rec);
    }
    if ($marked_done) {
     $this->task_done($rec['ID']);
    }

    $this->redirect("?");
   }


  }

  outHash($rec, $out);
  $out['USERS']=SQLSelect("SELECT * FROM users ORDER BY NAME");
  //$out['LOCATIONS']=SQLSelect("SELECT * FROM gpslocations ORDER BY TITLE");
  $out['SCRIPTS']=SQLSelect("SELECT ID, TITLE FROM scripts ORDER BY TITLE");
  $out['CALENDAR_CATEGORIES']=SQLSelect("SELECT ID, TITLE from calendar_categories ORDER BY TITLE");

$w_days = array();
if ($rec['WEEK_DAYS']!=='') {
  $w_days = explode(',', $rec['WEEK_DAYS']);
}

$days = array( 1=>"Пн","Вт","Ср","Чт","Пт","Сб","Вс");
for ($i = 1; $i < 8; $i++) {
    $out['WDAYS'][] = array(
         'VALUE'    => $i,
         'DNAME'    => $days[$i],
         'SELECTED' => (in_array($i, $w_days))?1:0,
   );
    
}

 }

/**
* Title
*
* Description
*
* @access public
*/


 function task_done($id) {
  //DebMes("Task $id is DONE! Congratulations!!!");
  $rec = SQLSelectOne("SELECT * FROM calendar_events WHERE ID='".(int)$id."'");
  $rec['DONE_WHEN'] = date('Y-m-d H:i:s');
  $rec['IS_DONE'] = 1;

  //$tmp=explode('-', $rec['DUE']);
  //$due_time=mktime(1, 1, 1, $tmp[1], $tmp[2], $tmp[0]);

  if ($rec['IS_REPEATING']) {

   $due_time = strtotime(date('Y-m-d H:i:00',strtotime($rec['DUE']))); //unixtime
   //от греха поставим сегодня для пусто
   if(!$due_time) $due_time = time();
   $part_due = date_parse($rec['DUE']);

   $rec['IS_DONE']=0;
   if ($rec['REPEAT_TYPE']==1) {
    // yearly task
   $due_time_next_year=mktime($part_due['hour'],$part_due['minute'],0,$part_due['month'],$part_due['day'],$part_due['year']+1);
    $rec['DUE']=date('Y-m-d H:i:00', $due_time_next_year);
   } elseif ($rec['REPEAT_TYPE']==2) {
    // monthly task
    $time_next_month=$due_time+31*24*60*60;
    $due_time_next_month=mktime($part_due['hour'],$part_due['minute'],0, date('m', $time_next_month), $part_due['day'], date('Y', $time_next_month));
    $rec['DUE'] = date('Y-m-d H:i:00', $due_time_next_month);
   } elseif ($rec['REPEAT_TYPE']==3) {
     if(!$rec['WEEK_DAYS']){
       // weekly task
      $due_time_next_week=$due_time+7*24*60*60;
      $rec['DUE']=date('Y-m-d H:i:00', $due_time_next_week);
     }else{
       $week_days = array();
	   if ($rec['WEEK_DAYS']!=='') $week_days = explode(',', $rec['WEEK_DAYS']);
       $dd = time();
       $due = date("N", $dd); //переведём в формат пн=1...вс=7
       for($i = 0; $i < 7;$i++){
         $dd = $dd + 24*60*60; //след. дата
         $due = $due + 1;
         if($due > 7) $due = 1;
         if(in_array($due, $week_days)) { //первый следующий запуск
           //$next = $dd;
           break;
         }
       }
       $due_time_next_week= mktime($part_due['hour'],$part_due['minute'],0, date('m', $dd), date('j', $dd), date('Y', $dd));
       $rec['DUE']=date('Y-m-d H:i:00', $due_time_next_week);
   



     }
   } elseif ($rec['REPEAT_TYPE']==9) {
    // custom repeat task
    if ($rec['IS_REPEATING_AFTER']) {
     $rec['DUE']=date('Y-m-d H:i:00', time()+$rec['REPEAT_IN']*24*60*60);
    } else {
     $rec['DUE']=date('Y-m-d H:i:00', $due_time+$rec['REPEAT_IN']*24*60*60);
    }
   }
  }

  //$rec['LOG']=date('Y-m-d H:i:s').' Task marked DONE'."\n".$rec['LOG'];

  SQLUpdate('calendar_events', $rec);

  if ($rec['DONE_SCRIPT_ID']) {
   runScriptSafe($rec['DONE_SCRIPT_ID'], $rec);
  }
  //todo run done_code
 }

/**
* calendar_events search
*
* @access public
*/
 function search_calendar_events(&$out) {
  require(DIR_MODULES.$this->name.'/calendar_events_search.inc.php');
 }
/**
* calendar_events edit/add
*
* @access public
*/
 function edit_calendar_events(&$out, $id) {
  require(DIR_MODULES.$this->name.'/calendar_events_edit.inc.php');
 }
/**
* calendar_events delete record
*
* @access public
*/
 function delete_calendar_events($id) {
  $rec=SQLSelectOne("SELECT * FROM calendar_events WHERE ID='$id'");
  // some action for related tables
  SQLExec("DELETE FROM calendar_events WHERE ID='".$rec['ID']."'");
 }

/**
* calendar_events delete all task
*
* @access public
*/
 function delete_all_task() {
  SQLExec("DELETE FROM calendar_events WHERE IS_TASK=1 and IS_DONE=1 and (TO_DAYS(NOW())-TO_DAYS(DONE_WHEN))>1");
 }

/**
* calendar_events delete all past events
*
* @access public
*/
 function delete_all_past_events() {
$hl_ID=-1;
$workdays_ID=-1;
$rec=SQLSelectOne('select ID from calendar_categories where holidays=1');
if ($rec) 
 $hl_ID=$rec['ID'];

$rec=SQLSelectOne('select ID from calendar_categories where workdays=1');
if ($rec) 
 $workdays_ID=$rec['ID'];

  SQLExec("DELETE FROM calendar_events WHERE CALENDAR_CATEGORY_ID<>".$hl_ID." AND CALENDAR_CATEGORY_ID<>".$workdays_ID." AND IS_TASK=0 and IS_REPEATING=0 and (TO_DAYS(NOW())-TO_DAYS(DUE))>1");
 }

/**
* calendar_categories search
*
* @access public
*/
 function search_calendar_categories(&$out) {
  require(DIR_MODULES.$this->name.'/calendar_categories_search.inc.php');
 }
/**
* calendar_categories edit/add
*
* @access public
*/
 function edit_calendar_categories(&$out, $id) {
  require(DIR_MODULES.$this->name.'/calendar_categories_edit.inc.php');
 }
/**
* calendar_categories delete record
*
* @access public
*/
 function delete_calendar_categories($id) {
  $rec=SQLSelectOne("SELECT * FROM calendar_categories WHERE ID='$id'");
  // some action for related tables
  @unlink(ROOT.'./cms/calendar/'.$rec['ICON']);
  SQLExec("DELETE FROM calendar_categories WHERE ID='".$rec['ID']."'");
 }

/**
* calendar_full
*
* @access public
*/
 function calendar_full(&$out,$m1=1,$m2=12) {
  require(DIR_MODULES.$this->name.'/calendar_full.inc.php');
 }
/**
* GetHolidays
*
* @access public
*/
 function calendar_getholidays() {
$year=date('Y');

$rec=SQLSelectOne('select ID from calendar_categories where holidays=1');
if ($rec) {
$hl_ID=$rec['ID'];
//Удаляем все записи за текущий год из календаря
//с категорией у которой стоит галочка Праздники
SQLExec('delete from calendar_events where CALENDAR_CATEGORY_ID=' . $hl_ID . ' and Year(DUE)=' . $year);
$rec=SQLSelectOne('select ID from calendar_categories where workdays=1');
$workdays_ID=$rec['ID'];
//Удаляем все записи за текущий год из календаря
//с категорией у которой стоит галочка Праздники
SQLExec('delete from calendar_events where CALENDAR_CATEGORY_ID=' . $workdays_ID . ' and Year(DUE)=' . $year);

$calendar = simplexml_load_file('http://xmlcalendar.ru/data/ru/'.date('Y').'/calendar.xml');
$hd=$calendar->holidays->holiday; 
$calendar = $calendar->days->day;
foreach( $hd as $hday ){
    $id = (array)$hday->attributes()->id;
    $id = $id[0]; 
    $title = (array)$hday->attributes()->title;
    $title = $title[0]; 
    $holidays[$id]=$title;
}

//все праздники за текущий год
foreach( $calendar as $day ){
    $d = (array)$day->attributes()->d;
    $d = $d[0];
    //не считая короткие дни
    if( $day->attributes()->t == 1 ) {
     $h=$day->attributes()->h;
     if (isset($holidays[(int)$h]))
      $hd_name=$holidays[(int)$h];
     else
      $hd_name='Выходной день';
//     $arHolidays[] = array('DAY'=>substr($d, 3, 2),'MONTH'=>substr($d, 0, 2),'HD_NAME'=>$hd_name);
     $Record = Array();
     $Record['DUE'] = $year . '-' . substr($d, 0, 2) . '-' . substr($d, 3, 2) .' 00:00:00';
     $Record['CALENDAR_CATEGORY_ID'] = $hl_ID;
     $Record['TITLE'] = $hd_name;
     $Record['ID']=SQLInsert('calendar_events', $Record);
     
    }
    elseif ( $day->attributes()->t ==3 ) {
//     $arWorkdays[]=array('DAY'=>substr($d, 3, 2),'MONTH'=>substr($d, 0, 2));
     $Record = Array();
     $Record['DUE'] = $year . substr($d, 0, 2) . substr($d, 3, 2) .' 00:00:00' ;
     $Record['CALENDAR_CATEGORY_ID'] = $workdays_ID;
     $Record['TITLE'] = 'Перенесенный рабочий день';
     $Record['ID']=SQLInsert('calendar_events', $Record);

    }
}
 
}

 }

/**

* Install
*
* Module installation routine
*
* @access private
*/
 function install($data='') {
 @umask(0);
  if (!Is_Dir(ROOT."./cms/calendar")) {
   mkdir(ROOT."./cms/calendar", 0777);
  }
  parent::install();
 }
/**
* Uninstall
*
* Module uninstall routine
*
* @access public
*/
 function uninstall() {
  SQLExec('DROP TABLE IF EXISTS calendar_events');
  SQLExec('DROP TABLE IF EXISTS calendar_categories');
  parent::uninstall();
 }
/**
* dbInstall
*
* Database installation routine
*
* @access private
*/
 function dbInstall($data) {
/*
calendar_events - Events
calendar_categories - Categories
*/
  $data = <<<EOD
 calendar_events: ID int(10) unsigned NOT NULL auto_increment
 calendar_events: TITLE varchar(255) NOT NULL DEFAULT ''
 calendar_events: SYSTEM varchar(255) NOT NULL DEFAULT ''
 calendar_events: NOTES text
 calendar_events: DUE datetime
 calendar_events: ADDED datetime
 calendar_events: DONE_WHEN datetime
 calendar_events: IS_TASK int(3) NOT NULL DEFAULT '0'
 calendar_events: IS_DONE int(3) NOT NULL DEFAULT '0'
 calendar_events: IS_NODATE int(3) NOT NULL DEFAULT '0'
 calendar_events: IS_REPEATING int(3) NOT NULL DEFAULT '0'
 calendar_events: REPEAT_TYPE int(3) NOT NULL DEFAULT '0'
 calendar_events: WEEK_DAYS varchar(255) NOT NULL DEFAULT ''
 calendar_events: IS_REPEATING_AFTER int(3) NOT NULL DEFAULT '0'
 calendar_events: REPEAT_IN int(10) NOT NULL DEFAULT '0'
 calendar_events: USER_ID int(10) NOT NULL DEFAULT '0'
 calendar_events: LOCATION_ID int(10) NOT NULL DEFAULT '0'
 calendar_events: CALENDAR_CATEGORY_ID int(10) NOT NULL DEFAULT '0'
 calendar_events: DONE_SCRIPT_ID int(10) NOT NULL DEFAULT '0'
 calendar_events: DONE_CODE text
 calendar_events: LOG text
 calendar_events: IS_REMIND int(3) NOT NULL DEFAULT '0'
 calendar_events: REMIND_TIME datetime DEFAULT NULL
 calendar_events: REMIND_CODE text
 calendar_events: ALL_DAY int(3) NOT NULL DEFAULT '1'

 calendar_categories: ID int(10) unsigned NOT NULL auto_increment
 calendar_categories: TITLE varchar(255) NOT NULL DEFAULT ''
 calendar_categories: ACTIVE int(255) NOT NULL DEFAULT '0'
 calendar_categories: PRIORITY int(10) NOT NULL DEFAULT '0'
 calendar_categories: ICON varchar(70) NOT NULL DEFAULT ''
 calendar_categories: AT_CALENDAR tinyint(1) NOT NULL DEFAULT 0
 calendar_categories: CALENDAR_COLOR int(11) NOT NULL DEFAULT 0
 calendar_categories: HOLIDAYS tinyint(1) NOT NULL DEFAULT 0
 calendar_categories: WORKDAYS tinyint(1) NOT NULL DEFAULT 0
 
EOD;
  parent::dbInstall($data);
 }
// --------------------------------------------------------------------
}
/*
*
* TW9kdWxlIGNyZWF0ZWQgTWF5IDA3LCAyMDEyIHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/
?>
