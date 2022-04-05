<?php

use RexShijaku\SQLToLaravelBuilder\SQLToLaravelBuilder;

require_once dirname(__FILE__) . '/../vendor/autoload.php';

$converter = new SQLToLaravelBuilder();

//==========================================================

$sql = 'SELECT
       s.start_date,
           Count(*) as panel_booked_count
FROM   package_panel AS a
       JOIN (SELECT Count(*)   AS booked_count_day,
                    start_date,
                    manage_panel_id AS manage_panel_id
             FROM   (SELECT items [manage_panel_id],
                            [start_date],
                            [end_date],
                            [id]
                     FROM   [booking_slot_detail] t1
                            OUTER apply dbo.Split(t1.[manage_panel_id], ',')
                     WHERE  ( start_date_time <= '2022-04-04 06:00:00.000'
                              AND end_date_time >= '2022-04-04 06:00:00.000' )
                             OR ( start_date_time < '2022-04-05 23:00:00.000'
                                  AND end_date_time >= '2022-04-05 23:00:00.000'
                                )
                             OR ( '2022-04-04 06:00:00.000' <= start_date_time
                                  AND '2022-04-05 23:00:00.000' >=
                                      start_date_time ))
                    AS a
             GROUP  BY manage_panel_id,
                       start_date) AS s
         ON s.manage_panel_id = a.manage_panel_id
WHERE  a.package_id = 1042 
       AND booked_count_day > 3
GROUP  BY 
          s.start_date';
echo $converter->convert($sql);
// prints
//          DB::table('members')
//              ->join('details', 'members.id', '=', 'details.members_id')
//              ->get();

//===============================================================

$sql = 'SELECT * FROM members LEFT JOIN details ON members.id = details.members_id';
echo $converter->convert($sql);
// prints
//          DB::table('members')
//              ->leftJoin('details', 'members.id', '=', 'details.members_id')
//              ->get();

//===============================================================

$sql = 'SELECT * FROM members RIGHT JOIN details ON members.id = details.members_id';
echo $converter->convert($sql);
// prints
//          DB::table('members')
//              ->rightJoin('details', 'members.id', '=', 'details.members_id')
//              ->get();


//===============================================================

$sql = 'SELECT * FROM members,details';
echo $converter->convert($sql);
// prints
//          DB::table('members')
//              ->crossJoin('details')
//              ->get();

//===================Advanced Join Clauses========================

$sql = 'SELECT * FROM members JOIN details 
                                  ON members.id = details.members_id 
                                  AND age > 10 AND age NOT BETWEEN 10 AND 20 
                                  AND title IS NOT NULL AND NOT age > 10 AND NAME LIKE "%Jo%" 
                                  AND age NOT IN (10,20,30)
                              LEFT JOIN further_details fd
                                  ON details.id = fd.details_id';
echo $converter->convert($sql);
// prints
//            DB::table('members')
//                ->join('details', function ($join) {
//                    $join->on('members.id', '=', 'details.members_id')
//                        ->where('age', '>', 10)
//                        ->whereNotBetween('age', [10, 20])
//                        ->whereNotNull('title')
//                        ->whereRaw(' NOT age > ? ', [10])
//                        ->where('NAME', 'LIKE', '%Jo%')
//                        ->whereNotIn('age', [10, 20, 30]);
//                })
//                ->leftJoin(DB::raw('further_details fd'), 'details.id', '=', 'fd.details_id')
//                ->get();
