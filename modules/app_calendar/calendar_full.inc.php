<?php
/*
* @version 0.1 (wizard)
*/
 global $session;
  if ($this->owner->name=='panel') {
   $out['CONTROLPANEL']=1;
  }


$year=(int)date('Y'); 
$today_day=(int)date('j');
$today_mon=(int)date('n');

if ($m1>1 or $m2<12) 
 $mon_filt='month(DUE) between ' .(int)$m1 . ' and ' . (int)$m2 . ' and ';
else
 $mon_filt='';


$cat_days=SQLSelect("select calendar_events.*,day(DUE) as DAY,month(DUE) as MON,calendar_categories.CALENDAR_COLOR from calendar_events inner join calendar_categories on calendar_events.calendar_category_id=calendar_categories.id where " . $mon_filt . "calendar_categories.AT_CALENDAR=1 and  (year(DUE)=" . $year . " or (IS_REPEATING=1 and REPEAT_TYPE=1)) and is_nodate=0"); //lsv added is_nodate 20201016

$arHolidays=SQLSelect("select calendar_events.ID,day(DUE) as DAY,month(DUE) as MONTH, calendar_events.TITLE as HD_NAME from calendar_events inner join calendar_categories on calendar_events.calendar_category_id=calendar_categories.id where " . $mon_filt . "calendar_categories.HOLIDAYS=1 and  year(DUE)=" . $year );

$arWorkdays=SQLSelect("select calendar_events.ID,day(DUE) as DAY,month(DUE) as MONTH from calendar_events inner join calendar_categories on calendar_events.calendar_category_id=calendar_categories.id where " . $mon_filt . "calendar_categories.WORKDAYS=1 and  year(DUE)=" . $year );



$month_name=array('Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь');
$calendar_index=0;
 
for ($month=$m1;$month<=$m2;$month++) {
  $calendar_month[$calendar_index]=array('MONTH_NAME'=>$month_name[$month-1],'YEAR'=>$year);
 // выставляем начало недели на понедельник
	$running_day = date('w',mktime(0,0,0,$month,1,$year));
	$running_day = $running_day - 1;
	if ($running_day == -1) {
 		$running_day = 6;
	}
	
	$days_in_month = date('t',mktime(0,0,0,$month,1,$year));
	$day_counter = 0;
	$dates_array = array();
        $j=1;

        if ($month==1) {
         $k=date('t',mktime(0,0,0,12,1,$year-1))-$running_day+1;
        }
        else {
         $k=date('t',mktime(0,0,0,$month-1,1,$year-1))-$running_day+1;
        }
	
	// вывод серым дней предыдущего месяца
        for ($x = 0; $x < $running_day; $x++) {
                $calendar_day='<div class="calendar_grey">' .$k . '</div>';
                $calendar_month[$calendar_index]['CALENDAR'][]=array('DAY'=>$calendar_day);
                $k++;

                $j++;
	}
	
	// дошли до чисел, будем их писать в первую строку
	for($list_day = 1; $list_day <= $days_in_month; $list_day++) {


                // выделяем сегодняшний день
                if ($today_day==$list_day and $today_mon==$month) 
                  $today_class=' today';
                else
                  $today_class='';
                $ev_tips='';

//		$cat_days=SQLSelect("select * from calendar_events where " . $filtr . "(Day(DUE)=" . $list_day . " and month(DUE)=" . $month . " and (year(DUE)=" . $year . " or (IS_REPEATING=1 and REPEAT_TYPE=1)))");
                foreach ($cat_days as $c_days) {
                 if ($c_days['DAY']==$list_day and $c_days['MON']==$month) {
		   $today_class .= ' category' . $c_days['CALENDAR_COLOR'] ;
                   $ev_tips.=$c_days['TITLE'] . ' ';
                 }
                }

		// выделяем выходные и праздничные дни
                $hd_name='';

                foreach($arHolidays as $hd) {
                 if ($hd['MONTH']==$month and $hd['DAY']==$list_day) {
                  $hd_name=$hd['HD_NAME'];
                  break;
                 }
                }
		
                if ($hd_name=='') {
		 if (($running_day  == 5) || ($running_day  == 6)) {
                   $is_workday=false;
                   foreach($arWorkdays as $wd) {
                    if ($wd['MONTH']==$month and $wd['DAY']==$list_day) {
                     $is_workday=true;
                     break;
                    }
                   }

                   if ($is_workday) 
                    $calendar_day='<div class="calendar' . $today_class ;
                   else
		    $calendar_day='<div class="calendar_we' . $today_class ;
		 }
		 else {
                   $calendar_day='<div class="calendar' . $today_class ;
                 }
                 if ($ev_tips=='') 
                  $calendar_day.= '">';
                 else
                  $calendar_day.='" data-toggle="tooltip" title="' . $ev_tips . '">';
                }
                else {
                 $calendar_day='<div class="calendar_hd' . $today_class . '" data-toggle="tooltip" title="' . $ev_tips .'">';
                }


		// пишем номер в ячейку
                $calendar_day.=$list_day . '</div>';
		$calendar_month[$calendar_index]['CALENDAR'][]=array('DAY'=>$calendar_day);
		

		// дошли до последнего дня недели
		if ($running_day == 6) 
  		   $running_day = -1;
		

		$days_in_this_week++; 
		$running_day++; 
		$day_counter++;
                $j++;
	}
        if ($running_day>0) {
         $k=1;
         for ($running_day=$running_day;$running_day<7;$running_day++) {
          $calendar_day='<div class="calendar_grey">' .$k . '</div>';
          $calendar_month[$calendar_index]['CALENDAR'][]=array('DAY'=>$calendar_day);
          $k++;
          $j++;
         }
        } 
        $calendar_index++;
        //for ($j=$j;$j<43;$j++) {
        //        $calendar_day='<div class="calendar"> </div>';
	//	$calendar_month[$month-1]['CALENDAR'][]=array('DAY'=>$calendar_day);
        //}
}	

    $out['CALENDAR_MONTH']=$calendar_month;

?>