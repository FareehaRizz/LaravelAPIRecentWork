<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PDO;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\DB;

class WarningController extends Controller
{
    /*public function warnings($locations, $devices, $eventType, $severity, $PDO) {
   
        $warningsData = array();
    
       
        $sam = $PDO->prepare("SELECT w.*, d.`dname` AS device_name,l.`name` AS lname FROM warning w
            INNER JOIN devices d ON w. `device_id`=d.`id`
            INNER JOIN locations l ON l.`id`=d.`location_id`
            WHERE w.`device_id` LIKE :devices AND l.`id` LIKE :locations AND w.`eventType` LIKE :eventType AND `severity` LIKE :severity ORDER BY w.`id` DESC  LIMIT 50");
        $sam->execute(array(':devices' => $devices, ':locations' => $locations, ':eventType' => $eventType, ':severity' => $severity));
    
       
        if ($sam->rowCount() > 0) {
            
            $warningsData = $sam->fetchAll(PDO::FETCH_ASSOC);
        }
    
        
        return $warningsData;
    }*/

    public function warnings(Request $request) {

    $locations = $request->query('locations');
    $devices = $request->query('devices');
    $eventType = $request->query('eventType');
    $severity = $request->query('severity');
   
        $warningsData = array();
    
        try {
            $query = "SELECT w.*, d.`dname` AS device_name, l.`name` AS lname 
                      FROM warnings w
                      INNER JOIN devices d ON w.`device_id` = d.`id`
                      INNER JOIN locations l ON l.`id` = d.`location_id`
                      WHERE w.`device_id` LIKE :devices 
                      AND l.`id` LIKE :locations 
                      AND w.`eventType` LIKE :eventType 
                      AND w.`severity` LIKE :severity 
                      ORDER BY w.`id` DESC 
                      LIMIT 50";
    
            $warningsData = DB::select($query, [
                ':devices' => $devices,
                ':locations' => $locations,
                ':eventType' => $eventType,
                ':severity' => $severity,
            ]);
        } catch (\Exception $e) {
            throw new \Exception('Database error: ' . $e->getMessage());
        }
        return $warningsData;
    }
    
    
    
    
    
    
}
